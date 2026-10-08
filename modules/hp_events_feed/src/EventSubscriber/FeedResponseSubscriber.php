<?php

namespace Drupal\hp_events_feed\EventSubscriber;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\hp_events_feed\Service\LocalistClient;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Bounds anonymous page-cache and proxy lifetimes for pages containing feeds.
 *
 * Drupal's Internal Page Cache ignores render max-age and uses Expires instead.
 */
final class FeedResponseSubscriber implements EventSubscriberInterface {

  public function __construct(private TimeInterface $time) {}

  public static function getSubscribedEvents(): array {
    // Run after core has established public/private response headers.
    return [KernelEvents::RESPONSE => ['onResponse', -10]];
  }

  public function onResponse(ResponseEvent $event): void {
    $response = $event->getResponse();
    $deadlines = $response->headers->all('X-Howard-Events-Expires');
    $response->headers->remove('X-Howard-Events-Expires');
    if (!$event->isMainRequest() || !$response instanceof CacheableResponseInterface || $response->getStatusCode() !== 200) {
      return;
    }
    $metadata = $response->getCacheableMetadata();
    if (!in_array(LocalistClient::CACHE_TAG, $metadata->getCacheTags(), TRUE)) {
      return;
    }
    // Preserve stricter caching policies from access control or other modules.
    foreach (['private', 'no-cache', 'no-store'] as $directive) {
      if ($response->headers->hasCacheControlDirective($directive)) {
        return;
      }
    }
    $age = $metadata->getCacheMaxAge();
    if ($age === Cache::PERMANENT) {
      $age = 300;
    }
    $age = min(300, max(0, $age));
    // Render and Dynamic Page Cache hits retain their original feed deadline.
    // Reusing a cached render must not restart its five-minute lifetime.
    foreach ($deadlines as $header) {
      foreach (explode(',', $header) as $deadline) {
        if (ctype_digit(trim($deadline))) {
          $age = min($age, max(0, (int) trim($deadline) - $this->time->getCurrentTime()));
        }
      }
    }
    if ($age === 0) {
      $response->headers->addCacheControlDirective('no-cache');
      return;
    }
    $expires = $this->time->getCurrentTime() + $age;
    $existing = $response->getExpires();
    if ($existing && $existing->getTimestamp() > $this->time->getCurrentTime()) {
      $expires = min($expires, $existing->getTimestamp());
    }
    $response->setExpires(new \DateTimeImmutable('@' . $expires));
    $max_age = $response->headers->getCacheControlDirective('max-age');
    $response->setMaxAge($max_age === NULL ? $age : min($age, (int) $max_age));
    if ($response->headers->hasCacheControlDirective('s-maxage')) {
      $response->setSharedMaxAge(min($age, (int) $response->headers->getCacheControlDirective('s-maxage')));
    }
  }

}
