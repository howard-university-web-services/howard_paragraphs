<?php

namespace Drupal\hp_wysiwyg_text_filter_ul\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * @Filter(
 *  id = "hp_wysiwyg_text_filter_ul",
 *  title = @Translation("UL Filter"),
 *  description = @Translation("Add a class to UL's to apply typographic styles."),
 *  type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE,
 * )
 */
class FilterUL extends FilterBase {
  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    // Create DOM document.
    $dom = Html::load($text);

    // Get DOM nodes from XPath query.
    $xpath = new \DOMXPath($dom);
    $ul_nodes = $xpath->query('//ul');

    // If any UL's were found, append the body-text--list class to each of their class attributes
    if ($ul_nodes->length > 0) {
      foreach($ul_nodes as $ul) {
        $class_list = explode(' ', $ul->getAttribute('class'));
        $class_list[] = 'body-text--list';
        $ul->setAttribute('class', join(' ', array_unique($class_list)));
      }
    }

    return new FilterProcessResult(Html::serialize($dom));
  }
}