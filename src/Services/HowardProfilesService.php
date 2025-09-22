<?php

namespace Drupal\howard_paragraphs\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Class HowardProfilesService.
 */
class HowardProfilesService {

  /**
   * @var GuzzleHttp\Client
   */
  protected $client;

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  public $apiEndpoint;
  public $notificationEndpoint;
  public $articleEndpoint;
  public $announcementEndpoint;
  public $personEndpoint;

  /**
   * Constructs a new HowardProfilesService object.
   *
   * @param \GuzzleHttp\Client $client
   *   The HTTP client.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct(Client $client, CacheBackendInterface $cache, LoggerChannelFactoryInterface $logger_factory) {
    $this->client = $client;
    $this->cache = $cache;
    $this->loggerFactory = $logger_factory;
    $this->apiEndpoint = 'http://profiles.howard.edu';
    $this->personEndpoint = "/api/profiles";
  }

  /**
   * Public method to return Howard Profiles.
   */
  public function getProfiles($env_url = 'http://profiles.howard.edu', $sort = 'all', $department = NULL, $id = 'default', $admin_taxonomy = NULL) {

    $url = $env_url . $this->personEndpoint . '?sort=' . $sort;

    // Filter for department.
    if (isset($department)) {
      $url .= '&department=' . $department;
    }

    // Filter for administrative taxonomy.
    if (isset($admin_taxonomy)) {
      $url .= '&admin=' . $admin_taxonomy;
    }

   //  dsm($url);
    $json = $this->getData($id, $url);

    return $json;
  }

  /**
   * Public method to get content from Howard Profiles API.
   *
   * @param string $cache_id
   *   The cache ID.
   * @param string $url
   *   The URL to fetch data from.
   *
   * @return array|null
   *   The fetched data or NULL on error.
   */
  public function getData($cache_id, $url) {
    if ($cache = $this->cache->get($cache_id)) {
      return $cache->data;
    }
    else {
      try {
        $request = $this->client->get($url, [
          'verify' => TRUE,
          'timeout' => 30,
          'connect_timeout' => 10,
          'headers' => [
            'Accept' => 'application/json',
            'User-Agent' => 'Howard Paragraphs Module/1.0',
          ],
        ]);
        $result = json_decode($request->getBody()->__toString(), TRUE);
      }
      catch (RequestException $e) {
        $message = 'Error connecting to Howard Profiles API via URL:' . $url;
        $this->loggerFactory->get('Howard Profiles API')->error($message);
        return;
      }
      if ($result['data']) {
        $this->cache->set($cache_id, $result, time() + 7200);
        return $result;
      }
      else {
        return;
      }
    }
  }

}
