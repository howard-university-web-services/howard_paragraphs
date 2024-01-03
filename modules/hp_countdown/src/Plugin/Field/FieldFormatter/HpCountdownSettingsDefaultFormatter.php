<?php

namespace Drupal\hp_countdown\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'hp_countdown_settings_default' formatter.
 *
 * @FieldFormatter(
 *   id = "hp_countdown_settings_default",
 *   label = @Translation("Hp Countdown paragraph settings default"),
 *   field_types = {
 *     "hp_settings"
 *   }
 * )
 */

class HpCountdownSettingsDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#theme' => 'hp_settings_default',
        '#hp_settings' => $item->hp_settings,
      ];
    }

    return $elements;
  }

}
