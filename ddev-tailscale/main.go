// ddevdns is een mini-DNS-server die alle *.ddev.site-vragen beantwoordt
// met het Tailscale-IP van je laptop, zodat telefoons in je tailnet
// (via Tailscale Split DNS) je lokale DDEV-projecten kunnen bereiken.
package main

import (
	"flag"
	"fmt"
	"log"
	"net"
	"os"
	"os/exec"
	"strings"

	"github.com/miekg/dns"
)

var (
	listenAddr = flag.String("listen", ":53", "adres om op te luisteren (UDP en TCP)")
	domainFlag = flag.String("domain", "ddev.site", "domein waarvoor geantwoord wordt (inclusief alle subdomeinen)")
	ipFlag     = flag.String("ip", "auto", "IP dat teruggegeven wordt; 'auto' detecteert het eigen Tailscale-IP")
	ttlFlag    = flag.Uint("ttl", 30, "TTL van DNS-antwoorden in seconden")
)

func main() {
	flag.Parse()

	target, err := resolveTargetIP(*ipFlag)
	if err != nil {
		log.Fatalf("kan doel-IP niet bepalen: %v", err)
	}

	domain := dns.Fqdn(strings.ToLower(*domainFlag))
	log.Printf("ddevdns: *.%s -> %s (luistert op %s)", strings.TrimSuffix(domain, "."), target, *listenAddr)

	dns.HandleFunc(".", func(w dns.ResponseWriter, req *dns.Msg) {
		handle(w, req, domain, target, uint32(*ttlFlag))
	})

	errCh := make(chan error, 2)
	for _, netw := range []string{"udp", "tcp"} {
		srv := &dns.Server{Addr: *listenAddr, Net: netw}
		go func() { errCh <- srv.ListenAndServe() }()
	}
	log.Fatal(<-errCh)
}

func handle(w dns.ResponseWriter, req *dns.Msg, domain string, target net.IP, ttl uint32) {
	resp := new(dns.Msg)
	resp.SetReply(req)
	resp.Authoritative = true

	if len(req.Question) == 0 {
		resp.Rcode = dns.RcodeFormatError
		w.WriteMsg(resp)
		return
	}

	q := req.Question[0]
	name := strings.ToLower(q.Name)
	if name != domain && !strings.HasSuffix(name, "."+domain) {
		// Split DNS hoort alleen ddev.site-vragen hierheen te sturen;
		// al het andere weigeren we zodat we nooit open resolver spelen.
		resp.Rcode = dns.RcodeRefused
		w.WriteMsg(resp)
		return
	}

	hdr := dns.RR_Header{Name: q.Name, Class: dns.ClassINET, Ttl: ttl}
	switch q.Qtype {
	case dns.TypeA:
		if v4 := target.To4(); v4 != nil {
			hdr.Rrtype = dns.TypeA
			resp.Answer = append(resp.Answer, &dns.A{Hdr: hdr, A: v4})
		}
	case dns.TypeAAAA:
		if target.To4() == nil {
			hdr.Rrtype = dns.TypeAAAA
			resp.Answer = append(resp.Answer, &dns.AAAA{Hdr: hdr, AAAA: target})
		}
		// Voor een IPv4-doel geven we een leeg NOERROR-antwoord, zodat de
		// client meteen doorvalt naar het A-record.
	}
	w.WriteMsg(resp)
}

// resolveTargetIP bepaalt welk IP we teruggeven. Bij 'auto' proberen we eerst
// `tailscale ip -4`, daarna zoeken we een interface-adres in het Tailscale
// CGNAT-bereik (100.64.0.0/10).
func resolveTargetIP(value string) (net.IP, error) {
	if value != "auto" {
		ip := net.ParseIP(value)
		if ip == nil {
			return nil, fmt.Errorf("ongeldig IP-adres: %q", value)
		}
		return ip, nil
	}

	if out, err := exec.Command("tailscale", "ip", "-4").Output(); err == nil {
		first := strings.TrimSpace(strings.SplitN(string(out), "\n", 2)[0])
		if ip := net.ParseIP(first); ip != nil {
			return ip, nil
		}
	}

	_, cgnat, _ := net.ParseCIDR("100.64.0.0/10")
	addrs, err := net.InterfaceAddrs()
	if err != nil {
		return nil, err
	}
	for _, addr := range addrs {
		if ipn, ok := addr.(*net.IPNet); ok && ipn.IP.To4() != nil && cgnat.Contains(ipn.IP) {
			return ipn.IP.To4(), nil
		}
	}

	fmt.Fprintln(os.Stderr, "geen Tailscale-IP gevonden; geef er zelf één op met --ip 100.x.y.z")
	return nil, fmt.Errorf("geen Tailscale-interface gevonden")
}
