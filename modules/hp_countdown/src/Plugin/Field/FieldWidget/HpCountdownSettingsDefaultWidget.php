<?php

namespace Drupal\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Color;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'hp_settings_default' widget.
 *
 * @FieldWidget(
 *   id = "hp_countdown_settings_default",
 *   label = @Translation("HP Countdown default paragraph settings"),
 *   field_types = {
 *     "hp_settings"
 *   }
 * )
 */

class HpCountdownSettingsDefaultWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['#attached']['library'][] = 'howard_paragraphs/colorpicker';
    $moduleHandler = \Drupal::service('module_handler');
    if ($moduleHandler->moduleExists('field_group')) {
      $element['#attached']['library'][] = 'field_group/element.horizontal_tabs';
    }
    $element['#attached']['library'][] = 'howard_paragraphs/hp_countdown_settings';

    $element['hp_countdown_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Paragraph settings'),
      '#open' => TRUE,
    ];

    $element['hp_countdown_settings']['pass_options_to_javascript'] = [
      '#type' => 'hidden',
      '#value' => FALSE,
    ];

    $element['hp_countdown_settings']['design_options'] = [
      '#type' => 'details',
      '#title' => $this->t('Design options'),
      '#open' => FALSE,
    ];

    $element['hp_countdown_settings']['design_options']['box1'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => 'design-options__margin-box',
      ],
    ];

    $element['hp_countdown_settings']['design_options']['box1']['label'] = [
      '#type' => 'html_tag',
      '#tag' => 'label',
      '#value' => $this->t('Margin'),
    ];

    $element['hp_countdown_settings']['design_options']['box1']['margin_top'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Top'),
      '#default_value' => $items[$delta]->hp_countdown_settings['design_options']['box1']['margin_top'] ?? '',
      '#attributes' => [
        'placeholder' => '-',
      ],
      '#element_validate' => [[$this, 'validateBoxElement']],
    ];

    $element['hp_countdown_settings']['design_options']['box1']['margin_right'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Right'),
      '#default_value' => $items[$delta]->hp_countdown_settings['design_options']['box1']['margin_right'] ?? '',
      '#attributes' => [
        'placeholder' => '-',
      ],
      '#element_validate' => [[$this, 'validateBoxElement']],
    ];

    $element['hp_countdown_settings']['design_options']['box1']['margin_bottom'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Bottom'),
      '#default_value' => $items[$delta]->hp_countdown_settings['design_options']['box1']['margin_bottom'] ?? '',
      '#attributes' => [
        'placeholder' => '-',
      ],
      '#element_validate' => [[$this, 'validateBoxElement']],
    ];

    $element['hp_countdown_settings']['design_options']['box1']['margin_left'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Left'),
      '#default_value' => $items[$delta]->hp_countdown_settings['design_options']['box1']['margin_left'] ?? '',
      '#attributes' => [
        'placeholder' => '-',
      ],
      '#element_validate' => [[$this, 'validateBoxElement']],
    ];

    $element['hp_countdown_settings']['design_options']['box1']['box2'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => 'design-options__border-box',
      ],
    ];

    $element['hp_countdown_settings']['design_options']['box1']['box2']['label'] = [
      '#type' => 'html_tag',
      '#tag' => 'label',
      '#value' => $this->t('Border'),
    ];

    $element['hp_countdown_settings']['design_options']['box1']['box2']['border_top'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Top'),
      '#default_value' => $items[$delta]->hp_countdown_settings['design_options']['box1']['box2']['border_top'] ?? '',
      '#attributes' => [
        'placeholder' => '-',
      ],
      '#element_validate' => [[$this, 'validateBoxElement']],
    ];

    $element['hp_countdown_settings']['design_options']['box1']['box2']['border_right'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Right'),
      '#default_value' => $items[$delta]->hp_countdown_settings['design_options']['box1']['box2']['border_right'] ?? '',
      '#attributes' => [
        'placeholder' => '-',
      ],
      '#element_validate' => [[$this, 'validateBoxElement']],
    ];

    $element['hp_countdown_settings']['design_options']['box1']['box2']['border_bottom'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Bottom'),
      '#default_value' => $items[$delta]->hp_countdown_settings['design_options']['box1']['box2']['border_bottom'] ?? '',
      '#attributes' => [
        'placeholder' => '-',
      ],
      '#element_validate' => [[$this, 'validateBoxElement']],
    ];

    $element['hp_countdown_settings']['design_options']['box1']['box2']['border_left'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Left'),
      '#default_value' => $items[$delta]->hp_countdown_settings['design_options']['box1']['box2']['border_left'] ?? '',
      '#attributes' => [
        'placeholder' => '-',
      ],
      '#element_validate' => [[$this, 'validateBoxElement']],
    ];

    $element['hp_countdown_settings']['design_options']['box1']['box2']['box3'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => 'design-options__padding-box',
      ],
    ];

    $element['hp_countdown_settings']['design_options']['box1']['box2']['box3']['label'] = [
      '#type' => 'html_tag',
      '#tag' => 'label',
      '#value' => $this->t('Padding'),
    ];

    $element['hp_countdown_settings']['design_options']['box1']['box2']['box3']['padding_top'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Top'),
      '#default_value' => $items[$delta]->hp_countdown_settings['design_options']['box1']['box2']['box3']['padding_top'] ?? '',
      '#attributes' => [
        'placeholder' => '-',
      ],
      '#element_validate' => [[$this, 'validateBoxElement']],
    ];

    $element['hp_countdown_settings']['design_options']['box1']['box2']['box3']['padding_right'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Right'),
      '#default_value' => $items[$delta]->hp_countdown_settings['design_options']['box1']['box2']['box3']['padding_right'] ?? '',
      '#attributes' => [
        'placeholder' => '-',
      ],
      '#element_validate' => [[$this, 'validateBoxElement']],
    ];

    $element['hp_countdown_settings']['design_options']['box1']['box2']['box3']['padding_bottom'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Bottom'),
      '#default_value' => $items[$delta]->hp_countdown_settings['design_options']['box1']['box2']['box3']['padding_bottom'] ?? '',
      '#attributes' => [
        'placeholder' => '-',
      ],
      '#element_validate' => [[$this, 'validateBoxElement']],
    ];

    $element['hp_countdown_settings']['design_options']['box1']['box2']['box3']['padding_left'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Left'),
      '#default_value' => $items[$delta]->hp_countdown_settings['design_options']['box1']['box2']['box3']['padding_left'] ?? '',
      '#attributes' => [
        'placeholder' => '-',
      ],
      '#element_validate' => [[$this, 'validateBoxElement']],
    ];

    $element['hp_countdown_settings']['design_options']['box1']['box2']['box3']['box4'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => 'design-options__logo-box',
      ],
    ];

    $element['hp_countdown_settings']['design_options']['box1']['box2']['box3']['box4']['label'] = [
      '#type' => 'html_tag',
      '#tag' => 'label',
      '#value' => $this->t('CNT'),
    ];

    $element['hp_countdown_settings']['design_options']['other_settings']['border_color'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Border Color'),
      '#default_value' => $items[$delta]->hp_countdown_settings['design_options']['other_settings']['border_color'] ?? '',
      '#attributes' => [
        'placeholder' => $this->t('Select Color'),
      ],
      '#element_validate' => [[$this, 'validateColorElement']],
    ];

    $border_style_description = '<ul>';
    $border_style_description .= '<li>' . 'solid: ' . $this->t('A solid line border') . '</li>';
    $border_style_description .= '<li>' . 'dashed: ' . $this->t('A dashed line border with spaces between the dashes') . '</li>';
    $border_style_description .= '<li>' . 'dotted: ' . $this->t('A dotted line border with dots') . '</li>';
    $border_style_description .= '<li>' . 'none: ' . $this->t('No border') . '</li>';
    $border_style_description .= '<li>' . 'hidden: ' . $this->t('A hidden border. This is similar to "none," but the element\'s space is still taken up by the border') . '</li>';
    $border_style_description .= '<li>' . 'initial: ' . $this->t('Sets the border style to its default value') . '</li>';
    $border_style_description .= '<li>' . 'inherit: ' . $this->t('Inherits the border style from its parent element') . '</li>';
    $border_style_description .= '<li>' . 'double: ' . $this->t('A double line border') . '</li>';
    $border_style_description .= '<li>' . 'groove: ' . $this->t('A 3D grooved border. The border looks like it is carved into the page') . '</li>';
    $border_style_description .= '<li>' . 'ridge: ' . $this->t('A 3D ridge border. The border looks like it is coming out of the page') . '</li>';
    $border_style_description .= '<li>' . 'inset: ' . $this->t('A 3D inset border. The border looks like it is pushed into the page') . '</li>';
    $border_style_description .= '<li>' . 'outset: ' . $this->t('A 3D outset border. The border looks like it is coming out of the page') . '</li>';
    $border_style_description .= '</ul>';
    $border_style_description .= $this->t('You can read more about border styles <a href="@border_styles_link">here</a>', [
      '@border_styles_link' => 'https://developer.mozilla.org/en-US/docs/Web/CSS/border-style',
    ]);

    $element['hp_countdown_settings']['design_options']['other_settings']['border_style'] = [
      '#type' => 'select',
      '#title' => $this->t('Border Style'),
      '#options' => [
        'solid' => $this->t('Solid'),
        'dashed' => $this->t('Dashed'),
        'dotted' => $this->t('Dotted'),
        'none' => $this->t('None'),
        'hidden' => $this->t('Hidden'),
        'initial' => $this->t('Initial'),
        'inherit' => $this->t('Inherit'),
        'double' => $this->t('Double'),
        'groove' => $this->t('Groove'),
        'ridge' => $this->t('Ridge'),
        'inset' => $this->t('Inset'),
        'outset' => $this->t('Outset'),
      ],
      '#default_value' => $items[$delta]->hp_countdown_settings['design_options']['other_settings']['border_style'] ?? '',
      '#description' => $border_style_description,
    ];

    $element['hp_countdown_settings']['design_options']['other_settings']['border_radius'] = [
      '#type' => 'select',
      '#title' => $this->t('Border Radius'),
      // @codingStandardsIgnoreStart
      '#options' => [
        'none' => $this->t('None'),
        '1px' => '1px',
        '2px' => '2px',
        '3px' => '3px',
        '4px' => '4px',
        '5px' => '5px',
        '10px' => '10px',
        '15px' => '15px',
        '20px' => '20px',
        '25px' => '25px',
        '30px' => '30px',
        '35px' => '35px',
      ],
      // @codingStandardsIgnoreEnd
      '#default_value' => $items[$delta]->hp_countdown_settings['design_options']['other_settings']['border_radius'] ?? 'none',
    ];

    $element['hp_countdown_settings']['design_options']['other_settings']['background_color'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Background Color'),
      '#default_value' => $items[$delta]->hp_countdown_settings['design_options']['other_settings']['background_color'] ?? '',
      '#attributes' => [
        'placeholder' => $this->t('Select Color'),
      ],
      '#element_validate' => [[$this, 'validateColorElement']],
    ];

    $element['hp_countdown_settings']['design_options']['other_settings']['background_media'] = [
      '#type' => 'media_library',
      '#allowed_bundles' => ['image', 'remote_video'],
      '#title' => $this->t('Background Image/Video'),
      '#default_value' => $items[$delta]->hp_countdown_settings['design_options']['other_settings']['background_media'] ?? NULL,
      '#description' => $this->t('Upload your image, video or Youtube video.'),
    ];

    $element['hp_countdown_settings']['design_options']['other_settings']['background_image_style'] = [
      '#type' => 'select',
      '#title' => $this->t('Background Image Style'),
      '#options' => [
        'default' => $this->t('No repeat'),
        'parallax' => $this->t('Parallax'),
        'cover' => $this->t('Cover'),
        'contain' => $this->t('Contain'),
        'repeat' => $this->t('Repeat'),
      ],
      '#default_value' => $items[$delta]->hp_countdown_settings['design_options']['other_settings']['background_image_style'] ?? 'default',
    ];

    $element['hp_countdown_settings']['design_options']['other_settings']['edge_to_edge'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Edge to Edge'),
      '#default_value' => $items[$delta]->hp_countdown_settings['design_options']['other_settings']['edge_to_edge'] ?? 0,
      '#description' => $this->t('If checked, the styles will be applied to the entire screen, starting from the left and extending to the right.'),
    ];

    $element['hp_countdown_settings']['design_options']['other_settings']['container_width'] = [
      '#type' => 'select',
      '#title' => $this->t('Container Max Width'),
      '#options' => [
        'auto' => $this->t('Auto (100%)'),
        'xxsmall' => $this->t('xxSmall'),
        'xsmall' => $this->t('xSmall'),
        'small' => $this->t('Small'),
        'default' => $this->t('Default'),
        'large' => $this->t('Large'),
        'xlarge' => $this->t('xLarge'),
        'xxlarge' => $this->t('xxLarge'),
        // 'custom' => $this->t('Set custom max width'),
        // @todo Add Custom Width field and show it when custom value is selected.
      ],

      '#description' => $this->t('See <a href=":howard_paragraphs_configuration_form">HP Countdown configuration form</a> to set Container Width and other default values.', [
        ':howard_paragraphs_configuration_form' => Url::fromRoute('howard_paragraphs.settings')->toString()
      ]),
      '#default_value' => $items[$delta]->hp_settings['design_options']['other_settings']['container_width'] ?? 'auto',
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as &$value) {
      $value += ['hp_settings' => []];
    }
    return $values;
  }

  /**
   * Validate if the box element has only numeric values.
   */
  public function validateBoxElement($element, FormStateInterface $form_state, $form) {

    // If element is empty, skip.
    if (empty($element['#value'])) {
      return;
    }

    // Get the element value.
    $elementValue = $element['#value'];

    // If the value isn't numeric, set error on validation.
    if (!is_numeric($elementValue)) {
      $form_state->setError($element, $this->t('Please use only numbers on box values'));
    }
  }

  /**
   * Validate if is the element has a valid color.
   */
  public static function validateColorElement($element, FormStateInterface $form_state, $form) {

    // If element is empty, skip.
    if (empty($element['#value'])) {
      return;
    }

    // Get the element value.
    $elementValue = $element['#value'];

    $isValidColor = Color::validateHex($elementValue);

    // If the value isn't a valid color, set error on validation.
    if (!$isValidColor) {
      $errorMessage = (string) new TranslatableMarkup('Please insert a valid color');
      $form_state->setError($element, $errorMessage);
    }
  }

}
