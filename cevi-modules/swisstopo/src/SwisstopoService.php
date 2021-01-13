<?php

namespace Drupal\swisstopo;

/**
 * Provides a Swisstopo class.
 */
class SwisstopoService {

  protected $zoomVariations = [
    650,
    500,
    250,
    100,
    50,
    20,
    10,
    5,
    2.5,
    2,
    1,
    0.5,
    0.25,
    0.1,
  ];

  protected $storageFactor = 100;

  /**
   * Get the full array with all possible zoom-options.
   *
   * @return array
   *   The array with a key ready for the storage.
   */
  public function getZoomOptionsArray() {
    $zoom_options = [];
    foreach ($this->zoomVariations as $zoom) {
      $zoom_options[$zoom * $this->storageFactor] = $zoom;
    }
    return $zoom_options;
  }

  /**
   * Get the correct zoom from a value which was saved in storage.
   *
   * @param mixed $zoom
   *   The value from the storage.
   *
   * @return float|int
   *   The correct zoom-value.
   */
  public function getZoomFromStorage($zoom) {
    return $zoom / $this->storageFactor;
  }

}
