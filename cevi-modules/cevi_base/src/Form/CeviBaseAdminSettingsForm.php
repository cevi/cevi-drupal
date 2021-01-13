<?php

namespace Drupal\cevi_base\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\cevi_base\Service\CeviBaseFormService;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\file\FileUsage\DatabaseFileUsageBackend;
use Drupal\Core\Database\Driver\mysql\Connection;

/**
 * Configure the settings for the settings-form.
 */
class CeviBaseAdminSettingsForm extends ConfigFormBase {

  protected $entityTypeManager;

  /**
   * Drupal's FileStorage.
   *
   * @var \Drupal\file\FileStorage
   */
  protected $fileStorage;

  /**
   * Drupal's FileUsage.
   *
   * @var \Drupal\file\FileUsage\FileUsageInterface
   */
  protected $fileUsage;

  /**
   * Drupal's Database.
   *
   * @var \Drupal\Core\Database\Driver\mysql\Connection
   */
  protected $database;

  /**
   * CeviBaseAdminSettingsForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The default config_factory.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The default entityTypeManager.
   * @param \Drupal\file\FileUsage\DatabaseFileUsageBackend $file_usage
   *   The default File-Usage-Service.
   * @param \Drupal\Core\Database\Driver\mysql\Connection $database
   *   The default Database.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManager $entity_type_manager, DatabaseFileUsageBackend $file_usage, Connection $database) {
    $this->entityTypeManager = $entity_type_manager;
    $this->fileStorage = $this->entityTypeManager->getStorage('file');
    $this->fileUsage = $file_usage;
    $this->database = $database;
    parent::__construct($config_factory);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('file.usage'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cevi_admin_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      CeviBaseFormService::CEVI_ADMIN_SETTINGS_FORM_NAME,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(CeviBaseFormService::CEVI_ADMIN_SETTINGS_FORM_NAME);

    $form[CeviBaseFormService::CLAIM_LEFT] = [
      '#title' => $this->t('Claim: Links'),
      '#description' => $this->t('Claim auf der Startseite: links vom Spickel.'),
      '#type' => 'textfield',
      '#maxlength' => 1024,
      '#default_value' => $config->get(CeviBaseFormService::CLAIM_LEFT),
    ];

    $form[CeviBaseFormService::CLAIM_RIGHT] = [
      '#title' => $this->t('Claim: Rechts'),
      '#description' => $this->t('Claim auf der Startseite: rechts vom Spickel.'),
      '#type' => 'textfield',
      '#maxlength' => 1024,
      '#default_value' => $config->get(CeviBaseFormService::CLAIM_RIGHT),
    ];

    $form[CeviBaseFormService::CLAIM_LOGO_LEFT] = [
      '#title' => $this->t('Logo: Links'),
      '#description' => $this->t('Claim auf der Startseite: linker Text vom Logo.'),
      '#type' => 'textfield',
      '#maxlength' => 1024,
      '#default_value' => $config->get(CeviBaseFormService::CLAIM_LOGO_LEFT),
    ];

    $form[CeviBaseFormService::CLAIM_LOGO_RIGHT] = [
      '#title' => $this->t('Logo: Rechts'),
      '#description' => $this->t('Claim auf der Startseite: rechter Text vom Logo. Leer lassen, damit kein Cevi-Logo angezeigt wird.'),
      '#type' => 'textfield',
      '#maxlength' => 1024,
      '#default_value' => $config->get(CeviBaseFormService::CLAIM_LOGO_RIGHT),
    ];

    $form[CeviBaseFormService::CLAIM_LOGO_RIGHT_SECOND] = [
      '#title' => $this->t('Logo: Rechts, 2. Zeile'),
      '#description' => $this->t('Claim auf der Startseite: zweite Zeile vom rechten Text vom Logo. Leer lassen, damit der rechte Text nur einzeilig ist.'),
      '#type' => 'textfield',
      '#maxlength' => 1024,
      '#default_value' => $config->get(CeviBaseFormService::CLAIM_LOGO_RIGHT_SECOND),
    ];

    $form[CeviBaseFormService::HEADER_LOGO] = [
      '#title' => $this->t('Header-Logo'),
      '#description' => $this->t('Das Logo oben rechts auf der normalen Webseiten-Ansicht'),
      '#type' => 'managed_file',
      '#upload_location' => 'public://cevi-config/',
      '#default_value' => $config->get(CeviBaseFormService::HEADER_LOGO),
      '#upload_validators' => [
        'file_validate_extensions' => ['png gif jpg jpeg svg'],
      ],
    ];

    $form[CeviBaseFormService::HEADER_LOGO_SMALL] = [
      '#title' => $this->t('Header-Logo Mobile'),
      '#description' => $this->t('Das Logo oben rechts auf der mobilen Webseiten-Ansicht'),
      '#type' => 'managed_file',
      '#upload_location' => 'public://cevi-config/',
      '#default_value' => $config->get(CeviBaseFormService::HEADER_LOGO_SMALL),
      '#upload_validators' => [
        'file_validate_extensions' => ['png gif jpg jpeg svg'],
      ],
    ];

    $form[CeviBaseFormService::FAVICON] = [
      '#title' => $this->t('Favicon'),
      '#description' => $this->t('Das Favicon der Webseite, wird im Browser-Tab angezeigt'),
      '#type' => 'managed_file',
      '#upload_location' => 'public://cevi-config/',
      '#default_value' => $config->get(CeviBaseFormService::FAVICON),
      '#upload_validators' => [
        'file_validate_extensions' => ['png gif jpg jpeg svg ico'],
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $file_header_logo_id = $form_state->getValue(CeviBaseFormService::HEADER_LOGO);
    $file_header_logo_small_id = $form_state->getValue(CeviBaseFormService::HEADER_LOGO_SMALL);
    $file_favion = $form_state->getValue(CeviBaseFormService::FAVICON);

    // Retrieve the configuration.
    $this->configFactory->getEditable(CeviBaseFormService::CEVI_ADMIN_SETTINGS_FORM_NAME) // @codingStandardsIgnoreLine
      // Set the submitted configuration setting.
      ->set(CeviBaseFormService::CLAIM_LEFT, $form_state->getValue(CeviBaseFormService::CLAIM_LEFT))
      ->set(CeviBaseFormService::CLAIM_RIGHT, $form_state->getValue(CeviBaseFormService::CLAIM_RIGHT))
      ->set(CeviBaseFormService::CLAIM_LOGO_LEFT, $form_state->getValue(CeviBaseFormService::CLAIM_LOGO_LEFT))
      ->set(CeviBaseFormService::CLAIM_LOGO_RIGHT, $form_state->getValue(CeviBaseFormService::CLAIM_LOGO_RIGHT))
      ->set(CeviBaseFormService::CLAIM_LOGO_RIGHT_SECOND, $form_state->getValue(CeviBaseFormService::CLAIM_LOGO_RIGHT_SECOND))
      ->set(CeviBaseFormService::HEADER_LOGO, $file_header_logo_id)
      ->set(CeviBaseFormService::HEADER_LOGO_SMALL, $file_header_logo_small_id)
      ->set(CeviBaseFormService::FAVICON, $file_favion)
      ->save();

    // Save the files as used:
    $this->saveFileAsUsed($file_header_logo_id, 'header_logo');
    $this->saveFileAsUsed($file_header_logo_small_id, 'header_logo_small');
    $this->saveFileAsUsed($file_favion, 'favicon');

    parent::submitForm($form, $form_state);
  }

  /**
   * Save a file as used by this module.
   *
   * Otherwise it will be deleted by the next cron-run.
   *
   * @param array $file_id
   *   The file_id.
   * @param string $name
   *   The type of the file.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function saveFileAsUsed(array $file_id, $name) {
    if (isset($file_id[0])) {
      // Delete the files as used files first.
      $this->deleteFilesFromUsed($file_id[0], $name);

      $file = $this->fileStorage->load($file_id[0]);
      if ($file) {
        $list = $this->fileUsage->listUsage($file);
        if (empty($list)) {
          $this->fileUsage->add($file, 'cevi_base', $name, 1);
        }
      }
    }
  }

  /**
   * Delete all files of this module and with the type $type as used files.
   *
   * @param int $fid
   *   The file id which will be created. Do not delete this file.
   * @param string $type
   *   The type of the file.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function deleteFilesFromUsed($fid, $type) {
    $query = $this->database->query("SELECT fid, id FROM {file_usage} WHERE module = 'cevi_base' AND type = '$type'");
    $result = $query->fetchAll();

    foreach ($result as $record) {
      if ($record->fid !== $fid) {
        $file = $this->fileStorage->load($record->fid);
        $file->delete();
        $delete = $this->database->delete('file_usage')
          ->condition('fid', $record->fid)
          ->condition('id', $record->id)
          ->condition('type', $type)
          ->condition('module', 'cevi_base');

        $delete->execute();
      }
    }
  }

}
