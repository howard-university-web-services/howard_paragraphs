<?php

/**
 * @file Custom formatter for tablefield, to add tablesaw classes/etc
 */

namespace Drupal\hp_table\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Url;

/**
 * Plugin implementation of the default Tablefield formatter.
 *
 * @FieldFormatter (
 *   id = "idfive_paragraphs_tablefield",
 *   label = @Translation("Idfive Paragraphs Tablesaw"),
 *   field_types = {
 *     "tablefield"
 *   }
 * )
 */
class IdfiveParagraphsTablefieldFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode = NULL) {

    $field = $items->getFieldDefinition();
    $field_name = $field->getName();
    $field_settings = $field->getSettings();

    $entity = $items->getEntity();
    $entity_type = $entity->getEntityTypeId();
    $entity_id = $entity->id();

    $elements = [];

    foreach ($items as $delta => $table) {

      if (!empty($table->value)) {
        // Tablefield::rationalizeTable($table->value);.
        $tabledata = $table->value;

        // Run the table through input filters.
        foreach ($tabledata as $row_key => $row) {
          foreach ($row as $col_key => $cell) {
            $tabledata[$row_key][$col_key] = [
              'data' => empty($table->format) ? $cell : check_markup($cell, $table->format),
              'class' => ['row_' . $row_key, 'col_' . $col_key],
            ];
          }
        }

        // Pull the header for theming.
        $header_data = array_shift($tabledata);

        // Check for an empty header, if so we don't want to theme it.
        $noheader = TRUE;
        foreach ($header_data as $cell) {
          if (strlen($cell['data']) > 0) {
            $noheader = FALSE;
            break;
          }
        }

        $header = $noheader ? NULL : $header_data;

        $render_array = [];

        $render_array['tablefield'] = [
          '#type' => 'table',
          '#header' => $header,
          '#rows' => $tabledata,
          '#attributes' => [
            'id' => 'tablefield-' . $delta,
            'class' => [
              'table',
              'table--theme-default',
              'table--stacked'
            ],
            'data-tablesaw-mode' => [
              'stack'
            ],
          ],
        ];

        $elements[$delta] = $render_array;
      }

    }
    return $elements;
  }

}
