# External Content & Caching

Howard Paragraphs includes sophisticated external content integration that fetches data from various Howard University services. This guide covers how the system works and how to configure it.

## Overview

The external content system allows paragraph components to display live data from:

- **thedig.howard.edu**: News and articles
- **profiles.howard.edu**: Student and alumni profiles
- **giving.howard.edu**: Giving campaigns and fundraising
- **magazine.howard.edu**: Magazine articles and features
- **programs.howard.edu**: Academic programs

### Deprecated Services
- **calendar.howard.edu**: Events and deadlines (deprecated - use alternative event management)
- **Facebook feeds**: Social media integration (deprecated due to API limitations)
- **Photoshelter feeds**: Image gallery integration (deprecated service)

## Architecture

### Service Layer

External content is managed through dedicated services:

```php
// Core services defined in howard_paragraphs.services.yml
services:
  howard.news:
    class: Drupal\howard_paragraphs\Services\HowardNewsService
    arguments: ['@http_client']
  howard.profiles:
    class: Drupal\howard_paragraphs\Services\HowardProfilesService
    arguments: ['@http_client']
  howard.giving:
    class: Drupal\howard_paragraphs\Services\HowardGivingService
    arguments: ['@http_client']
```

### Cache Management

The system implements intelligent caching:

- **Cache TTL**: 3 hours (10800 seconds) by default
- **Cache Tags**: Specific to each paragraph instance
- **Cache Bins**: Dedicated cache bins for external content
- **Invalidation**: Automatic cache clearing via cron

### Data Flow

1. **Request**: Component requests external data
2. **Cache Check**: System checks for cached data
3. **API Call**: If cache miss, fetch from external API
4. **Data Processing**: Clean and structure the data
5. **Cache Storage**: Store processed data with TTL
6. **Render**: Display the cached data

## Configuration

### API Endpoints

Configure API endpoints in settings.php:

```php
// Production endpoints (default)
$config['howard_paragraphs.settings']['news_api_endpoint'] = 'https://thedig.howard.edu/api';
$config['howard_paragraphs.settings']['profiles_api_endpoint'] = 'https://profiles.howard.edu/api';
$config['howard_paragraphs.settings']['giving_api_endpoint'] = 'https://giving.howard.edu/api';

// Development endpoints
$config['howard_paragraphs.settings']['news_api_endpoint'] = 'https://dev.thedig.howard.edu/api';
$config['howard_paragraphs.settings']['profiles_api_endpoint'] = 'https://dev.profiles.howard.edu/api';
```

### Cache Settings

Customize cache behavior:

```php
// Cache TTL (in seconds)
$config['howard_paragraphs.settings']['cache_ttl'] = 10800; // 3 hours

// Cache bins
$config['howard_paragraphs.settings']['cache_bins'] = [
  'external_news' => 'cache.backend.database',
  'external_profiles' => 'cache.backend.database',
  'external_giving' => 'cache.backend.database',
];

// Enable/disable external content
$config['howard_paragraphs.settings']['external_content_enabled'] = TRUE;
```

### Environment-Specific Configuration

```php
// Development environment
if (getenv('ENVIRONMENT') === 'development') {
  $config['howard_paragraphs.settings']['cache_ttl'] = 300; // 5 minutes
  $config['howard_paragraphs.settings']['debug_mode'] = TRUE;
}

// Staging environment
if (getenv('ENVIRONMENT') === 'staging') {
  $config['howard_paragraphs.settings']['cache_ttl'] = 1800; // 30 minutes
  $config['howard_paragraphs.settings']['use_staging_apis'] = TRUE;
}
```

## Feed Components

### News Feed (`hp_news_feed`)

Fetches articles from thedig.howard.edu:

```php
// Example API response structure
{
  "articles": [
    {
      "id": 12345,
      "title": "Howard University Announces New Research Initiative",
      "summary": "Leading groundbreaking research in AI and machine learning",
      "image": "https://thedig.howard.edu/files/article-image.jpg",
      "url": "https://thedig.howard.edu/article/12345",
      "published_date": "2025-01-15T10:00:00Z",
      "category": "Research",
      "tags": ["AI", "Research", "Technology"]
    }
  ]
}
```

Configuration options:
- **Environment**: Production, staging, or development
- **Category Filter**: Filter by news categories
- **Limit**: Number of articles to display
- **Feed Type**: Different display formats

### Alumni Feed (`hp_alumni_feed`)

Displays featured alumni from profiles.howard.edu:

```php
// Example alumni data structure
{
  "profiles": [
    {
      "id": 67890,
      "name": "Dr. Jane Smith",
      "title": "CEO, Tech Innovations Inc.",
      "graduation_year": "2005",
      "degree": "Computer Science",
      "image": "https://profiles.howard.edu/files/profile-67890.jpg",
      "bio": "Leading tech executive and Howard alumna",
      "url": "https://profiles.howard.edu/profile/67890"
    }
  ]
}
```

### Giving Feed (`hp_giving_feed`)

Shows giving campaigns from giving.howard.edu:

```php
// Example giving campaign structure
{
  "campaigns": [
    {
      "id": 999,
      "title": "Support Student Scholarships",
      "description": "Help provide scholarships for deserving students",
      "goal_amount": 100000,
      "current_amount": 75000,
      "percentage": 75,
      "end_date": "2025-12-31",
      "url": "https://giving.howard.edu/campaign/999"
    }
  ]
}
```

## Caching System

### Cache Implementation

```php
<?php
namespace Drupal\howard_paragraphs\Services;

use Drupal\Core\Cache\CacheBackendInterface;
use GuzzleHttp\ClientInterface;

class HowardNewsService {
  
  protected $httpClient;
  protected $cache;
  
  public function __construct(ClientInterface $http_client, CacheBackendInterface $cache) {
    $this->httpClient = $http_client;
    $this->cache = $cache;
  }
  
  public function getNews($params = []) {
    // Generate cache key
    $cache_key = 'howard_news:' . md5(serialize($params));
    
    // Try to get cached data
    if ($cached = $this->cache->get($cache_key)) {
      return $cached->data;
    }
    
    // Fetch fresh data
    $data = $this->fetchFromAPI($params);
    
    // Cache the data
    $this->cache->set($cache_key, $data, time() + 10800); // 3 hours
    
    return $data;
  }
}
```

### Cache Tags

Each paragraph gets specific cache tags:

```php
// Cache tags for external content
$cache_tags = [
  'howard_paragraphs:external_content',
  'howard_paragraphs:news_feed',
  'paragraph:' . $paragraph->id(),
];
```

### Cache Invalidation

Manual cache clearing:

```php
// Clear all external content cache
Cache::invalidateTags(['howard_paragraphs:external_content']);

// Clear specific feed cache
Cache::invalidateTags(['howard_paragraphs:news_feed']);

// Clear cache for specific paragraph
Cache::invalidateTags(['paragraph:123']);
```

## Cron Integration

### Automatic Cache Clearing

```php
<?php
// howard_paragraphs.module

/**
 * Implements hook_cron().
 */
function howard_paragraphs_cron() {
  // Clear external content cache every cron run
  HowardExternalContentCacheClear::clearExternalContent();
}
```

### Cron Configuration

```php
<?php
// Controller implementation
class HowardExternalContentCacheClear extends ControllerBase {
  
  public static function clearExternalContent() {
    // Get all external content paragraphs
    $query = \Drupal::entityQuery('paragraph')
      ->condition('type', [
        'hp_announcements_feed',
        'hp_news_feed',
        'hp_alumni_feed',
        'hp_giving_feed',
        'hp_magazine_feed',
        'hp_profiles_feed',
        'hp_programs_feed',
      ], 'IN');
    
    $paragraph_ids = $query->execute();
    
    // Clear cache for each paragraph
    foreach ($paragraph_ids as $pid) {
      Cache::invalidateTags(['paragraph:' . $pid]);
    }
    
    \Drupal::logger('howard_paragraphs')->info('Cleared external content cache for @count paragraphs.', [
      '@count' => count($paragraph_ids),
    ]);
  }
}
```

### Scheduled Jobs

For Acquia hosting:

```bash
# Run every 3 hours
0 */3 * * * bash /var/www/html/${AH_SITE_NAME}/scripts/hal_sites.sh core-cron
```

For other hosting:

```bash
# Crontab entry
0 */3 * * * /path/to/drush core-cron
```

## Manual Cache Management

### Administrative Interface

Visit `/admin/config/clear_howard_external_content` to:

- View external content statistics
- Manually clear all external caches
- Monitor API connectivity
- Review cache hit rates

### Drush Commands

```bash
# Clear all caches
drush cache:rebuild

# Clear specific cache bins
drush cache:clear external_content

# Run cron manually
drush core-cron
```

## Error Handling

### API Failures

```php
public function fetchFromAPI($params) {
  try {
    $response = $this->httpClient->request('GET', $this->apiEndpoint, [
      'query' => $params,
      'timeout' => 10,
      'headers' => ['Accept' => 'application/json'],
    ]);
    
    return json_decode($response->getBody(), TRUE);
    
  } catch (\Exception $e) {
    // Log error
    \Drupal::logger('howard_paragraphs')->error('API request failed: @message', [
      '@message' => $e->getMessage(),
    ]);
    
    // Return cached data if available
    if ($cached = $this->cache->get($cache_key . ':backup')) {
      return $cached->data;
    }
    
    // Return empty array as fallback
    return [];
  }
}
```

### Fallback Strategies

1. **Stale Cache**: Return expired cache data if API fails
2. **Default Content**: Display placeholder content
3. **Empty State**: Gracefully handle no content
4. **Error Messages**: Show user-friendly error messages

## Performance Optimization

### HTTP Client Configuration

```php
// Configure HTTP client for external requests
$config['http_client_config'] = [
  'timeout' => 10,
  'connect_timeout' => 5,
  'verify' => TRUE,
  'headers' => [
    'User-Agent' => 'Howard-Paragraphs/1.0',
    'Accept' => 'application/json',
  ],
];
```

### Rate Limiting

```php
// Implement rate limiting
class HowardAPIRateLimiter {
  protected $requests = [];
  
  public function isAllowed($endpoint) {
    $key = md5($endpoint);
    $now = time();
    
    // Clean old requests
    $this->requests[$key] = array_filter(
      $this->requests[$key] ?? [],
      function($time) use ($now) {
        return $now - $time < 60; // Keep requests from last minute
      }
    );
    
    // Check rate limit (max 10 requests per minute)
    if (count($this->requests[$key]) >= 10) {
      return FALSE;
    }
    
    // Record this request
    $this->requests[$key][] = $now;
    return TRUE;
  }
}
```

### Connection Pooling

```php
// Use persistent HTTP connections
$config['http_client_config']['curl'] = [
  CURLOPT_FRESH_CONNECT => FALSE,
  CURLOPT_FORBID_REUSE => FALSE,
];
```

## Monitoring & Analytics

### Logging

```php
// Comprehensive logging
\Drupal::logger('howard_paragraphs')->info('External content fetched', [
  'endpoint' => $endpoint,
  'response_time' => $response_time,
  'cache_hit' => $cache_hit,
  'data_count' => count($data),
]);
```

### Performance Metrics

Track key metrics:
- API response times
- Cache hit rates
- Error frequencies
- Data freshness

### Health Checks

```php
public function healthCheck() {
  $endpoints = [
    'news' => $this->newsApiEndpoint,
    'profiles' => $this->profilesApiEndpoint,
    'giving' => $this->givingApiEndpoint,
  ];
  
  $status = [];
  foreach ($endpoints as $service => $endpoint) {
    try {
      $start = microtime(TRUE);
      $response = $this->httpClient->head($endpoint . '/health');
      $end = microtime(TRUE);
      
      $status[$service] = [
        'status' => $response->getStatusCode() === 200 ? 'healthy' : 'unhealthy',
        'response_time' => round(($end - $start) * 1000, 2) . 'ms',
      ];
    } catch (\Exception $e) {
      $status[$service] = [
        'status' => 'error',
        'error' => $e->getMessage(),
      ];
    }
  }
  
  return $status;
}
```

## Security Considerations

### Input Validation

```php
// Validate API parameters
public function validateParams($params) {
  $allowed_keys = ['category', 'limit', 'offset', 'environment'];
  $params = array_intersect_key($params, array_flip($allowed_keys));
  
  // Sanitize values
  if (isset($params['limit'])) {
    $params['limit'] = min(50, max(1, (int) $params['limit']));
  }
  
  if (isset($params['category'])) {
    $params['category'] = filter_var($params['category'], FILTER_SANITIZE_STRING);
  }
  
  return $params;
}
```

### SSL Verification

```php
// Always verify SSL certificates
$config['http_client_config']['verify'] = TRUE;
```

### API Key Management

```php
// Secure API key handling
$api_key = \Drupal::config('howard_paragraphs.settings')->get('api_key');
if ($api_key) {
  $headers['Authorization'] = 'Bearer ' . $api_key;
}
```

The external content system provides powerful integration capabilities while maintaining performance and reliability through intelligent caching and error handling.
