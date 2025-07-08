# API Reference Index

This section provides comprehensive API documentation for Howard Paragraphs, including hooks, services, and extension points.

## Core APIs

### Hooks
- [hook_howard_paragraphs_alter()](hooks.md#hook_howard_paragraphs_alter)
- [hook_howard_paragraphs_cache_clear()](hooks.md#hook_howard_paragraphs_cache_clear)
- [hook_howard_paragraphs_external_content_alter()](hooks.md#hook_howard_paragraphs_external_content_alter)

### Services
- [HowardNewsService](services.md#howard-news-service)
- [HowardProfilesService](services.md#howard-profiles-service)
- [HowardGivingService](services.md#howard-giving-service)
- [HowardExternalContentCacheClear](services.md#cache-clear-service)

### Controllers
- [HowardExternalContentCacheClear](controllers.md#external-content-cache-clear)

### Utilities
- [External Content Helpers](utilities.md#external-content-helpers)
- [Cache Management](utilities.md#cache-management)
- [Template Helpers](utilities.md#template-helpers)

## Component APIs

### Base Component Methods
```php
// Basic paragraph component structure
interface HowardParagraphComponentInterface {
  public function build();
  public function validateConfiguration();
  public function getCacheContexts();
  public function getCacheTags();
  public function getCacheMaxAge();
}
```

### External Content Components
```php
// External content component interface
interface HowardExternalContentInterface {
  public function fetchExternalContent($parameters);
  public function getCacheKey($parameters);
  public function getApiEndpoint();
  public function processApiResponse($response);
}
```

## Configuration APIs

### Field Configuration
```php
// Field configuration structure
$field_config = [
  'field_name' => 'field_example',
  'type' => 'text',
  'label' => 'Example Field',
  'required' => TRUE,
  'cardinality' => 1,
  'settings' => [
    'max_length' => 255,
  ],
];
```

### Display Configuration
```php
// Display configuration structure
$display_config = [
  'type' => 'text_default',
  'label' => 'hidden',
  'settings' => [],
  'weight' => 0,
];
```

## Quick Reference

### Common Tasks

#### Create Custom Component
```php
// 1. Create info file
// 2. Define field configuration
// 3. Create templates
// 4. Add preprocessing if needed
// 5. Export configuration
```

#### Extend External Content
```php
// Implement HowardExternalContentInterface
class CustomExternalService implements HowardExternalContentInterface {
  public function fetchExternalContent($parameters) {
    // Implementation
  }
}
```

#### Add Custom Cache Tags
```php
function mymodule_preprocess_paragraph(&$variables) {
  $variables['#cache']['tags'][] = 'custom_tag';
}
```

### Service Injection
```php
// Inject Howard Paragraphs services
public function __construct(HowardNewsService $news_service) {
  $this->newsService = $news_service;
}
```

For detailed API documentation, see the individual sections linked above.
