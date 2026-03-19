<?php

/**
 * Database configuration — values injected via environment variables.
 * Set these in your .env file (never commit credentials).
 */
$databases['default']['default'] = [
  'driver'    => 'mysql',
  'namespace' => 'Drupal\mysql\Driver\Database\mysql',
  'autoload'  => 'core/modules/mysql/src/Driver/Database/mysql/',
  'database'  => getenv('DRUPAL_DB_NAME'),
  'username'  => getenv('DRUPAL_DB_USER'),
  'password'  => getenv('DRUPAL_DB_PASS'),
  'host'      => getenv('DRUPAL_DB_HOST') ?: 'mariadb',
  'port'      => getenv('DRUPAL_DB_PORT') ?: '3306',
  'prefix'    => '',
  'collation' => 'utf8mb4_general_ci',
];

// Hash salt — must be set in .env
$settings['hash_salt'] = getenv('DRUPAL_HASH_SALT');

// Config sync directory
$settings['config_sync_directory'] = '../config/sync';

// Trusted host patterns — set in .env as comma-separated list
// e.g. DRUPAL_TRUSTED_HOSTS=^example\.com$,^www\.example\.com$
if ($trusted = getenv('DRUPAL_TRUSTED_HOSTS')) {
  $settings['trusted_host_patterns'] = array_map('trim', explode(',', $trusted));
}

// Files
$settings['file_public_path']  = 'sites/default/files';
$settings['file_private_path'] = '/var/www/private';

// Reverse proxy (Caddy sits in front).
// Resolve Caddy's current Docker-internal IP so Drupal trusts X-Forwarded-For.
// Port 9000 is not exposed to the host, so only containers on the Docker network can reach PHP-FPM.
$settings['reverse_proxy'] = TRUE;
$caddy_ip = gethostbyname('caddy');
$settings['reverse_proxy_addresses'] = ($caddy_ip !== 'caddy') ? [$caddy_ip] : [];
// Required since Drupal 9 for reverse_proxy to take effect.
$settings['reverse_proxy_trusted_headers'] =
  \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_FOR |
  \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_PROTO |
  \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_PORT |
  \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_HOST;

// Performance
$config['system.performance']['css']['preprocess'] = TRUE;
$config['system.performance']['js']['preprocess']  = TRUE;
$settings['skip_permissions_hardening'] = FALSE;

// Environment indicator
$config['environment_indicator.indicator']['bg_color'] = '#BE0001';
$config['environment_indicator.indicator']['fg_color'] = '#ffffff';
$config['environment_indicator.indicator']['name']     = getenv('DRUPAL_ENV') ?: 'Production';
