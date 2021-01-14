#!/bin/bash

composer install

./vendor/bin/drush si --locale=de -y

./vendor/bin/drush config-set system.site uuid eb76eb7c-70c3-4296-960b-673b5f7702af -y
./vendor/bin/drush config-set language.entity.de uuid 7e2040a3-be26-44ad-ba1e-c5902dded730 -y
./vendor/bin/drush config-set shortcut.set.default uuid 6e0504ef-46e5-42bf-a1a2-cd097c1689ce -y
./vendor/bin/drush cdel field.field.node.article.body
./vendor/bin/drush cdel field.field.node.page.body
./vendor/bin/drush ev '\Drupal::entityManager()->getStorage("shortcut_set")->load("default")->delete();'

./vendor/bin/drush cim -y

./vendor/bin/drush user:password admin cevi
