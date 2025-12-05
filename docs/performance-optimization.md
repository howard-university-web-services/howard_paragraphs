# Performance Optimizations for Howard Paragraphs

This document outlines simple performance improvements that can be made to the Howard Paragraphs module to enhance speed, reduce resource usage, and improve the user experience.

## 1. Cache Optimization

### Cache Tags Instead of Time-Based Expiration

**Before:**

```php
$this->cache->set($cache_id, $result, time() + 7200);
```

**After:**

```php
use Drupal\Core\Cache\Cache;

$this->cache->set(
  $cache_id,
  $result,
  Cache::PERMANENT,
  ['howard_news_data', 'howard_external_content']
);
```

**Benefits:**

- More precise cache invalidation
- No unnecessary expiration of still-valid content
- Better integration with Drupal's cache system

### Implement in these files

- `src/Services/HowardNewsService.php`
- `src/Services/HowardProfilesService.php`
- `src/Services/HowardGivingService.php`
- `modules/hp_youtube_playlist/src/Services/HowardYoutubeService.php`

## 2. Batch Processing for Cache Clearing

The current cache clearing process processes all paragraphs at once, which can cause performance issues on sites with many paragraphs.

**Implementation:**

```php
public function clearExternalContent() {
  $query = $this->entityTypeManager->getStorage('paragraph')->getQuery()
    ->condition('type', [
      'hp_announcements_feed',
      'hp_twitter_feed',
      // other paragraph types...
    ], 'IN')
    ->accessCheck(FALSE);
  
  $pids = $query->execute();
  
  // Use Batch API for sites with many paragraphs
  if (count($pids) > 50) {
    $batch = [
      'title' => $this->t('Clearing external content cache'),
      'operations' => [
        [[$this, 'processBatchClear'], [$pids]],
      ],
      'finished' => [[$this, 'processBatchFinished']],
    ];
    batch_set($batch);
    return batch_process('admin/config/howard_paragraphs');
  }
  else {
    // Original method for smaller sites
    $cids = [];
    if (!empty($pids)) {
      $paragraphs = $this->entityTypeManager->getStorage('paragraph')->loadMultiple($pids);
      foreach ($paragraphs as $paragraph) {
        // Process each paragraph...
      }
    }
    return $cids;
  }
}
```

## 3. Lazy Load External Content

For non-critical content feeds, implement lazy loading to improve initial page load times.

**Implementation:**

1. Add a data attribute to templates:

```twig
<div class="hp-news-feed" 
     data-lazy-load="true" 
     data-feed-url="{{ feed_url }}"
     data-cache-id="{{ cache_id }}">
  <div class="loading-placeholder">Loading news...</div>
</div>
```

1. Add JavaScript to load content when visible:

```javascript
Drupal.behaviors.lazyLoadFeeds = {
  attach: function (context, settings) {
    const feeds = once('lazy-load', '[data-lazy-load="true"]', context);
    
    if (feeds.length > 0) {
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const el = entry.target;
            const url = el.getAttribute('data-feed-url');
            const cacheId = el.getAttribute('data-cache-id');
            
            fetch('/howard-paragraphs/ajax/feed?url=' + encodeURIComponent(url) + '&cache_id=' + cacheId)
              .then(response => response.json())
              .then(data => {
                el.innerHTML = data.content;
              });
            
            observer.unobserve(el);
          }
        });
      });
      
      feeds.forEach(feed => observer.observe(feed));
    }
  }
};
```

## 4. Optimize Database Queries

Improve database queries in the cache clearing controller.

**Before:**

```php
$query = $this->entityTypeManager->getStorage('paragraph')->getQuery()
  ->condition('type', [...], 'IN')
  ->accessCheck(FALSE);
$pids = $query->execute();

foreach ($pids as $key => $pid) {
  $paragraph = Paragraph::load($pid);
  // process paragraph...
}
```

**After:**

```php
$query = $this->entityTypeManager->getStorage('paragraph')->getQuery()
  ->condition('type', [...], 'IN')
  ->accessCheck(FALSE);
$pids = $query->execute();

// Load paragraphs in a single operation
if (!empty($pids)) {
  $paragraphs = $this->entityTypeManager->getStorage('paragraph')->loadMultiple($pids);
  foreach ($paragraphs as $paragraph) {
    // process paragraph...
  }
}
```

## 5. Implement Cache Warming

Proactively cache frequently accessed content to prevent cache misses during peak times.

**Implementation:**

```php
/**
 * Implements hook_cron().
 */
function howard_paragraphs_cron() {
  // Run cache warming every 6 hours
  $last_run = \Drupal::state()->get('howard_paragraphs.last_cache_warm', 0);
  if (time() - $last_run > 21600) {
    _howard_paragraphs_warm_caches();
    \Drupal::state()->set('howard_paragraphs.last_cache_warm', time());
  }
}

/**
 * Warms up commonly accessed caches.
 */
function _howard_paragraphs_warm_caches() {
  // Get frequently accessed news sources
  $news_service = \Drupal::service('howard_paragraphs.news_service');
  $popular_urls = [
    'https://thedig.howard.edu/jsonapi/node/article?sort[sort-published][path]=field_date&sort[sort-published][direction]=DESC&page[limit]=10',
    // Add other frequently accessed URLs
  ];
  
  foreach ($popular_urls as $url) {
    $cache_id = 'howard_news:' . md5($url);
    $news_service->getData($cache_id, $url);
  }
}
```

## 6. Compress API Responses

Add response compression for large API responses.

**Implementation:**

```php
$request = $this->client->get($url . '&filter[status][value]=1', [
  'verify' => TRUE,
  'timeout' => 30,
  'connect_timeout' => 10,
  'headers' => [
    'Accept' => 'application/json',
    'User-Agent' => 'Howard Paragraphs Module/1.0',
    'Accept-Encoding' => 'gzip, deflate', // Request compressed response
  ],
  'decode_content' => true, // Automatically handle decompression
]);
```

## Implementation Priority

1. **High Impact, Low Effort**
   - Cache Tags instead of time-based expiration
   - Optimize database queries
   - Compress API responses

2. **Medium Impact, Medium Effort**
   - Batch processing for cache clearing
   - Cache warming

3. **High Impact, Higher Effort**
   - Lazy load external content

These optimizations can significantly improve the performance of sites using the Howard Paragraphs module without requiring a major refactoring of the codebase.

## SEO and Structured Data Performance

Howard Paragraphs includes comprehensive Schema.org structured data implementation that provides SEO benefits with minimal performance impact. The structured data is implemented as lightweight JSON-LD scripts that add less than 2KB per component on average.

For details on the SEO implementation, see the [Schema.org Implementation Guide](schema-org-implementation.md).
