<?php

/**
 * Development overrides — loaded via docker-compose.override.yml
 */
$databases['default']['default'] = [
  'driver'    => 'mysql',
  'namespace' => 'Drupal\mysql\Driver\Database\mysql',
  'autoload'  => 'core/modules/mysql/src/Driver/Database/mysql/',
  'database'  => getenv('DRUPAL_DB_NAME') ?: 'drupal',
  'username'  => getenv('DRUPAL_DB_USER') ?: 'drupal',
  'password'  => getenv('DRUPAL_DB_PASS') ?: 'drupal',
  'host'      => getenv('DRUPAL_DB_HOST') ?: 'mariadb',
  'port'      => '3306',
  'prefix'    => '',
  'collation' => 'utf8mb4_general_ci',
];

$settings['hash_salt'] = getenv('DRUPAL_HASH_SALT') ?: 'dev-hash-salt-change-in-production';
$settings['config_sync_directory'] = '../config/sync';
$settings['trusted_host_patterns'] = [
  '^localhost$',
  '^127\.0\.0\.1$',
];
$settings['file_public_path']  = 'sites/default/files';
$settings['file_private_path'] = '/var/www/private';
$settings['skip_permissions_hardening'] = TRUE;

// Disable caching in dev — requires development.services.yml which defines cache.backend.null
$settings['container_yamls'][] = DRUPAL_ROOT . '/sites/development.services.yml';
$settings['cache']['bins']['render']        = 'cache.backend.null';
$settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';
$settings['cache']['bins']['page']          = 'cache.backend.null';

// Twig debug
$settings['twig_debug'] = TRUE;
$settings['twig_auto_reload'] = TRUE;
$settings['twig_cache'] = FALSE;

// Verbose errors
$config['system.logging']['error_level'] = 'verbose';
$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['js']['preprocess']  = FALSE;

// Environment indicator
$config['environment_indicator.indicator']['bg_color'] = '#0057B7';
$config['environment_indicator.indicator']['fg_color'] = '#ffffff';
$config['environment_indicator.indicator']['name']     = 'Development';
