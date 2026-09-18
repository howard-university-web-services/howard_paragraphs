<?php

namespace Drupal\hp_deadlines_feed\Plugin\ExternalDataSource;

use Drupal\external_data_source\Plugin\ExternalDataSourceBase;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Provides a 'Deadlines Audience' ExternalDataSource.
 *
 * @ExternalDataSource(
 *   id = "deadlines_audience",
 *   name = @Translation("Deadlines Audience"),
 *   description = @Translation("This Plugin will gather a list of Howard Deadline Audiences.")
 * )
 */
class DeadlinesAudience extends ExternalDataSourceBase {

  /**
   *
   * @return string
   */
  public function getPluginId() {
    return 'deadlines_audience';
  }

  /**
   *
   * @return string
   */
  public function getPluginDefinition() {
    return $this->t('This Plugin will gather a list of Howard Deadline Audiences.');
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
    $cid = 'hp_deadlines_feed_external_data_source_deadlines_audience';
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
        // TODO: switch to https://deadlines.howard.edu once prod exists;
        // there is no prod Deadlines site yet, so we use stg for now.
        // hc_deadline_audience is the Deadline Audience taxonomy on Howard Dates & Deadlines.
        $response = $client->get('https://stg.deadlines.howard.edu/jsonapi/taxonomy_term/hc_deadline_audience', [
          // Stg requires basic auth, and its cert is currently expired -
          // both TODOs to remove once we're pointed at prod.
          'auth' => ['huweb', 'huweb'],
          'verify' => FALSE,
          'timeout' => 30,
          'connect_timeout' => 10,
          'headers' => [
            'Accept' => 'application/json',
            'User-Agent' => 'Howard Paragraphs Module/1.0',
          ],
        ]);
        $decoded = json_decode($response->getBody()->getContents());
        // Guard against a non-JSON or unexpected-shape response (e.g. a
        // parked/placeholder page) so this never fatals the caller.
        if (is_object($decoded) && isset($decoded->data) && is_array($decoded->data)) {
          $data = $decoded->data;
        }
        else {
          \Drupal::logger('external_data_source')->error('Unexpected response shape from Deadlines Audience API.');
        }
      }
      catch (\Throwable $e) {
        \Drupal::logger('external_data_source')->error('HTTP request failed for Deadlines Audience API: @message', ['@message' => $e->getMessage()]);
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
      // Unlike other hp_*_feed plugins, we use the JSON:API UUID directly
      // (not the numeric drupal_internal__tid) since the frontend widget
      // filters by term UUID.
      $collection[] = [
        'value' => $entry->id,
        'label' => t($entry->attributes->name),
      ];
    }
    return $collection;
  }

}
