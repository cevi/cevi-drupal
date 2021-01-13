<?php

namespace Drupal\cevi_base\TwigExtension;

use Drupal\Core\Template\TwigExtension;
use Twig_SimpleFunction;

/**
 * A Twig extension to hash emails.
 */
class EmailTwigExtension extends TwigExtension {

  /**
   * Generates a list of all Twig functions that this extension defines.
   *
   * @return array
   *   A key/value array that defines custom Twig functions. The key denotes the
   *   function name used in the tag, e.g.:
   *
   * @code
   *   {{ testfunc() }}
   * @endcode
   *
   *   The value is a standard PHP callback that defines what the function does.
   */
  public function getFunctions() {
    return [
      'email_obfuscator' => new Twig_SimpleFunction('email_obfuscator', [$this, 'emailObfuscator']),
      'phone_obfuscator' => new Twig_SimpleFunction('phone_obfuscator', [$this, 'phoneObfuscator']),
    ];
  }

  /**
   * Get the unique name of this extension.
   *
   * @return string
   *   Name of the extension.
   */
  public function getName() {
    return 'cevi_base.email_twig_extension';
  }

  /**
   * Returns a hashed email object (<div>) for javascript.
   */
  public function emailObfuscator($email, $classes = [], $content = NULL) {
    if (is_array($email)) {
      if (isset($email['#context']['value'])) {
        $email = $email['#context']['value'];
      }
    }

    $mail_string = str_replace(['.'], ['/c/'], $email);
    $mail_split = explode('@', $mail_string);

    $class_string = implode(' ', $classes);

    if ($content != NULL) {
      $content = str_replace($email, '/c/', $content);
    }
    else {
      $content = '/c/';
    }

    $content = "<a href='#' class='-obfuscated $class_string' data-obfuscate data-start='$mail_split[0]' data-end='$mail_split[1]' data-replace-inner>$content</a>";

    return ['#markup' => $content];
  }

  /**
   * Returns a hashed email object (<div>) for javascript.
   */
  public function phoneObfuscator($phone, $classes = []) {
    if (is_array($phone)) {
      if (isset($phone['#context']['value'])) {
        $phone = $phone['#context']['value'];
      }
    }

    $phone_string = str_replace(['.'], [' '], $phone);
    $phone_split = explode(' ', $phone_string);

    $class_string = implode(' ', $classes);
    $content = '/c/';

    $data_string = '';

    foreach ($phone_split as $key => $split_item) {
      $data_string .= " data-phone-$key='$split_item'";
    }

    $count = count($phone_split);
    $data_string .= " data-phone-length='$count'";

    $content = "<a href='#' class='-obfuscated -phone $class_string' data-obfuscate data-obfuscate-phone $data_string data-replace-inner>$content</a>";

    return ['#markup' => $content];
  }

}
