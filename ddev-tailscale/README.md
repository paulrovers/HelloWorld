# ddev-tailscale

Bekijk al je lokale DDEV-projecten (`*.ddev.site`) op je mobiel, via het
Tailscale dat je al gebruikt — **zonder extra app of tweede VPN op je telefoon**.

## Waarom geen aparte mobiele app?

Android en iOS staan maar één actieve VPN tegelijk toe, en dat slot is al
bezet door Tailscale. Een eigen DNS-forwarder-app kan dus niet naast
Tailscale draaien. Gelukkig hoeft dat ook niet: Tailscale heeft **Split DNS**,
waarmee je voor één domein (`ddev.site`) een eigen nameserver aanwijst. De
Tailscale-app op je telefoon past dat automatisch toe.

Het enige dat ontbreekt is die nameserver. Dat is `ddevdns` in deze map: een
mini-DNS-server die elke `*.ddev.site`-vraag beantwoordt met het (gekozen)
Tailscale-IP van je laptop. Alle andere domeinen weigert hij, dus je bent
nooit een open resolver.

```
telefoon ──Tailscale──▶ vraag: mijnproject.ddev.site?
                          │  (Split DNS: ddev.site → laptop)
laptop: ddevdns ──────────▶ antwoord: 100.x.y.z (Tailscale-IP laptop)
telefoon ──Tailscale──▶ https://mijnproject.ddev.site → DDEV-router op laptop
```

## Installatie (laptop)

### 1. Bouw en start ddevdns

```sh
go build -o ddevdns .

# Automatisch eigen Tailscale-IP detecteren:
sudo ./ddevdns

# Of expliciet kiezen welk Tailscale-IP teruggegeven wordt:
sudo ./ddevdns --ip 100.x.y.z
```

`sudo` is nodig omdat poort 53 een privileged poort is. Op Linux kan het ook
zonder sudo:

```sh
sudo setcap 'cap_net_bind_service=+ep' ./ddevdns
./ddevdns
```

Flags:

| Flag       | Default     | Betekenis                                              |
|------------|-------------|--------------------------------------------------------|
| `--ip`     | `auto`      | IP in de antwoorden; `auto` = eigen Tailscale-IP       |
| `--domain` | `ddev.site` | domein (incl. subdomeinen) waarvoor geantwoord wordt   |
| `--listen` | `:53`       | luisteradres (UDP + TCP)                               |
| `--ttl`    | `30`        | TTL van de antwoorden in seconden                      |

Automatisch starten: zie [`ddevdns.service`](ddevdns.service) (Linux/systemd)
of [`nl.paulrovers.ddevdns.plist`](nl.paulrovers.ddevdns.plist) (macOS/launchd).

### 2. Laat DDEV op alle interfaces luisteren

Standaard bindt de DDEV-router alleen aan 127.0.0.1. Zet hem open (de
firewall van je laptop + Tailscale-ACL's bepalen wie erbij kan):

```sh
ddev config global --router-bind-all-interfaces
ddev poweroff && ddev start
```

Zorg dat je firewall poort 80/443 (en 53 voor ddevdns) toestaat op de
Tailscale-interface (`tailscale0` op Linux).

### 3. Split DNS instellen in Tailscale

1. Ga naar <https://login.tailscale.com/admin/dns>.
2. Onder **Nameservers** → **Add nameserver** → **Custom**.
3. Vul het Tailscale-IP van je laptop in (bijv. `100.x.y.z`).
4. Zet **Restrict to domain** (Split DNS) aan en vul `ddev.site` in.
5. Klaar — dit geldt meteen voor je hele tailnet.

## Op je telefoon

Niets installeren. Tailscale aan, browser open, `https://mijnproject.ddev.site`
intypen. De vraag gaat via Split DNS naar je laptop en je krijgt je lokale
DDEV-site te zien.

### HTTPS-waarschuwing wegwerken (optioneel)

DDEV gebruikt mkcert-certificaten die je telefoon niet kent. Óf klik de
waarschuwing weg / gebruik `http://`, óf installeer de mkcert-root-CA op je
telefoon:

```sh
mkcert -CAROOT   # toont de map met rootCA.pem
```

Stuur `rootCA.pem` naar je telefoon en installeer hem als CA-certificaat
(Android: Instellingen → Beveiliging → Certificaat installeren → CA;
iOS: profiel installeren + vertrouwen aanzetten bij Certificaatvertrouwen).

## Een ander Tailscale-IP kiezen

- **Zelfde ddevdns, ander doel:** start met `--ip <ander-tailscale-ip>`;
  alle telefoons kijken dan naar die machine.
- **Meerdere laptops:** draai ddevdns op elke laptop en wissel in de
  Tailscale DNS-instellingen de nameserver voor `ddev.site` om — dat is de
  "keuzeschakelaar" voor je hele tailnet.

## Ontwikkelen

```sh
go test ./...
```
