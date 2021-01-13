<?php

namespace Drupal\swisstopo\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\swisstopo\SwisstopoService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Plugin implementation of the 'field_example_text' widget.
 *
 * @FieldWidget(
 *   id = "swisstopo_widget",
 *   module = "swisstopo",
 *   label = @Translation("swisstopo Widget"),
 *   field_types = {
 *     "field_swisstopo"
 *   }
 * )
 */
class SwisstopoWidget extends WidgetBase implements ContainerFactoryPluginInterface {

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
   *   The plugin_id for the widget.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
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
    array $third_party_settings,
    SwisstopoService $swisstopo_service
  ) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->swisstopoService = $swisstopo_service;
  }

  /**
   * Create the Widget.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The ContainerInterface.
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   Plugin id of the Widget.
   * @param mixed $plugin_definition
   *   Plugin definition of the Widget.
   *
   * @return \Drupal\swisstopo\Plugin\Field\FieldWidget\SwisstopoWidget
   *   Returns the created SwisstopoWidget.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('swisstopo.service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['x'] = [
      '#type' => 'textfield',
      '#default_value' => isset($items[$delta]->x) ? $items[$delta]->x : '',
      '#size' => 20,
      '#maxlength' => 7,
      '#title' => $this->t('Swiss Grid X'),
      '#description' => $this->t('The Latitude in the Swisstopo format (e.g. 2600000)'),
      '#element_validate' => [
        [static::class, 'validate'],
      ],
    ];

    $element['y'] = [
      '#type' => 'textfield',
      '#default_value' => isset($items[$delta]->y) ? $items[$delta]->y : '',
      '#size' => 20,
      '#maxlength' => 7,
      '#title' => $this->t('Swiss Grid Y'),
      '#description' => $this->t('The Longitude in the Swisstopo format (e.g. 1200000)'),
      '#element_validate' => [
        [static::class, 'validate'],
      ],
    ];

    $element['zoom'] = [
      '#type' => 'select',
      '#default_value' => isset($items[$delta]->zoom) ? $items[$delta]->zoom : '',
      '#title' => $this->t('Zoom'),
      '#description' => $this->t('Zoom factor of the map'),
      '#options' => $this->swisstopoService->getZoomOptionsArray(),
    ];

    return $element;
  }

  /**
   * Validate the values of the coordinates-field "field_swisstopo".
   *
   * @param mixed $element
   *   The element which will be validated.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public static function validate($element, FormStateInterface $form_state) {
    $value = $element['#value'];
    if (strlen($value) == 0) {
      $form_state->setValueForElement($element, '');
      return;
    }
    if (strlen($value) !== 7 && strlen($value) !== 6) {
      $form_state->setError($element, t("The swiss coordinates must contain 6 or 7 numbers."));
    }
    if (!preg_match('/([0-9])$/iD', strtolower($value))) {
      $form_state->setError($element, t("Coordinates must be numeric."));
    }
  }

}
