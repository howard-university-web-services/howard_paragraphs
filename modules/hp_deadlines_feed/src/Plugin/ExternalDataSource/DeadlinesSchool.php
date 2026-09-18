<?php

namespace Drupal\hp_deadlines_feed\Plugin\ExternalDataSource;

use Drupal\external_data_source\Plugin\ExternalDataSourceBase;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Provides a 'Deadlines School' ExternalDataSource.
 *
 * Deadlines.howard.edu stores field_hc_deadline_school as a plain value
 * (not a taxonomy reference on that site) populated from thedig.howard.edu's
 * "Schools and Colleges" taxonomy - same source/convention as
 * hp_news_feed's NewsSchoolsColleges plugin.
 *
 * @ExternalDataSource(
 *   id = "deadlines_school",
 *   name = @Translation("Deadlines School"),
 *   description = @Translation("This Plugin will gather a list of Howard Schools and Colleges (from thedig.howard.edu) for use as a Deadlines prefilter.")
 * )
 */
class DeadlinesSchool extends ExternalDataSourceBase {

  /**
   *
   * @return string
   */
  public function getPluginId() {
    return 'deadlines_school';
  }

  /**
   *
   * @return string
   */
  public function getPluginDefinition() {
    return $this->t('This Plugin will gather a list of Howard Schools and Colleges (from thedig.howard.edu) for use as a Deadlines prefilter.');
  }

  /**
   * SetRequest
   * Setting sent request.
   *
   * @params Symfony\Component\HttpFoundation\Request $request
   */
  public function setRequest(Request $request) {
    $this->request = $request;
  }

  /**
   * GetRequest
   * getting sent request.
   *
   * @return \Symfony\Component\HttpFoundation\Request $request
   */
  public function getRequest() {
    return $this->request;
  }

  /**
   * GetResponse
   * Call WS to retrieve data.
   *
   * @return array
   */
  public function getResponse() {
    $cid = 'hp_deadlines_feed_external_data_source_deadlines_school';
    if ($this->request && !is_null($this->request->get('q'))) {
      $this->q = $this->request->get('q');
      $cid = $cid . '_' . $this->request->get('q');
    }
    $data = [];
    if ($cache = \Drupal::cache()->get($cid)) {
      $data = $cache->data;
    }
    else {
      $client = new Client();
      try {
        // Schools and Colleges taxonomy, same source as hp_news_feed's
        // NewsSchoolsColleges plugin.
        $response = $client->get('https://thedig.howard.edu/jsonapi/taxonomy_term/schools_and_colleges', [
          'verify' => TRUE,
          'timeout' => 30,
          'connect_timeout' => 10,
          'headers' => [
            'Accept' => 'application/json',
            'User-Agent' => 'Howard Paragraphs Module/1.0',
          ],
        ]);
        $data = json_decode($response->getBody()->getContents());
        $data = $data->data;
      }
      catch (RequestException $e) {
        \Drupal::logger('external_data_source')->error('HTTP request failed for Deadlines School API: @message', ['@message' => $e->getMessage()]);
      }
      // Caching result to avoid ws over use.
      \Drupal::cache()->set($cid, $data);
    }
    return $this->formatResponse($data);
  }

  /**
   * FormatResponse.
   *
   * @param array $response
   *   Formatting data retrieved from ws to match [{"value":"","label":""},
   *   {"value":"", "label":""}] return array $collection retrieved suggestions.
   *
   * @return array $collection
   */
  public function formatResponse(array $response) {
    $collection = [];
    foreach ($response as $entry) {
      // Matches the literal "id=<tid>" format already stored on
      // field_hc_deadline_school - no stripping needed downstream, unlike
      // our Category/Audience/UUID-based plugins.
      $value = 'id=' . strval($entry->attributes->drupal_internal__tid);
      $name = $entry->attributes->name;
      $collection[] = [
        'value' => $value,
        'label' => t($name),
      ];
    }
    return $collection;
  }

}
