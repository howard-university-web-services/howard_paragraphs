<?php

namespace Drupal\howard_paragraphs\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Controller that helps load and clear external content feed caches for Howard websites.
 */
class HowardExternalContentCacheClear extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The cache tags invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagsInvalidator;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Constructs a new HowardExternalContentCacheClear object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_tags_invalidator
   *   The cache tags invalidator.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, CacheTagsInvalidatorInterface $cache_tags_invalidator, LoggerChannelFactoryInterface $logger_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
    $this->loggerFactory = $logger_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('cache_tags.invalidator'),
      $container->get('logger.factory')
    );
  }

  /**
   * Gets paragraphs that contain external content feeds.
   *
   * @return array
   *   Array of cache tag arrays that were cleared.
   */
  public function clearExternalContent() {
    $query = $this->entityTypeManager->getStorage('paragraph')->getQuery()
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
      ], 'IN')
      ->accessCheck(FALSE);
    
    $pids = $query->execute();
    $cids = [];
    
    foreach ($pids as $key => $pid) {
      $paragraph = Paragraph::load($pid);
      if ($paragraph) {
        $tags = $paragraph->getCacheTags();
        $cids[] = $tags;
        $this->cacheTagsInvalidator->invalidateTags($tags);
      }
    }
    
    $message = 'Howard external content feed caches cleared.';
    $this->loggerFactory->get('howard_paragraphs')->notice($message);
    return $cids;
  }

  /**
   * Static method for backward compatibility with cron hook.
   *
   * @deprecated Use the service method instead.
   */
  public static function clearExternalContentStatic() {
    $container = \Drupal::getContainer();
    $controller = static::create($container);
    return $controller->clearExternalContent();
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
