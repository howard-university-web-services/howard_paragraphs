<?php

namespace Drupal\howard_paragraphs\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Class HowardNewsService.
 */
class HowardNewsService {

  /**
   * @var GuzzleHttp\Client
   */
  protected $client;
  public $currentTime;
  public $currentDate;
  public $apiEndpoint;
  public $notificationEndpoint;
  public $articleEndpoint;
  public $announcementEndpoint;

  /**
   * Constructs a new HowardNewsService object.
   */
  public function __construct(Client $client) {
    $this->client = $client;
    $this->currentTime = date("h:i:s");
    $this->currentDate = date("Y-m-d");
    $this->apiEndpoint = 'https://dev.thedig.howard.edu';
    $this->notificationEndpoint = "/jsonapi/node/initiative?filter[start-date][condition][path]=field_alert_start_date&filter[start-date][condition][value]=" . $this->currentDate . "%20" . $this->currentTime . "&filter[start-date][condition][operator]=%3C%3D&filter[end-date][condition][path]=field_alert_end_date&filter[end-date][condition][value]=" . $this->currentDate . "%20" . $this->currentTime . "&filter[end-date][condition][operator]=%3E%3D";
    $this->articleEndpoint = "/jsonapi/node/article";
    $this->announcementEndpoint = "/jsonapi/node/announcement";
  }

  /**
   * Public method to return Howard School wide notifications.
   */
  public function getNotifications() {
    $url = $this->apiEndpoint . $this->notificationEndpoint;
    $json = $this->getData('general_notifications', $url);
    return $json;
  }

  /**
   * Public method to return Howard Articles.
   */
  public function getArticles($env_url = 'https://thedig.howard.edu', $category = NULL, $initiatives = NULL, $units = NULL, $schools_colleges = NULL, $howard_forward = NULL, $range = 3, $id = 'default') {

    // left off below
    //$url = $env_url . $this->articleEndpoint . "/api/articlesearch/%20?sort=-publication_date&fields=id,label,publication_date_formatted,image,uri,summary&range=" . $range . "&page=" . $page . "&filter[publication_date][value][]=" . $currentDate . "%2000:00:00&filter[publication_date][operator]='<='&filter[exclude_from_main_feed][value]=1&filter[exclude_from_main_feed][operator]='!='";
    $url = $env_url . $this->articleEndpoint . "?page[limit]=" . $range;
    if (isset($category)) {
      $url .= $this->formatFilters('category', 'FIELD_NAME_HERE', $category);
    }
    if (isset($initiatives)) {
      $url .= $this->formatFilters('initiatives', 'FIELD_NAME_HERE', $initiatives);
    }
    if (isset($units)) {
      $url .= $this->formatFilters('units', 'FIELD_NAME_HERE', $units);
    }
    if (isset($schools_colleges)) {
      $url .= $this->formatFilters('schools', 'FIELD_NAME_HERE', $schools_colleges);
    }
    if (isset($howard_forward)) {
      $url .= $this->formatFilters('forward', 'FIELD_NAME_HERE', $howard_forward);
    }
    dsm($url);
    $json = $this->getData($id, $url);
   //dsm($json);
    return $json;
  }

  /**
   * Public method to return Howard Announcements.
   */
  public function getAnnouncements($env_url = 'https://thedig.howard.edu', $category = NULL, $unit = NULL, $range = 3, $id = 'default') {
    $url = $env_url . $this->announcementEndpoint . "?sort[sort-published][path]=field_announcement_pub_date&sort[sort-published][direction]=DESC&page[limit]=" . $range;
    // Filter for category.
    if (isset($category)) {
      $url .= $this->formatFilters('af-category', 'field_announcement_category', $category);
    }
    // Filter for unit.
    if (isset($unit)) {
      $url .= $this->formatFilters('af-unit', 'field_announcement_unit', $unit);
    }
    $json = $this->getData($id, $url);
    return $json;
  }

  /**
   * Public method to format filter sting for jsonapi filtering.
   */
  public function formatFilters($id, $path, $value, $operator = '%3D') {
    $filters = '&filter[' . $id . '][condition][path]=' . $path . '.meta.drupal_internal__target_id';
    $filters .= '&filter[' . $id . '][condition][value]=' . $value;
    $filters .= '&filter[' . $id . '][condition][operator]=' . $operator;
    return $filters;
  }

  /**
   * Public method to get content from Howard News API.
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
        $message = 'Error connecting to Howard Dig API via URL:' . $url;
        \Drupal::logger('Howard Dig API')->error($message);
        return;
      }
      if ($result['data']) {
         \Drupal::cache()->set($cache_id, $result['data'], time() + 7200);
        return $result['data'];
      }
      else {
        return;
      }
    }

  }

}
