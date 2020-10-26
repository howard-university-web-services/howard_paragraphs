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
  public $alertsEndpoint;

  /**
   * Constructs a new AmericanClientService object.
   */
  public function __construct(Client $client) {
    $this->client = $client;
    $this->currentTime = date("h:i:s");
    $this->currentDate = date("Y-m-d");
    $this->apiEndpoint = 'http://newsroom.howard.edu';
    $this->alertsEndpoint = $this->apiEndpoint . "/api/alerts?filter[start_date][value]=" . $this->currentDate . "%20" . $this->currentTime . "&filter[start_date][operator]='<='&filter[end_date][value]=" . $this->currentDate . "%20" . $this->currentTime . "&filter[end_date][operator]='>='";
  }

  /**
   * Public method to return Howard Announcements.
   */
  public function getAnnouncements() {
    $url = $this->alertsEndpoint . '&filter[alert_type]=announcement';
    $json = $this->getData($url);
    return $json;
  }

  /**
   * Public method to get content from Howard News API.
   */
  public function getData($url) {
    try {
      $request = $this->client->get($url);
      $result = json_decode($request->getBody()->__toString(), TRUE);
    }
    catch (RequestException $e) {
      $message = 'Error connecting to Howard News API via URL:' . $url;
      \Drupal::logger('Howard News API')->error($message);
      return;
    }
    if ($result['data']) {
      return $result['data'];
    }
    else {
      return;
    }
  }

}
