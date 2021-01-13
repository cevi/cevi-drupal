<?php

namespace Drupal\swisstopo\Plugin\Field\FieldType;

use Drupal;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Plugin implementation of the 'field_example_rgb' field type.
 *
 * @FieldType(
 *   id = "field_swisstopo",
 *   label = @Translation("Swisstopo Geofield"),
 *   module = "swisstopo",
 *   description = @Translation("Create the geofield for swisstopo based on swiss coordinates"),
 *   default_widget = "swisstopo_widget",
 *   default_formatter = "swisstopo_formatter"
 * )
 */
class SwisstopoItem extends FieldItemBase {

  const DEFAULT_MARKER_URI = 'public://swisstopo_marker/orig/marker.png';

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::fieldSettingsForm($form, $form_state);
    $settings = $this->getSettings();
    $fids = [];

    if (!empty($settings['marker'])) {
      $fids = $settings['marker'];
    }

    $element['marker'] = [
      '#type' => 'managed_file',
      '#title' => t('Map Marker'),
      '#description' => t('The marker which is added to all maps of this field instance. If the field is empty, the default marker will be loaded.'),
      '#default_value' => $fids,
      '#upload_location' => 'public://swisstopo_marker/',
      '#element_validate' => [
        '\Drupal\file\Element\ManagedFile::validateManagedFile',
      ],
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function isEmpty() {
    $lat = $this->get('x')->getValue();
    $lon = $this->get('y')->getValue();
    return $lat === NULL || $lat === '' || $lon === NULL || $lon === '';
  }

  /**
   * Get the marker from the settings.
   *
   * Load the default marker if the settings are empty.
   *
   * @return string
   *   Url of the marker.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getMarker() {
    $fileStorage = Drupal::entityTypeManager()->getStorage('file');

    if ($settings = $this->getFieldDefinition()->getSettings()) {
      if (isset($settings['marker'][0])) {
        $marker_file = $fileStorage->load($settings['marker'][0]);
        if ($marker_file) {
          $marker_uri = $marker_file->get('uri')->getValue()[0]['value'];
          return file_create_url($marker_uri);
        }
      }
    }

    // Return the default Url of the marker.
    return file_create_url($this::DEFAULT_MARKER_URI);
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    $marker_id = SwisstopoItem::loadDefaultMarkerImageId();

    if (!$marker_id) {
      $marker_id = '';
    }

    return [
      'marker' => [$marker_id],
    ] + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function defaultFieldSettings() {
    $marker_id = SwisstopoItem::loadDefaultMarkerImageId();

    if (!$marker_id) {
      $marker_id = '';
    }

    $settings = [
      'marker' => [$marker_id],
    ];

    return $settings + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $columns = [];

    $columns['x'] = [
      'type' => 'numeric',
      'size' => 'normal',
      'length' => 7,
    ];

    $columns['y'] = [
      'type' => 'numeric',
      'size' => 'normal',
      'length' => 7,
    ];

    $columns['zoom'] = [
      'type' => 'float',
    ];

    return [
      'columns' => $columns,
      'indexes' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['x'] = DataDefinition::create('integer')
      ->setLabel(t('Swiss Coordinates X'));

    $properties['y'] = DataDefinition::create('integer')
      ->setLabel(t('Swiss Coordinates Y'));

    $properties['zoom'] = DataDefinition::create('float')
      ->setLabel(t('Zoom Level'));

    return $properties;
  }

  /**
   * Load the default marker-image.
   *
   * Copy the correct image to the files-directory and link it correctly to the
   * Swisstopo-Item as a file.
   *
   * @return null|string
   *   Return the UUID of the default marker-image.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected static function loadDefaultMarkerImageId() {
    $image_uri = SwisstopoItem::DEFAULT_MARKER_URI;
    $module_path = drupal_get_path('module', 'swisstopo');

    $files = Drupal::entityTypeManager()->getStorage('file')->loadByProperties(['uri' => $image_uri]);

    if (empty($files)) {
      $file_system = Drupal::service('file_system');
      $image_path_module = $file_system->realpath($module_path . '/assets/images/marker.png');
      $module_path_public = $file_system->realpath('public://') . '/swisstopo_marker/orig';
      $image_path_public = $module_path_public . '/marker.png';

      if (file_prepare_directory($module_path_public, FILE_CREATE_DIRECTORY)) {
        if (file_unmanaged_copy($image_path_module, $image_path_public, FILE_EXISTS_RENAME)) {
          // File could be copied successfully.
        }
        else {
          Drupal::messenger()->addError(t('The default map-marker-file could not be copied to %file.', ['%file' => $image_path_public]));
          return FALSE;
        }
      }
      else {
        Drupal::messenger()->addError(t('The directory %dir could not be prepared.', ['%dir' => $module_path_public]));
        return FALSE;
      }

      $image = File::create();
      $image->setFileUri($image_uri);
      $image->setOwnerId(\Drupal::currentUser()->id());
      $image->setMimeType('image/png');
      $image->setFileName('marker.png');
      $image->setPermanent();
      $image->save();
    }
    else {
      $image = $files[key($files)];
    }

    return $image->id();
  }

}
