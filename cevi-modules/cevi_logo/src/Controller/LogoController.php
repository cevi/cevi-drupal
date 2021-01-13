<?php

namespace Drupal\cevi_logo\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * A controller for the Logo-Generator.
 */
class LogoController extends ControllerBase {

  /**
   * Returns the default logo-generato.
   *
   * @return array
   *   The themeable array.
   */
  public function generator() {
    return [
      '#theme' => 'logo-generator',
      '#cache' => ['max-age' => 0],
    ];
  }

}
