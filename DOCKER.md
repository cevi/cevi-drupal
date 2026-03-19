# Docker Setup

Dieses Projekt läuft vollständig in Docker. **Es müssen keine weiteren Tools installiert werden.**

## Voraussetzungen

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (Mac/Windows) oder Docker + Docker Compose Plugin (Linux)

## Lokale Entwicklung

### 1. Ersteinrichtung

```bash
git clone https://github.com/cevi/cevi-drupal.git
cd cevi-drupal
cp .env.example .env        # Werte anpassen falls nötig (Standardwerte funktionieren für Entwicklung)
docker compose up -d --build
```

### 2. Neue leere Cevi-Seite einrichten (Erstinstallation ohne Dump)

Wenn kein Datenbank-Dump vorhanden ist, richtet dieses Skript eine frische Drupal-Instanz mit dem Cevi-Theme und der vollständigen Konfiguration ein:

```bash
docker compose exec drupal sh /cevi-install.sh
```

Das Skript führt folgende Schritte aus:
1. `drush si` — Drupal mit deutschem Standard-Profil installieren
2. UUIDs setzen (Site, Sprache, Shortcuts) — nötig für sauberen Config-Import
3. Standard-Felder entfernen, die mit der Cevi-Konfiguration kollidieren
4. `drush cim` — vollständige Konfiguration importieren (Theme, Module, Content-Typen)

Anschliessend Frontend-Assets bauen (läuft im separaten Node-Container):

```bash
docker compose run --rm node-builder sh -c "npm ci && npm run build"
```

Danach Login-Link ausgeben und im Browser öffnen — Drupal setzt kein Standardpasswort, der Link aus `drush uli` ist der einzige Zugang.

---

### 3. Datenbank-Dump importieren

```bash
# Unkomprimiert:
docker compose exec -T mariadb mysql -udrupal -pdrupal drupal < pfad/zum/dump.sql

# Gzip-komprimiert:
gunzip -c pfad/zum/dump.sql.gz | docker compose exec -T mariadb mysql -udrupal -pdrupal drupal

# Cache leeren nach Import:
docker compose exec drupal php vendor/bin/drush cr
```

### 4. Login-Link generieren

```bash
docker compose exec drupal php vendor/bin/drush uli
```

Seite unter http://localhost öffnen — fertig.

---

## Häufige Befehle (Entwicklung)

> Alle Befehle ohne `-f` nutzen automatisch `docker-compose.override.yml` (Dev-Modus).

```bash
# Stack starten / stoppen
docker compose up -d --build
docker compose down

# Drush (Drupal CLI)
docker compose exec drupal php vendor/bin/drush cr           # Cache leeren
docker compose exec drupal php vendor/bin/drush cim -y       # Konfiguration importieren
docker compose exec drupal php vendor/bin/drush cex -y       # Konfiguration exportieren
docker compose exec drupal php vendor/bin/drush uli          # Admin-Login-Link

# Composer
docker compose exec drupal php /usr/bin/composer require drupal/token

# DB-Export
docker compose exec -T mariadb mysqldump -udrupal -pdrupal drupal | gzip > db_dumps/export-$(date +%Y-%m-%d).sql.gz

# Logs
docker compose logs -f

# Shell im Drupal-Container
docker compose exec drupal sh

# Frontend-Assets neu kompilieren (danach Stack neu bauen, damit das Image die aktuellen Assets enthält)
docker compose run --rm node-builder sh -c "npm ci && npm run build"
docker compose up -d --build

# Frontend-Watch-Modus
docker compose run --rm node-builder npm run watch
```

---

## Produktion

### Erstinstallation

```bash
git clone https://github.com/cevi/cevi-drupal.git /opt/cevi-drupal
cd /opt/cevi-drupal
cp .env.example .env
# .env bearbeiten: sichere Passwörter, DRUPAL_HASH_SALT, CADDY_DOMAIN, DRUPAL_TRUSTED_HOSTS setzen

docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build
```

Caddy beschafft automatisch Let's-Encrypt-Zertifikate für Apex- und www-Domain.
Vor dem ersten Start **zwei** DNS-A-Records auf die VM-IP setzen:

```txt
example.com      A  <VM-IP>
www.example.com  A  <VM-IP>
```

Anfragen an `www.example.com` werden dauerhaft auf `example.com` (HTTPS) umgeleitet.
`DRUPAL_TRUSTED_HOSTS=^example\.com$` setzen (nur Apex — www erreicht Drupal nie).

### Bestehende Daten importieren

```bash
# DB-Dump auf die VM kopieren, dann:
docker compose -f docker-compose.yml -f docker-compose.prod.yml exec -T mariadb \
  mysql -u"$DRUPAL_DB_USER" -p"$DRUPAL_DB_PASS" "$DRUPAL_DB_NAME" < dump.sql

# Hochgeladene Dateien kopieren
docker run --rm -v cevi-drupal_drupal_files:/target -v $(pwd):/src alpine \
  tar xzf /src/files.tar.gz -C /target
```

### Updates

```bash
git pull
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build
```

Der Entrypoint führt beim Start automatisch `drush updb` + `drush cr` aus.

---

## Architektur

```txt
┌──────────────────────────────────────────────┐
│  Internet                                    │
└──────────────────────────────────────────────┘
        │ :80 / :443
┌───────┴─────┐    ┌───────────┐    ┌─────────┐
│ Caddy       │───►│  Drupal   │───►│ MariaDB │
│ (HTTPS+TLS) │    │ (php-fpm) │    │ (Daten) │
└─────────────┘    └───────────┘    └─────────┘
```
