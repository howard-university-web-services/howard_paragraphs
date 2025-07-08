<?php

namespace Drupal\howard_paragraphs\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Messenger\MessengerInterface;
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
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a new HowardExternalContentCacheClear object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_tags_invalidator
   *   The cache tags invalidator.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    CacheTagsInvalidatorInterface $cache_tags_invalidator, 
    LoggerChannelFactoryInterface $logger_factory,
    MessengerInterface $messenger
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
    $this->loggerFactory = $logger_factory;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('cache_tags.invalidator'),
      $container->get('logger.factory'),
      $container->get('messenger')
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
    
    // Use batch processing for large number of paragraphs to avoid timeouts
    if (count($pids) > 50) {
      $batch = [
        'title' => $this->t('Clearing external content cache'),
        'operations' => [
          [[$this, 'processBatchClear'], [$pids]],
        ],
        'finished' => [[$this, 'processBatchFinished']],
        'progressive' => TRUE,
      ];
      batch_set($batch);
      
      // If this is being run as part of a form/UI action, process the batch
      if (php_sapi_name() != 'cli') {
        return batch_process();
      }
      // Otherwise, process the batch directly (for cron/drush)
      else {
        $batch =& batch_get();
        $batch['progressive'] = FALSE;
        batch_process();
      }
      
      // Return empty array as the batch process will handle creating the cids
      return [];
    }
    
    // For smaller number of paragraphs, process directly
    $cids = [];
    
    // Load all paragraphs at once to avoid multiple database queries
    if (!empty($pids)) {
      $paragraphs = $this->entityTypeManager->getStorage('paragraph')->loadMultiple($pids);
      foreach ($paragraphs as $paragraph) {
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

  /**
   * Process a batch of paragraphs for cache clearing.
   *
   * @param array $pids
   *   Array of paragraph IDs to process.
   * @param array $context
   *   The batch context.
   */
  public function processBatchClear(array $pids, array &$context) {
    if (!isset($context['sandbox']['progress'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['max'] = count($pids);
      $context['sandbox']['pids'] = $pids;
      $context['results']['cids'] = [];
    }
    
    // Process 20 paragraphs at a time
    $batch_size = 20;
    $batch_pids = array_slice($context['sandbox']['pids'], $context['sandbox']['progress'], $batch_size);
    
    if (!empty($batch_pids)) {
      $paragraphs = $this->entityTypeManager->getStorage('paragraph')->loadMultiple($batch_pids);
      foreach ($paragraphs as $paragraph) {
        $tags = $paragraph->getCacheTags();
        $context['results']['cids'][] = $tags;
        $this->cacheTagsInvalidator->invalidateTags($tags);
        $context['sandbox']['progress']++;
      }
    }
    
    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
    else {
      $context['finished'] = 1;
    }
  }

  /**
   * Finish batch processing.
   *
   * @param bool $success
   *   Whether the batch was successful.
   * @param array $results
   *   The batch results.
   * @param array $operations
   *   The operations processed.
   */
  public function processBatchFinished($success, array $results, array $operations) {
    if ($success) {
      $cleared = count($results['cids']);
      $message = $this->formatPlural(
        $cleared,
        'Cleared cache for 1 external content paragraph.',
        'Cleared cache for @count external content paragraphs.'
      );
      $this->messenger()->addStatus($message);
      $this->loggerFactory->get('howard_paragraphs')->notice('Batch cleared @count external content paragraph caches.', ['@count' => $cleared]);
    }
    else {
      $this->messenger()->addError($this->t('An error occurred while clearing external content caches.'));
      $this->loggerFactory->get('howard_paragraphs')->error('Error occurred during batch clearing of external content caches.');
    }
  }

}
