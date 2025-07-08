# Architecture Overview

Understanding the architecture of Howard Paragraphs will help you make the most of the module and customize it effectively for your needs.

## High-Level Architecture

Howard Paragraphs follows a modular, service-oriented architecture designed for scalability, maintainability, and flexibility.

```
Howard Paragraphs (Base Module)
├── Core Services (External Content, Caching)
├── Base Templates & Themes
├── Shared Utilities & Controllers
└── Submodules (Individual Components)
    ├── hp_cards/
    ├── hp_news_feed/
    ├── hp_alumni_feed/
    └── ... (40+ components)
```

## Core Components

### Base Module (`howard_paragraphs`)

The base module provides:

- **Core Services**: External content fetching and cache management
- **Base Templates**: Default paragraph rendering templates
- **Shared Controllers**: Cache clearing and administrative functions
- **Common Hooks**: Preprocessing and theme functions
- **Configuration**: Routing, services, and menu definitions

### Submodules

Each component is implemented as a separate submodule containing:

- **Configuration**: Field definitions, form displays, view modes
- **Templates**: Twig templates for rendering
- **Assets**: Component-specific CSS/JS (when needed)
- **Documentation**: Component-specific README
- **Services**: Component-specific business logic (when needed)

## Key Architectural Patterns

### Dependency Injection

The module uses Drupal's service container for dependency injection:

```yaml
# howard_paragraphs.services.yml
services:
  howard.news:
    class: Drupal\howard_paragraphs\Services\HowardNewsService
    arguments: ['@http_client']
```

### Service Layer

External content integration is handled through dedicated services:

- `HowardNewsService`: Fetches news from thedig.howard.edu
- `HowardProfilesService`: Fetches profiles from profiles.howard.edu
- `HowardGivingService`: Fetches giving content from giving.howard.edu

### Template Inheritance

Templates follow Drupal's template suggestion hierarchy:

```
paragraph--hp-cards.html.twig
paragraph--hp-cards--featured.html.twig
paragraph--default.html.twig
field--entity-reference-revisions.html.twig
```

### Configuration Management

Each submodule provides:

- **Default Configuration**: Installed automatically with the module
- **Exportable Configuration**: Can be exported/imported across environments
- **Override Support**: Local customizations without losing update capability

## Data Flow

### Content Creation Flow

1. **User Creates Content**: Editor selects paragraph type
2. **Form Rendering**: Drupal renders the paragraph form
3. **Data Validation**: Field values are validated
4. **Entity Storage**: Paragraph entity is saved to database
5. **Display Rendering**: Templates render the paragraph for display

### External Content Flow

1. **Cron Trigger**: Drupal cron or manual trigger
2. **Service Invocation**: External content services are called
3. **Data Fetching**: HTTP requests fetch external content
4. **Cache Storage**: Content is cached for performance
5. **Cache Invalidation**: Old cache entries are cleared

### Render Pipeline

1. **Entity Load**: Paragraph entity is loaded
2. **Preprocessing**: Hook functions modify variables
3. **Template Selection**: Drupal selects appropriate template
4. **Rendering**: Twig renders the final HTML
5. **Caching**: Rendered output is cached

## Performance Considerations

### Caching Strategy

- **External Content Caching**: 3-hour cache TTL for external feeds
- **Render Caching**: Drupal's built-in render caching
- **Configuration Caching**: Configuration is cached in production

### Lazy Loading

- External content is fetched asynchronously when possible
- Images use lazy loading through the idfive Component Library
- JavaScript components initialize on demand

### Database Optimization

- Efficient entity queries for external content lookup
- Proper indexing on paragraph type fields
- Batch processing for bulk operations

## Security Model

### Input Validation

- All user inputs are sanitized through Drupal's form API
- External content is filtered through text filters
- File uploads use Drupal's file validation

### Permission System

- Administrative functions require `administer site configuration`
- Content creation follows Drupal's standard permissions
- External content clearing has dedicated access controls

### External Content Security

- HTTP client uses SSL verification
- API responses are sanitized before caching
- Rate limiting prevents abuse of external services

## Extensibility Points

### Custom Components

Create new paragraph types by:

1. Creating a new submodule
2. Defining field configuration
3. Creating templates
4. Adding any necessary services

### Service Extension

Extend existing services by:

1. Creating a service decorator
2. Implementing the same interface
3. Adding custom logic
4. Registering in services.yml

### Template Overrides

Override templates by:

1. Copying templates to your theme
2. Modifying as needed
3. Following Drupal's template naming conventions

### Hook Implementations

Extend functionality through:

- `hook_preprocess_paragraph()`
- `hook_paragraph_view_alter()`
- `hook_theme_suggestions_paragraph_alter()`

## Integration Points

### idfive Component Library

- Templates output markup compatible with ICL
- CSS classes follow ICL naming conventions
- JavaScript integration through ICL patterns

### Drupal Core

- Full integration with Paragraphs module
- Uses Entity API for all data operations
- Follows Drupal coding standards

### Third-Party Services

- HTTP client for external API calls
- Configurable endpoints for different environments
- Error handling and fallback strategies

## Development Workflow

### Local Development

1. Enable developer modules (`devel`, `stage_file_proxy`)
2. Use development external content endpoints
3. Clear caches frequently during development
4. Use `drush` commands for efficient development

### Testing Strategy

- Functional tests for each component
- Unit tests for services
- Integration tests for external content
- Visual regression testing with component library

### Deployment Process

1. Export configuration changes
2. Test in staging environment
3. Deploy code and configuration
4. Clear caches and run updates
5. Verify external content functionality

This architecture provides a solid foundation for building scalable, maintainable paragraph components while ensuring good performance and security practices.
