<?php

$databases['default']['default'] = array (
  'database' => 'DATABASE_NAME',
  'username' => 'DATABASE_USER',
  'password' => 'DATABASE_PASSWORD',
  'prefix' => '',
  'host' => 'localhost',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);

assert_options(ASSERT_ACTIVE, TRUE);
\Drupal\Component\Assertion\Handle::register();

$config['system.logging']['error_level'] = 'verbose';

$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['js']['preprocess'] = FALSE;

$settings['container_yamls'][] = DRUPAL_ROOT . '/sites/development.services.yml';

$settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';
$settings['cache']['bins']['page'] = 'cache.backend.null';
$settings['cache']['bins']['render'] = 'cache.backend.null';
$settings['cache']['bins']['discovery_migration'] = 'cache.backend.memory';

$settings['twig_debug'] = TRUE;

$settings['extension_discovery_scan_tests'] = TRUE;

$settings['rebuild_access'] = FALSE;

$settings['skip_permissions_hardening'] = TRUE;

// Settings for drupal-module "environment_indicator".
$config['environment_indicator.indicator']['bg_color'] = '#014F01';
$config['environment_indicator.indicator']['fg_color'] = '#ffffff';
$config['environment_indicator.indicator']['name'] = 'Local';

// Settings for password protection
$config['shield.settings']['credentials']['shield']['user'] = NULL;
$config['shield.settings']['credentials']['shield']['pass'] = NULL;
$config['shield.settings']['print'] = 'This site is NOT protected by a username and password.';

// Trusted domain patterns:
$settings['trusted_host_patterns'] = [
    '^www\.drupal-cevi\.ch$',
    '^drupal-cevi\.ch$',
];
