<?php

namespace Drupal\hp_events_feed\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\OptGroup;
use Drupal\hp_events_feed\EventsQuery;
use Drupal\hp_events_feed\Service\LocalistClient;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A structured editor for the JSON-backed Localist filters field.
 *
 * @FieldWidget(
 *   id = "hp_events_filters",
 *   label = @Translation("Howard Localist event filters"),
 *   field_types = {"string_long"}
 * )
 */
class EventsFiltersWidget extends WidgetBase {

  private LocalistClient $localist;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->localist = $container->get('hp_events_feed.localist');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    try {
      $values = EventsQuery::decode($items[$delta]->value);
    }
    catch (\Exception $exception) {
      // Keep malformed imported settings visible so a builder can repair them.
      $element['invalid'] = ['#markup' => $this->t('The saved event filters are invalid. Review the controls below and save to repair them.')];
      $values = EventsQuery::defaults();
    }
    $catalogue = $this->localist->getCatalogue();
    $element['#element_validate'][] = [static::class, 'validateFilters'];
    $element['filters'] = ['#type' => 'container', '#tree' => TRUE];
    if ($catalogue['unavailable']) {
      $element['filters']['notice'] = [
        '#markup' => $this->t('Some calendar options are temporarily unavailable: @resources. Saved selections are preserved; try again shortly.', ['@resources' => implode(', ', $catalogue['unavailable'])]),
      ];
    }
    $groups = [
      'dates' => ['Date range', TRUE],
      'taxonomy' => ['Categories and keywords', FALSE],
      'location' => ['Places, groups, and departments', FALSE],
      'refinements' => ['Event options and ordering', FALSE],
    ];
    foreach ($groups as $key => [$label, $open]) {
      $element['filters'][$key] = ['#type' => 'details', '#title' => $this->t($label), '#open' => $open, '#tree' => TRUE];
    }
    $controls = &$element['filters'];
    $controls['dates']['start'] = $this->control('date', 'Start date', $values['start'] ?? '', 'Leave blank to start today automatically. Dates use the Howard calendar timezone (America/New_York).');
    $controls['dates']['end'] = $this->control('date', 'End date (exclusive)', $values['end'] ?? '', 'Events on this date are excluded. Clear Number of days when using an end date. Maximum range: 370 days.');
    $controls['dates']['days'] = $this->control('number', 'Number of days', $values['days'] ?? '', 'Rolling window after the start date. Leave both this and End date blank to use 30 days.') + ['#min' => 1, '#max' => 370];
    $controls['dates']['created_after'] = $this->control('date', 'Created after', $values['created_after'] ?? '', 'Optional filter on when an event was added to the calendar.');
    $controls['dates']['created_before'] = $this->control('date', 'Created before', $values['created_before'] ?? '');

    foreach (['type' => 'Include categories', 'exclude_type' => 'Exclude categories'] as $key => $label) {
      $options = $this->preserveSelections($catalogue['types'], $values[$key] ?? []);
      $controls['taxonomy'][$key] = $this->control('select', $label, $values[$key] ?? [], 'Select any combination of available audiences, event types, subjects, or other calendar categories. Use Ctrl/Command to select multiple options.') + ['#options' => $options, '#multiple' => TRUE, '#size' => 10];
    }
    $controls['taxonomy']['keyword'] = $this->control('textarea', 'Keywords or tags', implode("\n", $values['keyword'] ?? []), 'One keyword or tag per line. Matching follows the calendar API, not a full-text description search.') + ['#rows' => 3];

    foreach (['venue_id' => ['Place', 'venues'], 'group_id' => ['Group or department', 'groups'], 'campus_id' => ['Community / campus', 'communities']] as $key => [$label, $resource]) {
      $selected = $values[$key] ?? '';
      $options = $this->preserveSelections($catalogue[$resource], $selected === '' ? [] : [$selected]);
      $controls['location'][$key] = $this->control('select', $label, $selected) + ['#options' => $options, '#empty_option' => $this->t('- Any -'), '#empty_value' => ''];
    }
    $controls['location']['campus_id']['#description'] = $this->t('Only available if Howard’s calendar defines communities.');

    $controls['refinements']['experience'] = $this->control('select', 'Attendance format', $values['experience'] ?? '') + ['#options' => ['' => $this->t('Any'), 'inperson' => $this->t('In person'), 'virtual' => $this->t('Virtual'), 'hybrid' => $this->t('Hybrid')]];
    $controls['refinements']['featured'] = $this->control('checkbox', 'Featured events only', !empty($values['featured']));
    $controls['refinements']['sponsored'] = $this->control('checkbox', 'Sponsored events only', !empty($values['sponsored']));
    $controls['refinements']['recurring'] = $this->control('select', 'Recurring events', isset($values['recurring']) ? (string) (int) (bool) $values['recurring'] : '') + ['#options' => ['' => $this->t('Any'), '1' => $this->t('Recurring only'), '0' => $this->t('Non-recurring only')]];
    $controls['refinements']['distinct'] = $this->control('checkbox', 'Show each event once', !empty($values['distinct']), 'Show the first matching occurrence of each event. Uncheck to allow repeated occurrences.');
    $sort = ($values['sort'] ?? 'date') === 'distance' ? 'date' : ($values['sort'] ?? 'date');
    $controls['refinements']['sort'] = $this->control('select', 'Sort by', $sort) + ['#options' => ['date' => $this->t('Event date'), 'name' => $this->t('Name'), 'ranking' => $this->t('Popularity'), 'created' => $this->t('Creation date'), 'updated' => $this->t('Updated date')]];
    $controls['refinements']['direction'] = $this->control('select', 'Sort direction', $values['direction'] ?? '') + ['#options' => ['' => $this->t('Calendar default'), 'asc' => $this->t('Ascending'), 'desc' => $this->t('Descending')]];

    return $element;
  }

  /**
   * A labelled form control with translated description.
   */
  private function control(string $type, string $title, $default, string $description = ''): array {
    return ['#type' => $type, '#title' => $this->t($title), '#default_value' => $default, '#description' => $description === '' ? '' : $this->t($description)];
  }

  /**
   * Preserve removed or temporarily unavailable IDs when editing a paragraph.
   */
  private function preserveSelections(array $options, array $selected): array {
    $flat = OptGroup::flattenOptions($options);
    foreach ($selected as $id) {
      if (!isset($flat[$id])) {
        $options[$id] = $this->t('Saved selection (ID @id)', ['@id' => $id]);
      }
    }
    return $options;
  }

  /**
   * Flatten the form's presentation groups to a Localist filter object.
   */
  public static function submittedFilters(array $item): array {
    $filters = [];
    foreach (['dates', 'taxonomy', 'location', 'refinements'] as $group) {
      $filters += $item['filters'][$group] ?? [];
    }
    // Discard retired controls from old or programmatically submitted forms.
    unset($filters['match'], $filters['for'], $filters['near'], $filters['within'], $filters['units'], $filters['bounds']);
    if (($filters['sort'] ?? '') === 'distance') {
      $filters['sort'] = 'date';
    }
    // Drupal's multi-select callback keys selections by ID; store JSON lists.
    foreach (['type', 'exclude_type'] as $key) {
      $filters[$key] = array_values($filters[$key] ?? []);
    }
    $filters['keyword'] = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $filters['keyword'] ?? '')), static fn(string $value): bool => $value !== ''));
    foreach (['featured', 'sponsored', 'distinct'] as $key) {
      $filters[$key] = !empty($filters[$key]);
    }
    // Unlike featured/sponsored, false is a meaningful recurring filter.
    if (isset($filters['recurring']) && $filters['recurring'] !== '') {
      $filters['recurring'] = (bool) $filters['recurring'];
    }
    else {
      unset($filters['recurring']);
    }
    return $filters;
  }

  /**
   * Validate cross-field combinations before the entity is saved.
   */
  public static function validateFilters(array &$element, FormStateInterface $form_state, array &$form): void {
    $item = $form_state->getValue($element['#parents'], []);
    try {
      EventsQuery::build(static::submittedFilters($item));
    }
    catch (\InvalidArgumentException $exception) {
      $form_state->setError($element, $exception->getMessage());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as &$item) {
      $item = ['value' => json_encode(static::submittedFilters($item), JSON_THROW_ON_ERROR)];
    }
    return $values;
  }

}
