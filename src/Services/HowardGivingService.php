<?php

namespace Drupal\howard_paragraphs\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Class HowardGivingService.
 */
class HowardGivingService {

  /**
   * @var GuzzleHttp\Client
   */
  protected $client;
  public $currentTime;
  public $currentDate;
  public $apiEndpoint;
  public $articleEndpoint;

  /**
   * Constructs a new HowardGivingService object.
   */
  public function __construct(Client $client) {
    $this->client = $client;
    $this->currentTime = date("h:i:s");
    $this->currentDate = date("Y-m-d");
    $this->apiEndpoint = 'https://stg.giving.howard.edu';
    $this->articleEndpoint = "/jsonapi/node/gs_giving_internal";

  }

  /**
   * Public method to return Howard Giving Articles.
   */
  public function getArticles($env_url = 'https://stg.giving.howard.edu/', $category = NULL, $range = 3, $id = 'default') {

    $url = $env_url . $this->articleEndpoint ."?page[limit]=" . $range . '&include=field_hc_header_image,field_hc_header_image.field_media_image,field_hc_header_image.field_media_image.uid,field_hc_resource_category';

    if (isset($category)) {
      $url .= $this->formatFilters('category', 'field_hc_resource_category', $category);
    }

    $json = $this->getData($id, $url);
    $json = $this->formatContentWithImages($json, 'large', 'field_hc_header_image');
    $articles_with_category = [];

    if (isset($json['data']) && is_array($json['data'])) {
      foreach ($json['data'] as $article) {
        $category = $article['relationships']['field_hc_resource_category']['data'][0]['meta']['drupal_internal__target_id'];
        $category_name = $this->getCategory($category);

        $article['category_name'] = $category_name;
        $articles_with_category[] = $article;
      }
    }

    $result = [
      'data' => $articles_with_category,
      'original_data' => $json['data'],
    ];
    return $result;
  }


  public function getCategory( $category = NULL){
    $env_url = 'https://stg.giving.howard.edu/';
    $url = $env_url . 'jsonapi/taxonomy_term/hc_resource_category?filter[drupal_internal__tid]='.$category;

    try {
        $request = $this->client->get($url, ['verify' => FALSE]);
        $result = json_decode($request->getBody()->__toString(), TRUE);
      }
      catch (RequestException $e) {
        $message = 'Error connecting to Howard Dig API via URL:' . $url;
        \Drupal::logger('Howard Giving API')->error($message);
        return;
      }

    return $result['data'][0]["attributes"]['name'];
  }

  /**
   * Public method to get content from Howard Giving API.
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

        $message = 'Error connecting to Howard Giving API via URL:' . $url;
        \Drupal::logger('Howard Giving API')->error($message);

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
   * Public method to format content to get an image.
   */
  public function formatContentWithImages($json, $style = 'large', $field = 'field_hc_header_image') {
    if (isset($json)) {

      foreach ($json['data'] as $key => $article) {
        $image = [];
        if (isset($article['relationships'][$field]['data']['id'])) {

         // echo"<pre>";
          //var_dump($json['data'][$key]['relationships']['field_hc_header_image']['links']);
          //var_dump($article);
          //echo "</pre>";
         // die();
          if (isset($json['included'][$key]['relationships'])) {

            $image['alt'] = $json['included'][$key]['relationships']['field_media_image']['data']['meta']['alt'];
            $image_key = array_search($json['included'][$key]['relationships']['field_media_image']['data']['id'], array_column($json['included'], 'id'));
            //$image_style = array_column($json['included'][$image_key]['attributes']['image_style_uri'], $style);
            $image['uri'] = $json['included'][$image_key]['attributes']['image_style_uri']['large'];
            //echo "<pre>";
            //var_dump($json['included'][$image_key]['attributes']['image_style_uri']);
            //var_dump($image);
            //die();
          }
        }

        $json['data'][$key]['attributes']['image'] = $image;
      }
    }
    return $json;
  }

}
