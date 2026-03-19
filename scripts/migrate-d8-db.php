#!/usr/bin/env php
<?php
/**
 * migrate-d8-db.php
 *
 * Run this ONCE after importing a Drupal 8/9 DB dump into Docker.
 * It patches DB-level config so Drupal 10/11 can bootstrap cleanly.
 *
 * Usage (from repo root):
 *   docker compose exec drupal php scripts/migrate-d8-db.php
 *
 * Idempotency: most steps are safe to re-run (they check before acting).
 * EXCEPTION: the CKEditor 4 → 5 migration rewrites editor.editor.* rows — if
 * you have already configured CKEditor 5 via the UI, do NOT re-run this script
 * as it will overwrite those settings.
 *
 * What it fixes:
 *  1. core.extension: remove D10/D11-removed modules (ckeditor, color, quickedit, rdf, statistics, tour, tracker)
 *                     and themes (stable, classy, seven); add ckeditor5, claro
 *  2. editor.editor.*: rewrite from CKEditor 4 (serialized) to CKEditor 5 format
 *  3. system.schema key_value: remove orphaned entries for removed modules/themes
 *  4. Disables maintenance mode
 */

$host = getenv('DRUPAL_DB_HOST') ?: 'mariadb';
$db   = getenv('DRUPAL_DB_NAME') ?: 'drupal';
$user = getenv('DRUPAL_DB_USER') ?: 'drupal';
$pass = getenv('DRUPAL_DB_PASS') ?: 'drupal';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    fwrite(STDERR, "DB connection failed: " . $e->getMessage() . "\n");
    exit(1);
}

echo "Connected to $db on $host\n";

// -------------------------------------------------------------------------
// 1. Fix core.extension
// -------------------------------------------------------------------------
$stmt = $pdo->query("SELECT data FROM config WHERE collection='' AND name='core.extension'");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
    echo "SKIP: core.extension not found (fresh install?)\n";
} else {
    $data = unserialize($row['data']);

    // Remove modules dropped from D10/D11 core
    foreach (['ckeditor', 'color', 'quickedit', 'rdf', 'statistics', 'tour', 'tracker'] as $m) {
        if (isset($data['module'][$m])) {
            unset($data['module'][$m]);
            echo "  Removed module: $m\n";
        }
    }
    // Add ckeditor5 if not present
    if (!isset($data['module']['ckeditor5'])) {
        $data['module']['ckeditor5'] = 0;
        echo "  Added module: ckeditor5\n";
    }

    // Remove themes dropped from D10 core
    foreach (['stable', 'classy', 'seven'] as $t) {
        if (isset($data['theme'][$t])) {
            unset($data['theme'][$t]);
            echo "  Removed theme: $t\n";
        }
    }
    // Add claro (base theme for ceviadmin) if not present
    if (!isset($data['theme']['claro'])) {
        $data['theme']['claro'] = 0;
        echo "  Added theme: claro\n";
    }

    $pdo->prepare("UPDATE config SET data=? WHERE collection='' AND name='core.extension'")
        ->execute([serialize($data)]);
    echo "core.extension updated (" . count($data['module']) . " modules, " . count($data['theme']) . " themes)\n";
}

// -------------------------------------------------------------------------
// 2. Fix editor.editor.basic_html
// -------------------------------------------------------------------------
$basic_html = [
  'uuid' => 'ab7e2b8f-9e42-451d-bcd6-f82685bf53b5',
  'langcode' => 'de', 'status' => true,
  'dependencies' => ['config' => ['filter.format.basic_html'], 'module' => ['ckeditor5']],
  '_core' => ['default_config_hash' => 'AqlPmO16LvJI4D0Ih6u4GFQIzqr5OnLgAUSjcUGWk2g'],
  'format' => 'basic_html', 'editor' => 'ckeditor5',
  'settings' => [
    'toolbar' => ['items' => ['bold','italic','|','link','|','bulletedList','numberedList','|','drupalInsertImage','blockQuote','|','heading']],
    'plugins' => [
      'ckeditor5_heading' => ['enabled_headings' => ['heading2','heading3','heading4']],
      'ckeditor5_image' => ['toolbar' => ['toggleImageCaption','imageTextAlternative','|','imageStyle:inline','imageStyle:block','imageStyle:side']],
      'ckeditor5_language' => ['language_list' => 'un'],
      'ckeditor5_list' => ['properties' => ['reversed' => false, 'startIndex' => false], 'multiBlock' => true],
    ],
  ],
  'image_upload' => ['status' => true, 'scheme' => 'public', 'directory' => 'inline-images', 'max_size' => '', 'max_dimensions' => ['width' => null, 'height' => null]],
];

// -------------------------------------------------------------------------
// 3. Fix editor.editor.full_html
// -------------------------------------------------------------------------
$full_html = [
  'uuid' => '2f0d0437-04d0-4671-af6c-15d2e9fab743',
  'langcode' => 'de', 'status' => true,
  'dependencies' => ['config' => ['filter.format.full_html'], 'module' => ['ckeditor5']],
  '_core' => ['default_config_hash' => '967ijj7p6i7rwrYl7r08WQFeCY_c23YAh0h8u-w_CXM'],
  'format' => 'full_html', 'editor' => 'ckeditor5',
  'settings' => [
    'toolbar' => ['items' => ['bold','italic','superscript','subscript','|','link','|','bulletedList','numberedList','|','drupalInsertImage','blockQuote','insertTable','horizontalLine','|','heading','|','sourceEditing']],
    'plugins' => [
      'ckeditor5_heading' => ['enabled_headings' => ['heading2','heading3','heading4','heading5','heading6']],
      'ckeditor5_image' => ['toolbar' => ['toggleImageCaption','imageTextAlternative','|','imageStyle:inline','imageStyle:block','imageStyle:side']],
      'ckeditor5_language' => ['language_list' => 'un'],
      'ckeditor5_list' => ['properties' => ['reversed' => false, 'startIndex' => false], 'multiBlock' => true],
      'ckeditor5_sourceEditing' => ['allowed_tags' => []],
    ],
  ],
  'image_upload' => ['status' => false, 'scheme' => 'public', 'directory' => 'inline-images', 'max_size' => '', 'max_dimensions' => ['width' => null, 'height' => null]],
];

foreach (['basic_html' => $basic_html, 'full_html' => $full_html] as $fmt => $cfg) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM config WHERE collection='' AND name='editor.editor.$fmt'");
    if ($stmt->fetchColumn()) {
        $pdo->prepare("UPDATE config SET data=? WHERE collection='' AND name='editor.editor.$fmt'")
            ->execute([serialize($cfg)]);
        echo "editor.editor.$fmt updated to ckeditor5\n";
    } else {
        $pdo->prepare("INSERT INTO config (collection, name, data) VALUES ('', ?, ?)")
            ->execute(["editor.editor.$fmt", serialize($cfg)]);
        echo "editor.editor.$fmt inserted (ckeditor5)\n";
    }
}

// -------------------------------------------------------------------------
// 4. Clean system.schema for removed modules/themes
// -------------------------------------------------------------------------
$removed = ['ckeditor', 'color', 'quickedit', 'rdf', 'stable', 'classy', 'seven', 'statistics', 'tour', 'tracker'];
$in = implode(',', array_fill(0, count($removed), '?'));
$stmt = $pdo->prepare("DELETE FROM key_value WHERE collection='system.schema' AND name IN ($in)");
$stmt->execute($removed);
echo "Cleaned " . $stmt->rowCount() . " orphaned system.schema entries\n";

// -------------------------------------------------------------------------
// 5. Disable maintenance mode
// -------------------------------------------------------------------------
$stmt = $pdo->query("SELECT data FROM config WHERE collection='' AND name='system.maintenance'");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row) {
    $cfg = unserialize($row['data']);
    $cfg['enabled'] = false;
    $pdo->prepare("UPDATE config SET data=? WHERE collection='' AND name='system.maintenance'")
        ->execute([serialize($cfg)]);
    echo "Maintenance mode disabled\n";
}

// -------------------------------------------------------------------------
// 6. Add mysql core module to core.extension (D10.3+ DB driver provider)
// -------------------------------------------------------------------------
$stmt = $pdo->query("SELECT data FROM config WHERE collection='' AND name='core.extension'");
$data = unserialize($stmt->fetchColumn());
if (!isset($data['module']['mysql'])) {
    $data['module']['mysql'] = 0;
    $pdo->prepare("UPDATE config SET data=? WHERE collection='' AND name='core.extension'")->execute([serialize($data)]);
    echo "Added mysql module to core.extension\n";
}

// -------------------------------------------------------------------------
// 7. Mark D8->D10 upgrade-path post_update hooks as applied
//    (live site never ran them; marking as done prevents fatal updb errors)
// -------------------------------------------------------------------------
$legacy_post_updates = [
  'block_post_update_replace_node_type_condition',
  'ckeditor5_post_update_alignment_buttons',
  'file_post_update_add_txt_if_allows_insecure_extensions',
  'honeypot_post_update_joyride_location_to_position',
  'honeypot_post_update_rebuild_service_container',
  'image_post_update_image_loading_attribute',
  'layout_builder_post_update_section_storage_context_mapping',
  'layout_builder_post_update_tempstore_route_enhancer',
  'media_post_update_modify_base_field_author_override',
  'node_post_update_glossary_view_published',
  'node_post_update_rebuild_node_revision_routes',
  'node_post_update_modify_base_field_author_override',
  'system_post_update_uninstall_simpletest',
  'system_post_update_uninstall_entity_reference_module',
  'system_post_update_entity_revision_metadata_bc_cleanup',
  'system_post_update_uninstall_classy',
  'system_post_update_uninstall_stable',
  'system_post_update_claro_dropbutton_variants',
  'system_post_update_schema_version_int',
  'system_post_update_delete_rss_settings',
  'system_post_update_remove_key_value_expire_all_index',
  'system_post_update_service_advisory_settings',
  'system_post_update_delete_authorize_settings',
  'system_post_update_sort_all_config',
  'system_post_update_enable_provider_database_driver',
  'taxonomy_post_update_clear_views_argument_validator_plugins_cache',
  'user_post_update_update_roles',
  'views_post_update_configuration_entity_relationships',
  'views_post_update_rename_default_display_setting',
  'views_post_update_remove_sorting_global_text_field',
  'views_post_update_title_translations',
  'views_post_update_sort_identifier',
  'views_post_update_provide_revision_table_relationship',
  'views_post_update_image_lazy_load',
];
$stmt = $pdo->query("SELECT value FROM key_value WHERE collection='post_update' AND name='existing_updates'");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$existing = $row ? unserialize($row['value']) : [];
$added = 0;
foreach ($legacy_post_updates as $hook) {
    if (!in_array($hook, $existing)) {
        $existing[] = $hook;
        $added++;
    }
}
if ($added > 0) {
    $pdo->prepare("UPDATE key_value SET value=? WHERE collection='post_update' AND name='existing_updates'")
        ->execute([serialize($existing)]);
    echo "Marked $added legacy post_update hooks as applied\n";
}

// -------------------------------------------------------------------------
// 8. Register mysql module in system.schema
// -------------------------------------------------------------------------
$pdo->prepare("INSERT INTO key_value (collection, name, value) VALUES ('system.schema', 'mysql', 's:4:\"8000\";') ON DUPLICATE KEY UPDATE value=value;")->execute();
echo "mysql schema entry ensured\n";

// -------------------------------------------------------------------------
// 9. Fix admin_toolbar_search schema + create help_search_items table
// -------------------------------------------------------------------------
$pdo->exec("UPDATE key_value SET value='i:8003;' WHERE collection='system.schema' AND name='admin_toolbar_search'");
$pdo->exec("UPDATE key_value SET value='i:8003;' WHERE collection='system.schema' AND name='admin_toolbar'");
$pdo->exec("CREATE TABLE IF NOT EXISTS help_search_items (
  sid INT UNSIGNED NOT NULL AUTO_INCREMENT,
  section_plugin_id VARCHAR(255) NOT NULL DEFAULT '',
  permission VARCHAR(255) NOT NULL DEFAULT '',
  topic_id VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (sid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "admin_toolbar schema fixed, help_search_items table ensured\n";

// -------------------------------------------------------------------------
// 10. Remove orphaned config for D11-dropped modules
// -------------------------------------------------------------------------
$pdo->exec("DELETE FROM config WHERE name LIKE 'statistics.%' OR name LIKE 'tracker.%' OR name = 'search.page.help_search'");
echo "Removed orphaned module config entries\n";

// -------------------------------------------------------------------------
// 11. Clear stale entity bundle field map cache
// -------------------------------------------------------------------------
$pdo->exec("DELETE FROM key_value WHERE collection='entity.definitions.bundle_field_map'");
echo "Cleared entity bundle field map cache\n";

echo "\nDone. You can now start the drupal container.\n";
