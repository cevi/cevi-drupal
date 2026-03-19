<?php

namespace Drupal\allow_iframe\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * An event subscriber to remove the X-Frame-Options header.
 */
class RemoveXFrameOptionsSubscriber implements EventSubscriberInterface {

  /**
   * Remove the X-Frame-Options header.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   The event object.
   */
  public function removeXframeOptions(ResponseEvent $event) {
    if ($event->getRequest()->query->get('iframe')) {
      $response = $event->getResponse();
      $response->headers->remove('X-Frame-Options');
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['removeXFrameOptions', -10];
    return $events;
  }

}
