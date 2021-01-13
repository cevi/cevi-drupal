<?php

namespace Drupal\cevi_base\Controller;

use Drupal\cevi_base\Service\CeviBaseFormService;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\Utility\Unicode;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A controller for all the favicons.
 */
class FaviconController extends ControllerBase {

  protected $configFactory;
  protected $fileMimeTypeGuesser;

  /**
   * Create the Controller.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container interface.
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    // Get your services here.
    return new static(
      $container->get('config.factory'),
      $container->get('file.mime_type.guesser')
    );
  }

  /**
   * TrophyEmailController constructor.
   *
   * @param object $config_factory
   *   Drupal's ConfigFactory.
   * @param object $file_mime_type_guesser
   *   Drupal Service file.mime_type.guesser.
   */
  public function __construct($config_factory, $file_mime_type_guesser) {
    $this->configFactory = $config_factory;
    $this->fileMimeTypeGuesser = $file_mime_type_guesser;
  }

  /**
   * Returns a redirect to the correct file.
   */
  public function favicon() {
    $favicon_uri = '';
    $config = $this->configFactory->get(CeviBaseFormService::CEVI_ADMIN_SETTINGS_FORM_NAME);
    $fileStorage = $this->entityTypeManager()->getStorage('file');
    $favicon_id = $config->get(CeviBaseFormService::FAVICON);

    if (isset($favicon_id[0])) {
      $favicon_file = $fileStorage->load($favicon_id[0]);
      if ($favicon_file) {
        $favicon_uri = $favicon_file->get('uri')->getValue()[0]['value'];
      }
    }

    if (!$favicon_uri) {
      $this->redirect('front');
    }

    $mime = $this->fileMimeTypeGuesser->guess($favicon_uri);

    $headers = [
      'Content-Type' => $mime . '; name="' . Unicode::mimeHeaderEncode(basename($favicon_uri)) . '"',
      'Content-Length' => filesize($favicon_uri),
      'Content-Disposition' => 'inline; filename="' . Unicode::mimeHeaderEncode(basename($favicon_uri)) . '"',
      'Cache-Control' => 'private',
    ];

    return new BinaryFileResponse($favicon_uri, 200, $headers);
  }

}
