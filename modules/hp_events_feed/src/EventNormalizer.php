<?php

namespace Drupal\hp_events_feed;

/**
 * Converts untrusted Localist event data into plain text and safe URLs.
 */
final class EventNormalizer {

  /**
   * Normalize a result row, selecting an instance inside the requested range.
   */
  public static function normalize(array $event, array $query): ?array {
    // Even with a configured token, this component only displays public events.
    if (!empty($event['private']) || (!empty($event['publish_status']) && $event['publish_status'] !== 'published')) {
      return NULL;
    }
    $title = self::text($event['title'] ?? '');
    $url = self::url($event['localist_url'] ?? '');
    if ($title === '' || $url === '') {
      return NULL;
    }
    $start_date = $query['start'];
    $end_date = $query['end'] ?? (new \DateTimeImmutable($start_date))->modify('+' . $query['days'] . ' days')->format('Y-m-d');
    $zone = new \DateTimeZone('America/New_York');
    $instances = [];
    foreach ($event['event_instances'] ?? [] as $wrapper) {
      $instance = $wrapper['event_instance'] ?? [];
      if (!is_array($instance) || empty($instance['start']) || !is_string($instance['start'])) {
        continue;
      }
      try {
        $start = new \DateTimeImmutable($instance['start']);
        $date = (!empty($instance['all_day']) ? $start : $start->setTimezone($zone))->format('Y-m-d');
        if ($date >= $start_date && $date < $end_date) {
          $instances[] = ['start' => $start, 'instance' => $instance];
        }
      }
      catch (\Exception $exception) {
        // Missing or malformed dates must not turn into today's date.
      }
    }
    if (!$instances) {
      return NULL;
    }
    usort($instances, static fn(array $a, array $b): int => $a['start'] <=> $b['start']);
    $start = $instances[0]['start'];
    $instance = $instances[0]['instance'];
    $all_day = !empty($instance['all_day']);
    $end = NULL;
    if (!empty($instance['end']) && is_string($instance['end'])) {
      try {
        $candidate = new \DateTimeImmutable($instance['end']);
        if ($candidate >= $start) {
          $end = $candidate;
        }
      }
      catch (\Exception $exception) {
        // End times are optional.
      }
    }
    // Keep all-day calendar dates intact, rather than shifting them by timezone.
    $display_start = $all_day ? $start : $start->setTimezone($zone);
    $display_end = $end ? ($all_day ? $end : $end->setTimezone($zone)) : NULL;
    $description = self::text($event['description_text'] ?? $event['description'] ?? '');
    $summary = mb_strlen($description) > 240 ? mb_substr($description, 0, 237) . '…' : $description;
    return [
      'id' => (string) ($event['id'] ?? ''),
      'title' => $title,
      'url' => $url,
      'image' => self::url($event['photo_url'] ?? ''),
      'summary' => $summary,
      'start' => $start->format(DATE_ATOM),
      'end' => $end ? $end->format(DATE_ATOM) : '',
      'date' => $display_start->format('F d, Y'),
      'time' => $all_day ? '' : $display_start->format('g:i a T'),
      'time_short' => $all_day ? '' : $display_start->format('g:ia'),
      'end_date' => $display_end ? $display_end->format('F d, Y') : '',
      'end_time' => $display_end && !$all_day ? $display_end->format('g:i a T') : '',
      'end_time_short' => $display_end && !$all_day ? $display_end->format('g:ia') : '',
      'all_day' => $all_day,
      'location' => self::text($event['location_name'] ?? $event['location'] ?? ''),
      'room' => self::text($event['room_number'] ?? ''),
      'experience' => in_array($event['experience'] ?? '', ['inperson', 'virtual', 'hybrid'], TRUE) ? $event['experience'] : '',
      'status' => in_array($event['status'] ?? '', ['canceled', 'postponed', 'soldout'], TRUE) ? $event['status'] : '',
      'cost' => self::text($event['ticket_cost'] ?? ''),
      'free' => !empty($event['free']),
      'ticket_url' => self::url($event['ticket_url'] ?? ''),
      'ics_url' => self::url($event['localist_ics_url'] ?? ''),
    ];
  }

  /**
   * Plain text only; Twig performs the final contextual escaping.
   */
  public static function text($value): string {
    if (!is_string($value)) {
      return '';
    }
    $value = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return trim(preg_replace('/\s+/u', ' ', $value) ?? '');
  }

  /**
   * External URLs must be HTTP(S), with no embedded credentials.
   */
  public static function url($value): string {
    if (!is_string($value)) {
      return '';
    }
    $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $parts = parse_url($value);
    if (!$parts || !in_array(strtolower($parts['scheme'] ?? ''), ['https', 'http'], TRUE) || empty($parts['host']) || isset($parts['user']) || isset($parts['pass']) || preg_match('/[\x00-\x20]/', $value)) {
      return '';
    }
    return $value;
  }

}
