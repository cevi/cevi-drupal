<?php

namespace Drupal\swisstopo\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\swisstopo\SwisstopoService;

/**
 * Plugin implementation of the 'swisstopo Default' formatter.
 *
 * @FieldFormatter(
 *   id = "swisstopo_formatter",
 *   module = "swisstopo",
 *   label = @Translation("Swisstopo Default Output"),
 *   field_types = {
 *     "field_swisstopo"
 *   }
 * )
 */
class SwisstopoDefaultFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The SwisstopoService which helps with some services.
   *
   * @var \Drupal\swisstopo\SwisstopoService
   */
  protected $swisstopoService;

  /**
   * LeafletDefaultFormatter constructor.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings settings.
   * @param \Drupal\swisstopo\SwisstopoService $swisstopo_service
   *   The Swisstopo service.
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    $label,
    $view_mode,
    array $third_party_settings,
    SwisstopoService $swisstopo_service
  ) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->swisstopoService = $swisstopo_service;
  }

  /**
   * The Creator.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The Container interface.
   * @param array $configuration
   *   The configuration of the formatter.
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   *
   * @return \Drupal\swisstopo\Plugin\Field\FieldFormatter\SwisstopoDefaultFormatter
   *   Returns the created formatter.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('swisstopo.service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'output_format' => 'map',
    ];
  }

  /**
   * Helper function to get the formatter settings options.
   *
   * @return array
   *   The formatter settings options.
   */
  protected function formatOptions() {
    return [
      'raw' => $this->t("Raw"),
      'map' => $this->t("Rendered Map"),
    ];
  }

  /**
   * Returns the output format, set or default one.
   *
   * @return string
   *   The output format string.
   */
  protected function getOutputFormat() {
    return in_array($this->getSetting('output_format'), array_keys($this->formatOptions())) ? $this->getSetting('output_format') : self::defaultSettings()['output_format'];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['output_format'] = [
      '#title' => $this->t('Output Format'),
      '#type' => 'select',
      '#default_value' => $this->getOutputFormat(),
      '#options' => $this->formatOptions(),
      '#required' => TRUE,
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary[] = $this->t('Field output format: @format', ['@format' => $this->formatOptions()[$this->getOutputFormat()]]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {

      $x = $item->x;
      $y = $item->y;
      $zoom = $item->zoom;
      $marker = $item->getMarker();

      $output = [
        '#x' => $x,
        '#y' => $y,
        '#zoom' => $this->swisstopoService->getZoomFromStorage($zoom),
        '#marker' => $marker,
        '#theme' => 'swisstopo_map',
      ];

      if ($this->getOutputFormat() == 'raw') {
        $output['#theme'] = 'swisstopo_raw';
      }
      $element[$delta] = $output;
    }

    return $element;
  }

}
