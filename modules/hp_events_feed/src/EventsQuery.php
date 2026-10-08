<?php

namespace Drupal\hp_events_feed;

/**
 * Validates and builds the documented public Localist event-list query.
 */
final class EventsQuery {

  /**
   * Allowed enum values, shared by validation and the editing widget.
   */
  public const ENUMS = [
    'experience' => ['inperson', 'virtual', 'hybrid'],
    'sort' => ['date', 'name', 'ranking', 'created', 'updated'],
    'direction' => ['asc', 'desc'],
  ];

  /**
   * Default feed: upcoming month, ordered by date, one row per event.
   */
  public static function defaults(): array {
    return ['days' => 30, 'sort' => 'date', 'distinct' => TRUE];
  }

  /**
   * Decode the stored filter field; reject damaged JSON instead of broadening.
   */
  public static function decode(?string $value): array {
    if ($value === NULL || $value === '') {
      return self::defaults();
    }
    $decoded = json_decode($value, TRUE, 512, JSON_THROW_ON_ERROR);
    if (!is_array($decoded) || (array_is_list($decoded) && $decoded !== [])) {
      throw new \InvalidArgumentException('Event filters must be a JSON object.');
    }
    return $decoded;
  }

  /**
   * Build an allowlisted query, enforcing Localist's date and paging limits.
   *
   * Only published events are requested. Response-expansion and user-specific
   * attendance parameters are deliberately excluded from this public feed.
   */
  public static function build(array $filters, int $count = 6, int $page = 1, ?string $today = NULL): array {
    $today ??= (new \DateTimeImmutable('now', new \DateTimeZone('America/New_York')))->format('Y-m-d');
    // Matching and display context are fixed; retired settings are ignored.
    $query = ['pp' => max(1, min(100, $count)), 'page' => max(1, min(10000, $page)), 'publish_status' => 'published', 'match' => 'all', 'for' => 'widget'];
    // Previously saved distance sorts need no geographic input after removal.
    if (($filters['sort'] ?? '') === 'distance') {
      $filters['sort'] = 'date';
    }
    foreach (self::ENUMS as $key => $allowed) {
      if (isset($filters[$key]) && $filters[$key] !== '') {
        if (!is_string($filters[$key]) || !in_array($filters[$key], $allowed, TRUE)) {
          throw new \InvalidArgumentException("Invalid $key selection.");
        }
        $query[$key] = $filters[$key];
      }
    }
    foreach (['start', 'end', 'created_before', 'created_after'] as $key) {
      if (!empty($filters[$key])) {
        self::date($filters[$key], $key);
        $query[$key] = $filters[$key];
      }
    }
    // Explicitly key rolling feeds by calendar day in the calendar's timezone.
    $query['start'] ??= $today;
    if (!empty($query['end'])) {
      if (!empty($filters['days'])) {
        throw new \InvalidArgumentException('Use an end date or a number of days, not both.');
      }
      $days = (int) (new \DateTimeImmutable($query['start']))->diff(new \DateTimeImmutable($query['end']))->format('%r%a');
      if ($days < 1 || $days > 370) {
        throw new \InvalidArgumentException('The end date must be after the start date and within 370 days (end date is exclusive).');
      }
    }
    else {
      $days = $filters['days'] ?? 30;
      if ($days === '') {
        $days = 30;
      }
      if (filter_var($days, FILTER_VALIDATE_INT) === FALSE || (int) $days < 1 || (int) $days > 370) {
        throw new \InvalidArgumentException('The date window must be between 1 and 370 days.');
      }
      $query['days'] = (int) $days;
    }
    foreach (['campus_id', 'venue_id', 'group_id'] as $key) {
      if (isset($filters[$key]) && $filters[$key] !== '') {
        $query[$key] = self::id($filters[$key]);
      }
    }
    foreach (['type', 'exclude_type', 'keyword'] as $key) {
      $values = $filters[$key] ?? [];
      if (!is_array($values)) {
        throw new \InvalidArgumentException("$key must be a list.");
      }
      foreach ($values as $value) {
        if ($value === '') {
          continue;
        }
        if ($key === 'keyword') {
          if (!is_string($value) || mb_strlen($value) > 255) {
            throw new \InvalidArgumentException('Keywords must be text of at most 255 characters each.');
          }
          $value = trim($value);
          if ($value === '') {
            continue;
          }
        }
        else {
          $value = self::id($value);
        }
        $query[$key][] = $value;
      }
      if (isset($query[$key])) {
        $query[$key] = array_values(array_unique($query[$key]));
      }
    }
    foreach (['featured', 'sponsored', 'recurring', 'distinct'] as $key) {
      if (isset($filters[$key]) && $filters[$key] !== '') {
        $value = filter_var($filters[$key], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($value === NULL) {
          throw new \InvalidArgumentException("Invalid $key selection.");
        }
        $query[$key] = $value ? 'true' : 'false';
      }
    }
    return $query;
  }

  /**
   * Encode repeated parameters as type[]=ID, as required by Localist.
   */
  public static function encode(array $query): string {
    $parts = [];
    foreach ($query as $key => $value) {
      foreach (is_array($value) ? $value : [$value] as $item) {
        $parts[] = rawurlencode($key . (is_array($value) ? '[]' : '')) . '=' . rawurlencode((string) $item);
      }
    }
    return implode('&', $parts);
  }

  /**
   * Validate a positive ID without truncating Localist's large numeric IDs.
   */
  private static function id($value): string {
    if ((!is_string($value) && !is_int($value)) || !preg_match('/^[1-9][0-9]*$/D', (string) $value)) {
      throw new \InvalidArgumentException('Filter, place, group, and community IDs must be positive integers.');
    }
    return (string) $value;
  }

  /**
   * Validate an exact ISO calendar date (no relative date interpretation).
   */
  private static function date($value, string $key): void {
    if (!is_string($value)) {
      throw new \InvalidArgumentException("$key must be a date in YYYY-MM-DD format.");
    }
    $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    if (!$date || $date->format('Y-m-d') !== $value) {
      throw new \InvalidArgumentException("$key must be a valid date in YYYY-MM-DD format.");
    }
  }

}
