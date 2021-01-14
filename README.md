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

Lokaler Server so einrichten, dass die Webseite im Verzeichnis `./drupal/web` aufgerufen werden kann

`node`, `npm` und `composer` global im System installieren.

# <a name="installation">Installation</a>

1) Frontend einmalig generieren: `$ npm install; npm run build`

1) `$ cd drupal`

1) Run `$ ./cevi-install.sh`

OR

1) `$ composer install`

1) Drupal installieren: Seite besuchen und Installation starten.
Installationsprogramm kann unter DOMAIN//core/install.php aufgerufen werden. Diese Seite wird automatisch geladen.
   
   1) Sprache: Deutsch
   1) Installationsprofil: Standard
   1) Eigene Informationen eingeben (Name der Webseite, Emailadresse, Admin-User, ...)
   1) Oder via Konsole: `$ ./vendor/bin/drush si --locale=de` 

1) Eigene Config-UUID überschreiben:

   1) `$ vendor/bin/drush config-set system.site uuid eb76eb7c-70c3-4296-960b-673b5f7702af -y`
   1) `$ vendor/bin/drush config-set language.entity.de uuid 7e2040a3-be26-44ad-ba1e-c5902dded730 -y`

1) `$ vendor/bin/drush cim -y`
Importiert die Konfiguration für die ganze Webseite.


## Frontend bearbeiten

`$ npm install`

Die Dateien vom Frontend liegen im Ordner `./cevi-themes` bereit und werden im Composer-Prozess (siehe [Installation](#installation) in die entsprechenden Verzeichnisse im Drupal-Verzeichnis kopiert.

`$ npm run build`
Bereitet alle Dateien vor und optimiert alle Frontend-Dateien

`$ npm run watch`
Startet den Befehl, die aktuellen Bearbeitungen am Frontend zu überwachen.

`$ cd drupal && composer install && cd ..`
Um die Frontend-Dateien ins Drupal-System einzugliedern.
