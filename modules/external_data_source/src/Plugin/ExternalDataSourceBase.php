<?php

namespace Drupal\external_data_source\Plugin;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Masterminds\HTML5\Parser\UTF8Utils;
use Symfony\Component\HttpFoundation\Request;

/**
 * Base class for External Data Source plugins.
 */
abstract class ExternalDataSourceBase implements ExternalDataSourceInterface {
  use StringTranslationTrait;

  /**
   * The request from the controller.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  public $request;

  /**
   * Requested result count.
   *
   * @var int
   */
  public $count;

  /**
   * Requested result string search query.
   *
   * @var string
   */
  public $q;

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->pluginDefinition['name'];
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->pluginDefinition['description'];
  }

  /**
   * Calls the external web service to retrieve data.
   *
   * @return array
   *   Retrieved data formatted as option arrays.
   */
  abstract public function getResponse();

  /**
   * Sets the current request on the plugin instance.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   */
  public function setRequest(Request $request) {
    $this->request = $request;
  }

  /**
   * Returns the current request.
   *
   * @return \Symfony\Component\HttpFoundation\Request
   *   The current request.
   */
  public function getRequest() {
    return $this->request;
  }

  /**
   * Detect & convert special char to UTF8.
   *
   * @param array $data
   *
   * @return array
   */
  public function sanitizeArray(array $data) {
    $cleanOptions = [];
    foreach ($data as $key => $value) {
      $cleanOptions[UTF8Utils::convertToUTF8($key)] = UTF8Utils::convertToUTF8($value);
    }
    return $cleanOptions;
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
      $collection[] = [
        'value' => (string) $entry->label,
        'label' => (string) $entry->label . ' (' . (string) $entry->value . ')',
      ];
    }
    return $collection;
  }

}
