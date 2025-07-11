<?php

namespace Drupal\drupalx_setup\EventSubscriber;

use Drupal\Core\EventSubscriber\HttpExceptionSubscriberBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Redirects non-admin users trying to access DrupalX Setup to /user.
 */
class DrupalxSetupRouteSubscriber implements EventSubscriberInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a DrupalxSetupRouteSubscriber object.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(AccountInterface $current_user) {
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Run before routing to catch the request early.
    $events[KernelEvents::REQUEST][] = ['onKernelRequest', 31];
    return $events;
  }

  /**
   * Redirects non-admin users away from drupalx-setup.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The event to process.
   */
  public function onKernelRequest(RequestEvent $event) {
    $request = $event->getRequest();
    $path = $request->getPathInfo();

    // Check if the path is /drupalx-setup or / (homepage).
    if ($path === '/drupalx-setup' || $path === '/') {
      // Check if the user has admin permissions.
      if (!$this->currentUser->hasPermission('administer site configuration')) {
        // Redirect anonymous users and non-admin users to /user.
        $url = Url::fromRoute('user.page');
        $response = new RedirectResponse($url->toString());
        $event->setResponse($response);
      }
    }
  }

}
