package main

import (
	"net"
	"testing"

	"github.com/miekg/dns"
)

func startTestServer(t *testing.T) string {
	t.Helper()
	target := net.ParseIP("100.101.102.103")
	mux := dns.NewServeMux()
	mux.HandleFunc(".", func(w dns.ResponseWriter, req *dns.Msg) {
		handle(w, req, "ddev.site.", target, 30)
	})

	pc, err := net.ListenPacket("udp", "127.0.0.1:0")
	if err != nil {
		t.Fatal(err)
	}
	srv := &dns.Server{PacketConn: pc, Handler: mux}
	go srv.ActivateAndServe()
	t.Cleanup(func() { srv.Shutdown() })
	return pc.LocalAddr().String()
}

func query(t *testing.T, addr, name string, qtype uint16) *dns.Msg {
	t.Helper()
	m := new(dns.Msg)
	m.SetQuestion(dns.Fqdn(name), qtype)
	resp, _, err := new(dns.Client).Exchange(m, addr)
	if err != nil {
		t.Fatal(err)
	}
	return resp
}

func TestSubdomainResolvesToTarget(t *testing.T) {
	addr := startTestServer(t)
	for _, name := range []string{"mijnproject.ddev.site", "a.b.ddev.site", "DDEV.SITE"} {
		resp := query(t, addr, name, dns.TypeA)
		if resp.Rcode != dns.RcodeSuccess || len(resp.Answer) != 1 {
			t.Fatalf("%s: rcode=%v answers=%d", name, resp.Rcode, len(resp.Answer))
		}
		if a := resp.Answer[0].(*dns.A); a.A.String() != "100.101.102.103" {
			t.Fatalf("%s: kreeg %s", name, a.A)
		}
	}
}

func TestAAAAReturnsEmptyNoErrorForIPv4Target(t *testing.T) {
	addr := startTestServer(t)
	resp := query(t, addr, "mijnproject.ddev.site", dns.TypeAAAA)
	if resp.Rcode != dns.RcodeSuccess || len(resp.Answer) != 0 {
		t.Fatalf("rcode=%v answers=%d", resp.Rcode, len(resp.Answer))
	}
}

func TestOtherDomainsRefused(t *testing.T) {
	addr := startTestServer(t)
	for _, name := range []string{"example.com", "evilddev.site", "ddev.site.example.org"} {
		resp := query(t, addr, name, dns.TypeA)
		if resp.Rcode != dns.RcodeRefused {
			t.Fatalf("%s: verwachtte REFUSED, kreeg %v", name, dns.RcodeToString[resp.Rcode])
		}
	}
}
