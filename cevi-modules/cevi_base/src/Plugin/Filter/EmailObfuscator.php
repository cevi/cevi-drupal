<?php

namespace Drupal\cevi_base\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * Provide a filter to obfuscate emails.
 *
 * The filter turns all mailto anchor tags into hidden javascript data-eleents
 * and optionally replace inner text.
 *
 * @see https://github.com/WondrousLLC/obfuscate_email/blob/master/src/Plugin/Filter/ObfuscateEmail.php
 *
 * @Filter(
 *   id = "filter_cevi_base",
 *   title = @Translation("Emails verschlüsseln / Spamschutz"),
 *   description = @Translation("Verschlüsselt alle <code>mailto</code>-Links."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE
 * )
 */
class EmailObfuscator extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);

    if (stristr($text, 'mailto') === FALSE) {
      return $result;
    }

    $dom = Html::load($text);
    $xpath = new \DOMXPath($dom);

    /** @var \DOMElement $domElement */
    foreach ($xpath->query('//a[starts-with(@href, "mailto:")]') as $domElement) {
      // Read the href attribute value and delete it.
      $href = str_replace('mailto:', '', $domElement->getAttribute('href'));
      $domElement->setAttribute('href', '#');

      $mail_string = str_replace(['.'], ['/c/'], $href);
      $mail_splitted = explode('@', $mail_string);
      $domElement->setAttribute('data-start', $mail_splitted[0]);
      $domElement->setAttribute('data-end', $mail_splitted[1]);
      $domElement->setAttribute('data-obfuscate', '');
      $domElement->setAttribute('class', '-obfuscated');

      // Replace occurrence of the address in the anchor text.
      if (strpos($domElement->nodeValue, $href) !== FALSE) {
        $domElement->nodeValue = str_replace($href, '/c/', $domElement->nodeValue);
        $domElement->setAttribute('data-replace-inner', '');
      }
    }

    $result->setProcessedText(Html::serialize($dom));
    return $result;
  }

}
