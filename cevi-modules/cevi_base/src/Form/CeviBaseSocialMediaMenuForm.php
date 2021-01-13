<?php

namespace Drupal\cevi_base\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\cevi_base\Service\CeviBaseFormService;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure the settings for the config-menu.
 */
class CeviBaseSocialMediaMenuForm extends ConfigFormBase {

  protected $entityTypeManager;

  /**
   * CeviBaseSocialMediaMenuForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The default config_factory.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The default entityTypeManager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManager $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
    parent::__construct($config_factory);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cevi_social_media_menu';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      CeviBaseFormService::SOCIAL_MEDIA_MENU_NAME,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(CeviBaseFormService::SOCIAL_MEDIA_MENU_NAME);

    $form[CeviBaseFormService::SOCIAL_MEDIA_FACEBOOK] = [
      '#title' => $this->t('Facebook'),
      '#description' => $this->t('Link zum Facebook-Account für den Link. Leer lassen wenn keine Facebook-Seite verlinkt werden soll.'),
      '#type' => 'url',
      '#default_value' => $config->get(CeviBaseFormService::SOCIAL_MEDIA_FACEBOOK),
    ];

    $form[CeviBaseFormService::SOCIAL_MEDIA_INSTAGRAM] = [
      '#title' => $this->t('Instagram'),
      '#description' => $this->t('Link zum Instagram-Account für den Link. Leer lassen wenn keine Instagram-Seite verlinkt werden soll.'),
      '#type' => 'url',
      '#default_value' => $config->get(CeviBaseFormService::SOCIAL_MEDIA_INSTAGRAM),
    ];

    $form[CeviBaseFormService::SOCIAL_MEDIA_TWITTER] = [
      '#title' => $this->t('Twitter'),
      '#description' => $this->t('Link zum Twitter-Account für den Link. Leer lassen wenn kein Twitter-Account verlinkt werden soll.'),
      '#type' => 'url',
      '#default_value' => $config->get(CeviBaseFormService::SOCIAL_MEDIA_TWITTER),
    ];

    $form[CeviBaseFormService::SOCIAL_MEDIA_GOOGLE_PLUS] = [
      '#title' => $this->t('Google Plus'),
      '#description' => $this->t('Link zum Google-Plus-Account für den Link. Leer lassen wenn kein Google-Plus-Account verlinkt werden soll.'),
      '#type' => 'url',
      '#default_value' => $config->get(CeviBaseFormService::SOCIAL_MEDIA_GOOGLE_PLUS),
    ];

    $form[CeviBaseFormService::SOCIAL_MEDIA_SNAPCHAT] = [
      '#title' => $this->t('Snapchat'),
      '#description' => $this->t('Link zum Snapchat-Account für den Link. Leer lassen wenn kein Snapchat-Account verlinkt werden soll.'),
      '#type' => 'url',
      '#default_value' => $config->get(CeviBaseFormService::SOCIAL_MEDIA_SNAPCHAT),
    ];

    $form[CeviBaseFormService::SOCIAL_MEDIA_YOUTUBE] = [
      '#title' => $this->t('Youtube'),
      '#description' => $this->t('Link zum Youtube-Account für den Link. Leer lassen wenn kein Youtube-Account verlinkt werden soll.'),
      '#type' => 'url',
      '#default_value' => $config->get(CeviBaseFormService::SOCIAL_MEDIA_YOUTUBE),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $this->configFactory->getEditable(CeviBaseFormService::SOCIAL_MEDIA_MENU_NAME) // @codingStandardsIgnoreLine
      // Set the submitted configuration setting.
      ->set(CeviBaseFormService::SOCIAL_MEDIA_FACEBOOK, $form_state->getValue(CeviBaseFormService::SOCIAL_MEDIA_FACEBOOK))
      ->set(CeviBaseFormService::SOCIAL_MEDIA_INSTAGRAM, $form_state->getValue(CeviBaseFormService::SOCIAL_MEDIA_INSTAGRAM))
      ->set(CeviBaseFormService::SOCIAL_MEDIA_TWITTER, $form_state->getValue(CeviBaseFormService::SOCIAL_MEDIA_TWITTER))
      ->set(CeviBaseFormService::SOCIAL_MEDIA_GOOGLE_PLUS, $form_state->getValue(CeviBaseFormService::SOCIAL_MEDIA_GOOGLE_PLUS))
      ->set(CeviBaseFormService::SOCIAL_MEDIA_SNAPCHAT, $form_state->getValue(CeviBaseFormService::SOCIAL_MEDIA_SNAPCHAT))
      ->set(CeviBaseFormService::SOCIAL_MEDIA_YOUTUBE, $form_state->getValue(CeviBaseFormService::SOCIAL_MEDIA_YOUTUBE))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
