<?php

namespace Drupal\my_module;

use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ResponseSubscriber.
 *
 * Subscribe drupal events.
 *
 * @package Drupal\my_module
 */
class ResponseSubscriber implements EventSubscriberInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a new ResponseSubscriber instance.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.   */
  public function __construct(AccountInterface $current_user) {
    $this->currentUser = $current_user;
  }


  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE] = 'alterResponse';
    return $events;
  }
  /**
   * Redirect if 403 and node an event.
   *
   * @param FilterResponseEvent $event
   *   The route building event.
   */
  public function alterResponse(FilterResponseEvent $event) {
    if ($event->getResponse()->getStatusCode() == 403) {
      /** @var \Symfony\Component\HttpFoundation\Request $request */
      $request = $event->getRequest();
      $node = $request->attributes->get('node');
      if ($node instanceof Node && $node->getType() == 'show' && !$node->isPublished() && $this->currentUser->isAuthenticated()) {
        drupal_set_message('This show has now expired.', 'warning');
        $event->setResponse(new RedirectResponse('/all-shows', 301));
      }
    }
  }
}
