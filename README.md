# Cevi Drupal Seite

Das ist ein Projekt, damit du mit Hilfe vom CMS "Drupal" deine eigene Cevi-Webseite erstellen kannst.

## Voraussetzungen

Folgende Voraussetzungen müssen gegeben sein:

- Composer muss installiert sein
- Irgendein Server sollte vorhanden sein
- Eine Datenbank (mysql) mit den nötigen Zugängen muss vorhanden sein.

## Vorbereitung

Im Ordner `settings` muss eine Datei `settings.current.php` erstellt werden. Als Vorlage können die Dateien
`settings/settings.example.development.php` oder
`settings/settings.example.production.php`
dienen.

# <a name="installation">Installation</a>

`$ cd drupal`

`$ composer install`


## Frontend bearbeiten

`$ npm install`

Die Dateien vom Frontend liegen im Ordner `./cevi-themes` bereit und werden im Composer-Prozess (siehe [Installation](#installation) in die entsprechenden Verzeichnisse im Drupal-Verzeichnis kopiert.

`$ npm run build`
Bereitet alle Dateien vor und optimiert alle Frontend-Dateien

`$ npm run watch`
Startet den Befehl, die aktuellen Bearbeitungen am Frontend zu überwachen.

`$ cd drupal && composer install && cd ..`
Um die Frontend-Dateien ins Drupal-System einzugliedern.