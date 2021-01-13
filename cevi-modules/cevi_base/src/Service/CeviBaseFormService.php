<?php

namespace Drupal\cevi_base\Service;

use Drupal\Core\Entity\EntityTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Generic service for centralized base module methods.
 *
 * @package Drupal\heimat_admin
 */
class CeviBaseFormService {

  const SOCIAL_MEDIA_MENU_NAME = 'cevi_base.social_media_settings';
  const SOCIAL_MEDIA_FACEBOOK = 'social_media_facebook';
  const SOCIAL_MEDIA_INSTAGRAM = 'social_media_instagram';
  const SOCIAL_MEDIA_TWITTER = 'social_media_twitter';
  const SOCIAL_MEDIA_SNAPCHAT = 'social_media_snapchat';
  const SOCIAL_MEDIA_GOOGLE_PLUS = 'social_media_google_plus';
  const SOCIAL_MEDIA_YOUTUBE = 'social_media_youtube';
  const CEVI_ADMIN_SETTINGS_FORM_NAME = 'cevi_base.cevi_admin_settings';
  const CLAIM_LEFT = 'cevi_base_claim_left';
  const CLAIM_RIGHT = 'cevi_base_claim_right';
  const CLAIM_LOGO_LEFT = 'cevi_base_claim_logo_left';
  const CLAIM_LOGO_RIGHT = 'cevi_base_claim_logo_right';
  const CLAIM_LOGO_RIGHT_SECOND = 'cevi_base_claim_logo_right_second';
  const HEADER_LOGO = 'cevi_base_header_logo';
  const HEADER_LOGO_SMALL = 'cevi_base_header_logo_small';
  const FAVICON = 'cevi_base_favicon';

  protected $entityTypeManager;
  protected $configFactory;

  /**
   * HeimatAdminService constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The default config_factory.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The default EntityTypeManager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManager $entity_type_manager) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
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
   * Load Social Media Links based on the CeviBaseSocialMediaMenuForm.
   *
   * @return array
   *   The usable links.
   */
  public function loadSocialMediaLinks() {
    $config = $this->configFactory->get(CeviBaseFormService::SOCIAL_MEDIA_MENU_NAME);
    $social_media_links = [];

    $facebook = $config->get(CeviBaseFormService::SOCIAL_MEDIA_FACEBOOK);
    $twitter = $config->get(CeviBaseFormService::SOCIAL_MEDIA_TWITTER);
    $snapchat = $config->get(CeviBaseFormService::SOCIAL_MEDIA_SNAPCHAT);
    $gplus = $config->get(CeviBaseFormService::SOCIAL_MEDIA_GOOGLE_PLUS);
    $instagram = $config->get(CeviBaseFormService::SOCIAL_MEDIA_INSTAGRAM);
    $youtube = $config->get(CeviBaseFormService::SOCIAL_MEDIA_YOUTUBE);

    if ($facebook) {
      $social_media_links['facebook'] = $facebook;
    }

    if ($twitter) {
      $social_media_links['twitter'] = $twitter;
    }

    if ($snapchat) {
      $social_media_links['snapchat'] = $snapchat;
    }

    if ($gplus) {
      $social_media_links['gplus'] = $gplus;
    }

    if ($instagram) {
      $social_media_links['instagram'] = $instagram;
    }

    if ($youtube) {
      $social_media_links['youtube'] = $youtube;
    }

    return $social_media_links;
  }

}
