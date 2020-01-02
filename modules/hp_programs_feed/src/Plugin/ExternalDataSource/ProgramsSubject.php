<?php

/**
 * @file
 * Provides Drupal\external_data_source\Plugin\ExternalWsSource\ProgramsSubject.
 */

namespace Drupal\hp_programs_feed\Plugin\ExternalDataSource;

use Drupal\external_data_source\Plugin\ExternalDataSourceBase;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception as GuzzleException;

/**
 * Provides a 'Programs Subject' ExternalDataSource.
 *
 * @ExternalDataSource(
 *   id = "programs_subject",
 *   name = @Translation("Programs Subject"),
 *   description = @Translation("This Plugin will gather a list of Howard Programs Subjects.")
 * )
 */
class ProgramsSubject extends ExternalDataSourceBase {

  /**
   *
   * @return string
   */
  public function getPluginId() {
    return 'programs_subject';
  }

  /**
   *
   * @return string
   */
  public function getPluginDefinition() {
    return $this->t('This Plugin will gather a list of Howard Programs Subjects.');
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
    $cid = 'hp_programs_feed_external_data_source_programs_subject';
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
        // Subjects is the Subject endpoint on howard programs.
        $response = $client->get('https://programs.howard.edu/api/subjects/');
        $data = json_decode($response->getBody()->getContents());
        $data = $data->data;
      }
      catch (GuzzleException $e) {
        watchdog_exception('external_data_source', $e->getMessage());
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
      // Workaround to set as a text string, as a bug prevents from setting simply a number, even as string.
      $value = 'id=' . strval($entry->id);
      $collection[] = [
        'value' => $value,
        'label' => t($entry->label),
      ];
    }
    return $collection;
  }

}
