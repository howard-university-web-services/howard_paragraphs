<?php

namespace Drupal\hp_events_feed\Service;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\hp_events_feed\EventNormalizer;
use Drupal\hp_events_feed\EventsQuery;
use GuzzleHttp\ClientInterface;

/**
 * Cached server-side access to Howard's Localist calendar.
 */
class LocalistClient {

  public const BASE_URL = 'https://events.howard.edu/api/2/';
  public const CACHE_TAG = 'hp_events_feed:events';
  private const SETTINGS_TAG = 'config:hp_events_feed.settings';

  public function __construct(
    private ClientInterface $httpClient,
    private CacheBackendInterface $cache,
    private ConfigFactoryInterface $configFactory,
    private LoggerChannelFactoryInterface $loggerFactory,
    private TimeInterface $time,
  ) {}

  /**
   * Fetch a feed page; failures are distinguishable from legitimate empty feeds.
   */
  public function getFeed(array $filters, int $count = 6, int $page = 1, array $tags = []): array {
    try {
      $query = EventsQuery::build($filters, $count, $page);
    }
    catch (\InvalidArgumentException $exception) {
      return ['events' => [], 'error' => TRUE, 'page' => [], 'max_age' => 60];
    }
    $cid = $this->cacheId('events', [$query, $tags]);
    if ($cached = $this->cache->get($cid)) {
      $result = $cached->data;
      $result['max_age'] = max(0, $cached->expire - $this->time->getCurrentTime());
      return $result;
    }
    $result = ['events' => [], 'error' => FALSE, 'page' => [], 'max_age' => 300];
    try {
      $data = $this->request('events', $query);
      if (!isset($data['events'], $data['page']) || !is_array($data['events']) || !is_array($data['page'])) {
        throw new \UnexpectedValueException('Malformed events response.');
      }
      foreach ($data['events'] as $wrapper) {
        if (is_array($wrapper) && isset($wrapper['event']) && is_array($wrapper['event'])) {
          $event = EventNormalizer::normalize($wrapper['event'], $query);
          if ($event !== NULL) {
            $result['events'][] = $event;
          }
        }
      }
      $result['page'] = [
        'current' => $query['page'],
        'total' => max(1, (int) ($data['page']['total'] ?? 1)),
      ];
    }
    catch (\Exception $exception) {
      $result['error'] = TRUE;
      $result['max_age'] = 60;
      $this->logFailure('events');
    }
    $this->cache->set($cid, $result, $this->time->getCurrentTime() + $result['max_age'], array_merge($tags, [self::CACHE_TAG, self::SETTINGS_TAG]));
    return $result;
  }

  /**
   * Get all filter items, places, groups, departments, and communities.
   *
   * Follow catalogue pagination rather than silently omitting items after 100.
   * Partial catalogues are marked unavailable and retried after one minute.
   */
  public function getCatalogue(): array {
    $cid = $this->cacheId('catalogue', []);
    if ($cached = $this->cache->get($cid)) {
      return $cached->data;
    }
    $catalogue = ['types' => [], 'venues' => [], 'groups' => [], 'communities' => [], 'unavailable' => []];
    try {
      $filters = $this->request('events/filters');
      $labels = $this->request('events/labels');
      foreach ($filters as $context => $items) {
        if (!is_array($items)) {
          continue;
        }
        $label = EventNormalizer::text($labels['filters'][$context] ?? ucwords(str_replace('_', ' ', $context)));
        $names = [];
        foreach ($items as $item) {
          if (is_array($item) && isset($item['id'], $item['name'])) {
            $names[(string) $item['id']] = EventNormalizer::text($item['name']);
          }
        }
        $options = [];
        foreach ($items as $item) {
          if (is_array($item) && isset($names[(string) ($item['id'] ?? '')])) {
            $name = $names[(string) $item['id']];
            if (!empty($item['parent_id']) && isset($names[(string) $item['parent_id']])) {
              $name = $names[(string) $item['parent_id']] . ' / ' . $name;
            }
            $options[(string) $item['id']] = $name;
          }
        }
        natcasesort($options);
        $catalogue['types'][$label] = $options;
      }
    }
    catch (\Exception $exception) {
      $catalogue['unavailable'][] = 'event filters';
      $this->logFailure('event filters');
    }
    foreach (['places' => 'venues', 'groups' => 'groups', 'departments' => 'groups'] as $resource => $target) {
      try {
        $options = $this->listOptions($resource, $resource, rtrim($resource, 's'));
        if ($target === 'groups') {
          $catalogue[$target][ucfirst($resource)] = $options;
        }
        else {
          $catalogue[$target] = $options;
        }
      }
      catch (\Exception $exception) {
        $catalogue['unavailable'][] = $resource;
        $this->logFailure($resource);
      }
    }
    try {
      $organizations = $this->listOptions('organizations', 'organizations', 'organization');
      foreach ($organizations as $id => $name) {
        $options = $this->listOptions('organizations/' . $id . '/communities', 'communities', 'community');
        $catalogue['communities'] += $options;
      }
      natcasesort($catalogue['communities']);
    }
    catch (\Exception $exception) {
      $catalogue['unavailable'][] = 'communities';
      $this->logFailure('communities');
    }
    $ttl = $catalogue['unavailable'] ? 60 : 3600;
    $this->cache->set($cid, $catalogue, $this->time->getCurrentTime() + $ttl, [self::SETTINGS_TAG, 'hp_events_feed:catalogue']);
    return $catalogue;
  }

  /**
   * Fetch every page of a lookup resource, with a finite safety limit.
   */
  private function listOptions(string $path, string $collection, string $singular): array {
    $options = [];
    $total = 1;
    for ($page = 1; $page <= $total; $page++) {
      if ($page > 100) {
        throw new \UnexpectedValueException('Catalogue exceeded 100 pages.');
      }
      $data = $this->request($path, ['pp' => 100, 'page' => $page]);
      if (!isset($data[$collection]) || !is_array($data[$collection])) {
        throw new \UnexpectedValueException('Malformed catalogue response.');
      }
      foreach ($data[$collection] as $wrapper) {
        $item = $wrapper[$singular] ?? [];
        if (is_array($item) && isset($item['id'], $item['name']) && preg_match('/^[1-9][0-9]*$/D', (string) $item['id'])) {
          $options[(string) $item['id']] = EventNormalizer::text($item['name']);
        }
      }
      $total = max(1, (int) ($data['page']['total'] ?? 1));
    }
    natcasesort($options);
    return $options;
  }

  /**
   * Make a verified HTTPS request to a fixed calendar origin.
   */
  private function request(string $path, array $query = []): array {
    $options = [
      'headers' => ['Accept' => 'application/json'],
      'connect_timeout' => 3,
      'timeout' => 10,
      'allow_redirects' => FALSE,
    ];
    $token = (string) $this->configFactory->get('hp_events_feed.settings')->get('access_token');
    if ($token !== '') {
      $options['headers']['Authorization'] = 'Bearer ' . $token;
    }
    $url = self::BASE_URL . $path;
    if ($query) {
      $url .= '?' . EventsQuery::encode($query);
    }
    $response = $this->httpClient->request('GET', $url, $options);
    if ($response->getStatusCode() !== 200) {
      throw new \UnexpectedValueException('Unexpected API status.');
    }
    $data = json_decode((string) $response->getBody(), TRUE, 512, JSON_THROW_ON_ERROR);
    if (!is_array($data)) {
      throw new \UnexpectedValueException('Expected JSON object.');
    }
    return $data;
  }

  /**
   * Separate caches by token without putting the token in a cache identifier.
   */
  private function cacheId(string $resource, array $query): string {
    $token = (string) $this->configFactory->get('hp_events_feed.settings')->get('access_token');
    return 'hp_events_feed:' . $resource . ':' . hash('sha256', json_encode([$query, $token], JSON_THROW_ON_ERROR));
  }

  /**
   * Keep request URLs, credentials, and upstream response bodies out of logs.
   */
  private function logFailure(string $resource): void {
    $this->loggerFactory->get('hp_events_feed')->warning('Could not load Localist @resource from events.howard.edu.', ['@resource' => $resource]);
  }

}
