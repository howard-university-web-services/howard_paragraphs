<?php

namespace Drupal\howard_paragraphs\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Cache\Cache;

/**
 * Controller that helps load and clear external content feed caches for Howard websites.
 */
class HowardExternalContentCacheClear extends ControllerBase {

  /**
   * Gets paragraphs that contain external content feeds.
   */
  public static function clearExternalContent() {
    $query = \Drupal::entityQuery('paragraph')
    ->condition('type', [
      'hp_announcements_feed',
      'hp_twitter_feed',
      'hp_deadline_feed',
      'hp_events_feed',
      'hp_facebook_feed',
      'hp_giving_feed',
      'hp_instagram_feed',
      'hp_magazine_feed',
      'hp_news_feed',
      'hp_profiles_feed',
      'hp_program',
      'hp_programs_feed',
      'hp_alumni_featured',
      'hp_alumni_feed',
    ], 'IN');
    $pids = $query->execute();
    $cids = [];
    foreach ($pids as $key => $pid) {
      $paragraph = Paragraph::load($pid);
      $tags = $paragraph->getCacheTags();
      $cids[] = $tags;
      Cache::invalidateTags($tags);

    }
    $message = 'Howard external content feed caches cleared.';
    \Drupal::logger('howard_paragraphs')->notice($message);
    return $cids;
  }

  /**
   * Returns a simple page, when functionality is run manually.
   *
   * @return array
   *   A simple renderable array.
   */
  public function bootstrapCacheClear() {
    $paragraph_cids = $this->clearExternalContent();
    return [
      '#theme' => 'external_content_cache_clear',
      '#cids' => $paragraph_cids,
    ];
  }

}
