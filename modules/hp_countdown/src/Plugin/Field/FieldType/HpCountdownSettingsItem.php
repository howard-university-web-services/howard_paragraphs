<?php

namespace Drupal\hp_countdown\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\MapDataDefinition;

/**
 * Plugin implementation of the 'settings' field type.
 *
 * @FieldType(
 *   id = "hp_settings",
 *   label = @Translation("HP Countdown Settings"),
 *   description = @Translation("Stores HP Countdown settings in the database"),
 *   default_widget = "hp_countdown_settings_default",
 *   default_formatter = "hp_countdown_settings_default"
 * )
 */

class HpCountdownSettingsItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field) {
    return [
      'columns' => [
        'hp_settings' => [
          'description' => 'Serialized paragraph settings data',
          'type' => 'blob',
          'serialize' => TRUE,
          'size' => 'big',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('hp_settings')->getValue();
    return $value === NULL || $value === '';
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['hp_settings'] = MapDataDefinition::create()
      ->setLabel(t('HP Countdown settings'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function mainPropertyName() {
    // A map item has no main property.
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($values, $notify = TRUE) {
    if (isset($values)) {
      $values += [
        'hp_settings' => [],
      ];
    }

    parent::setValue($values, $notify);
  }

}
