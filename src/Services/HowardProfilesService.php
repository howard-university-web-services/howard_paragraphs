<?php

namespace Drupal\howard_paragraphs\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Class HowardProfilesService.
 */
class HowardProfilesService {

  /**
   * @var GuzzleHttp\Client
   */
  protected $client;
  public $apiEndpoint;
  public $notificationEndpoint;
  public $articleEndpoint;
  public $announcementEndpoint;
  public $personEndpoint;

  /**
   * Constructs a new HowardProfilesService object.
   */
  public function __construct(Client $client) {
    $this->client = $client;
    $this->apiEndpoint = 'http://profiles.howard.edu';
    $this->personEndpoint = "/api/profiles";
  }

  /**
   * Public method to return Howard Profiles.
   */
  public function getProfiles($env_url = 'http://profiles.howard.edu', $sort = 'all', $department = NULL, $id = 'default') {

    $url = $env_url . $this->personEndpoint . '?sort=' . $sort;

    // Filter for department.
    if (isset($department)) {
        $url .= '&department=' . $department;
    }

    //dsm($url);
    $json = $this->getData($id, $url);

    return $json;
  }

  /**
   * Public method to get content from Howard Profiles API.
   */
  public function getData($cache_id, $url) {
    if ($cache = \Drupal::cache()->get($cache_id)) {
      return $cache->data;
    } else {
      try {
        $request = $this->client->get($url, ['verify' => FALSE]);
        $result = json_decode($request->getBody()->__toString(), TRUE);
      }
      catch (RequestException $e) {
        $message = 'Error connecting to Howard Profiles API via URL:' . $url;
        \Drupal::logger('Howard Profiles API')->error($message);
        return;
      }
      if ($result['data']) {
        \Drupal::cache()->set($cache_id, $result, time() + 7200);
        return $result;
      }
      else {
        return;
      }
    }

  }

}
