#!/bin/bash

composer install

./vendor/bin/drush si --locale=de

./vendor/bin/drush config-set system.site uuid eb76eb7c-70c3-4296-960b-673b5f7702af -y
./vendor/bin/drush config-set language.entity.de uuid 7e2040a3-be26-44ad-ba1e-c5902dded730 -y

./vendor/bin/drush cim -y
