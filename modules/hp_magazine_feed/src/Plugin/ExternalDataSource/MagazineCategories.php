<?php

namespace Drupal\hp_magazine_feed\Plugin\ExternalDataSource;

use Drupal\external_data_source\Plugin\ExternalDataSourceBase;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Provides a 'Magazine Categories' ExternalDataSource.
 *
 * @ExternalDataSource(
 *   id = "magazine_categories",
 *   name = @Translation("Magazine Categories"),
 *   description = @Translation("This Plugin will gather a list of Howard Magazine Categories.")
 * )
 */
class MagazineCategories extends ExternalDataSourceBase {

  /**
   *
   * @return string
   */
  public function getPluginId() {
    return 'magazine_categories';
  }

  /**
   *
   * @return string
   */
  public function getPluginDefinition() {
    return $this->t('This Plugin will gather a list of Howard Magazine Categories.');
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
    $cid = 'hp_magazine_feed_external_data_source_magazine_categories';
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
        // Categories is the endpoint on magazine.howard.
        $response = $client->get('https://magazine.howard.edu/jsonapi/taxonomy_term/categories', [
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
        \Drupal::logger('external_data_source')->error('HTTP request failed for Magazine Categories API: @message', ['@message' => $e->getMessage()]);
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
      $value = 'id=' . strval($entry->attributes->drupal_internal__tid);
      $name = $entry->attributes->name;
      $collection[] = [
        'value' => $value,
        'label' => t($name),
      ];
    }
    // \Drupal::logger('hp_ec')->debug('response: ' . json_encode($collection) );
    return $collection;
  }

}
