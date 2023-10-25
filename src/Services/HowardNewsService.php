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
  public $magazineArticleEndpoint;
  public $announcementEndpoint;
  public $personEndpoint;

  /**
   * Constructs a new HowardNewsService object.
   */
  public function __construct(Client $client) {
    $this->client = $client;
    $this->currentTime = date("h:i:s");
    $this->currentDate = date("Y-m-d");
    $this->apiEndpoint = 'https://thedig.howard.edu';
    $this->notificationEndpoint = "/jsonapi/node/initiative?filter[start-date][condition][path]=field_alert_start_date&filter[start-date][condition][value]=" . $this->currentDate . "%20" . $this->currentTime . "&filter[start-date][condition][operator]=%3C%3D&filter[end-date][condition][path]=field_alert_end_date&filter[end-date][condition][value]=" . $this->currentDate . "%20" . $this->currentTime . "&filter[end-date][condition][operator]=%3E%3D";
    $this->articleEndpoint = "/jsonapi/node/article";
    $this->magazineArticleEndpoint = "/jsonapi/node/article";
    $this->announcementEndpoint = "/jsonapi/node/announcement";
    $this->personEndpoint = "/jsonapi/node/person";
  }

  /**
   * Public method to return Howard School wide notifications.
   */
  public function getNotifications() {
    $url = $this->apiEndpoint . $this->notificationEndpoint;
    $json = $this->getData('general_notifications', $url);
    if (isset($json['data'])) {
      return $json['data'];
    }
  }

  /**
   * Public method to return Howard Dig Articles.
   */
  public function getArticles($env_url = 'https://thedig.howard.edu', $category = NULL, $initiatives = NULL, $units = NULL, $schools_colleges = NULL, $howard_forward = NULL, $range = 3, $id = 'default') {

    $url = $env_url . $this->articleEndpoint . "?sort[sort-published][path]=field_date&sort[sort-published][direction]=DESC&page[limit]=" . $range . '&include=field_hero_image,field_hero_image.field_media_image,field_hero_image.field_media_image.uid';
    // Filter for category.
    if (isset($category)) {
      $url .= $this->formatFilters('category', 'field_primary_tag', $category);
    }
    // Filter for initiatives.
    if (isset($initiatives)) {
      $url .= $this->formatFilters('initiatives', 'field_event_initiative_campaign', $initiatives);
    }
    // Filter for units.
    if (isset($units)) {
      $url .= $this->formatFilters('units', 'field_campus_units_programs', $units);
    }
    // Filter for schools.
    if (isset($schools_colleges)) {
      $url .= $this->formatFilters('schools', 'field_schools_and_colleges', $schools_colleges);
    }
    // Filter for howard forward.
    if (isset($howard_forward)) {
      $url .= $this->formatFilters('forward', 'field_howard_forward', $howard_forward);
    }
    $json = $this->getData($id, $url);
    $json = $this->formatContentWithImages($json, 'large', 'field_hero_image');

    return $json;
  }

  /**
   * Public method to return single Howard Dig Article for the howard.edu homepage.
   */
  public function getHomeFeaturedArticles($env_url = 'https://thedig.howard.edu', $range = 1, $id = 'default') {

    $url = $env_url . $this->articleEndpoint . "?filter[field_howard_homepage_feature]=1&sort[sort-published][path]=field_date&sort[sort-published][direction]=DESC&page[limit]=" . $range . '&include=field_hero_image,field_hero_image.field_media_image,field_hero_image.field_media_image.uid';
    $json = $this->getData($id, $url);
    $json = $this->formatContentWithImages($json, 'dig_1920_x_1080', 'field_hero_image');

    return $json;
  }

  /**
   * Public method to return Howard Magazine Articles.
   */
  public function getMagazineArticles($env_url = 'https://magazine.howard.edu', $category = NULL, $range = 3, $id = 'default') {

    $url = $env_url . $this->magazineArticleEndpoint . "?sort[sort-published][path]=field_date&sort[sort-published][direction]=DESC&page[limit]=" . $range . '&include=field_hero_image,field_hero_image.field_media_image,field_hero_image.field_media_image.uid';
    // Filter for category.
    if (isset($category)) {
      $url .= $this->formatFilters('category', 'field_categories', $category);
    }
    $json = $this->getData($id, $url);
    $json = $this->formatContentWithImages($json, 'large', 'field_hero_image');
    return $json;
  }

  /**
   * Public method to return Howard Featured People.
   */
  public function getPeople($env_url = 'https://thedig.howard.edu', $ids = NULL, $category = NULL, $feed_type = NULL, $range = 3, $id = 'default') {

    $url = $env_url . $this->personEndpoint . "?sort[sort-name][path]=field_person_last_name&sort[sort-name][direction]=ASC&page[limit]=" . $range . '&include=field_person_profile_image,field_person_profile_image.field_media_image,field_person_profile_image.field_media_image.uid';
    // If we are specifying ID's, just get those, otherwise, look for filters.
    if (isset($ids)) {
      $url .= $this->formatIdFilters('node_ids', 'nid', $ids, 'IN');
    } else {
      // Filter for category.
      if (isset($category)) {
        $url .= $this->formatFilters('category', 'field_person_categories', $category);
      }
      // Filter for type.
      if (isset($feed_type)) {
        if ($feed_type == 'iconic_alumni') {
          $url .= $this->formatListFilters('type', 'field_person_classification', 'iconic_alumni', '%3D');
        }
        elseif ($feed_type == 'students_only') {
          $url .= $this->formatListFilters('type', 'field_person_classification', 'student', '%3D');
        }
        elseif ($feed_type == 'alumni_only') {
          $url .= $this->formatListFilters('type', 'field_person_classification', 'alumni', '%3D');
        }
        elseif ($feed_type == 'faculty_only') {
          $url .= $this->formatListFilters('type', 'field_person_classification', 'faculty', '%3D');
        }
        elseif ($feed_type == 'stem_scholars') {
          $url .= $this->formatListFilters('type', 'field_person_classification', 'stem_scholars', '%3D');
        }
        else {
          $url .= $this->formatListFilters('type', 'field_person_classification', 'iconic_alumni', 'NOT IN');
        }
      }

    }

    $json = $this->getData($id, $url);
    $json = $this->formatContentWithImages($json, 'dig_500_x_500', 'field_person_profile_image');

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
    return $json['data'];
  }

  /**
   * Public method to format taxonomy filter sting for jsonapi filtering.
   */
  public function formatFilters($id, $path, $value, $operator = '%3D') {
    $filters = '&filter[' . $id . '][condition][path]=' . $path . '.meta.drupal_internal__target_id';
    $filters .= '&filter[' . $id . '][condition][value]=' . $value;
    $filters .= '&filter[' . $id . '][condition][operator]=' . $operator;
    return $filters;
  }

  /**
   * Public method to format list (text) filter sting for jsonapi filtering.
   */
  public function formatListFilters($id, $path, $value, $operator = '%3D') {
    $filters = '&filter[' . $id . '][condition][path]=' . $path;
    $filters .= '&filter[' . $id . '][condition][value]=' . $value;
    $filters .= '&filter[' . $id . '][condition][operator]=' . $operator;
    return $filters;
  }

  /**
   * Public method to format multiple ID's to a filter sting for jsonapi filtering.
   */
  public function formatIdFilters($id, $path, $values, $operator = 'IN') {
    $filters = '&filter[' . $id . '][condition][path]=' . $path;
    $filters .= '&filter[' . $id . '][condition][operator]=' . $operator;
    foreach ($values as $key => $value) {
      $filters .= '&filter[' . $id . '][condition][value][' . $key . ']=' . $value['value'];
    }
    return $filters;
  }

  /**
   * Public method to format content to get an image.
   */
  public function formatContentWithImages($json, $style = 'large', $field = 'field_hero_image') {
    if (isset($json)) {
      foreach ($json['data'] as $key => $article) {
        $image = [];
        if (isset($article['relationships'][$field]['data']['id'])) {
          $media_key = array_search($article['relationships'][$field]['data']['id'], array_column($json['included'], 'id'));
          if (isset($json['included'][$key]['relationships']['field_media_image'])) {
            $image['alt'] = $json['included'][$key]['relationships']['field_media_image']['data']['meta']['alt'];
            $image_key = array_search($json['included'][$key]['relationships']['field_media_image']['data']['id'], array_column($json['included'], 'id'));
            $image_style = array_column($json['included'][$image_key]['attributes']['image_style_uri'], $style);
            if (!empty($image_style[0])) {
              $image['uri'] = $image_style[0];
            } else {
              // secondary condition to address issue with d10 upgrade
              if (!empty($json['included'][$image_key]['attributes']['image_style_uri'][$style])) {
                $image['uri'] = $json['included'][$image_key]['attributes']['image_style_uri'][$style];
              }
            }
          }
        }

        $json['data'][$key]['attributes']['image'] = $image;
      }
    }
    return $json;
  }

  /**
   * Public method to get content from Howard News API.
   */
  public function getData($cache_id, $url) {
    if ($cache = \Drupal::cache()->get($cache_id)) {
      return $cache->data;
    } else {
      try {
        $request = $this->client->get($url . '&filter[status][value]=1', ['verify' => FALSE]);
        $result = json_decode($request->getBody()->__toString(), TRUE);
      }
      catch (RequestException $e) {
        $message = 'Error connecting to Howard Dig API via URL:' . $url;
        \Drupal::logger('Howard Dig API')->error($message);
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
