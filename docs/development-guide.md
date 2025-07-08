# Development Guide Index

This section covers development topics for Howard Paragraphs, including creating custom components, extending existing functionality, and contributing to the project.

## Development Topics

### Getting Started
- [Development Environment Setup](development-guide.md#development-environment-setup)
- [Code Architecture](architecture.md)
- [Development Workflow](development-guide.md#development-workflow)

### Component Development
- [Creating Custom Components](development-guide.md#creating-custom-components)
- [Extending Existing Components](development-guide.md#extending-existing-components)
- [Template Development](development-guide.md#template-development)
- [Service Development](development-guide.md#service-development)

### Advanced Topics
- [External Content Integration](external-content.md)
- [Performance Optimization](development-guide.md#performance-optimization)
- [Testing Components](development-guide.md#testing-components)
- [Deployment Strategies](development-guide.md#deployment-strategies)

### Reference
- [Coding Standards](development-guide.md#coding-standards)
- [API Documentation](api-reference.md)
- [Hook Documentation](development-guide.md#hook-documentation)
- [Configuration Schema](development-guide.md#configuration-schema)

## Quick Start for Developers

### 1. Set Up Development Environment
```bash
# Clone the repository
git clone https://github.com/howard-university-web-services/howard_paragraphs.git

# Install dependencies
composer install

# Set up local development
drush site:install
drush en howard_paragraphs
```

### 2. Create Your First Component
```bash
# Create component directory
mkdir modules/hp_my_component

# Create info file
touch modules/hp_my_component/hp_my_component.info.yml

# Create module file
touch modules/hp_my_component/hp_my_component.module
```

### 3. Development Tools
- **Drupal Console**: For code generation
- **Drush**: For site management
- **Xdebug**: For debugging
- **PHPUnit**: For testing
- **Devel Module**: For development utilities

## Component Development Workflow

### Planning Phase
1. **Define Requirements**: What does the component need to do?
2. **Design Fields**: What fields will the component need?
3. **Plan Templates**: How will the component render?
4. **Consider Performance**: Will it need caching or optimization?

### Development Phase
1. **Create Module Structure**: Set up directories and files
2. **Define Configuration**: Create field and display configurations
3. **Implement Templates**: Create Twig templates
4. **Add Services**: Implement any required services
5. **Write Tests**: Create unit and functional tests

### Testing Phase
1. **Unit Testing**: Test individual functions
2. **Integration Testing**: Test component integration
3. **Manual Testing**: Test in browser
4. **Accessibility Testing**: Ensure WCAG compliance
5. **Performance Testing**: Check load times and caching

### Deployment Phase
1. **Export Configuration**: Export component configuration
2. **Documentation**: Update component documentation
3. **Code Review**: Peer review of changes
4. **Staging Testing**: Test in staging environment
5. **Production Deployment**: Deploy to production

## Best Practices

### Code Organization
```
modules/hp_my_component/
├── config/
│   └── install/
│       ├── paragraphs.paragraphs_type.hp_my_component.yml
│       └── field.field.paragraph.hp_my_component.field_title.yml
├── src/
│   └── Services/
│       └── MyComponentService.php
├── templates/
│   └── paragraph--hp-my-component.html.twig
├── hp_my_component.info.yml
├── hp_my_component.module
├── hp_my_component.services.yml
└── README.md
```

### Configuration Management
- Use configuration files for field definitions
- Export configuration after development
- Test configuration imports in different environments
- Document configuration dependencies

### Performance Considerations
- Implement proper caching strategies
- Use lazy loading for heavy components
- Optimize database queries
- Monitor external API calls

### Security Guidelines
- Sanitize all user inputs
- Use Drupal's security APIs
- Implement proper access controls
- Validate external content

## Component Architecture Patterns

### Simple Display Components
For basic content display (like Cards, Media):
```php
// Minimal module file
function hp_my_component_theme() {
  return [
    'paragraph__hp_my_component' => [
      'base hook' => 'paragraph',
    ],
  ];
}
```

### External Content Components
For components that fetch external data:
```php
// Service-based architecture
class MyExternalService {
  protected $httpClient;
  
  public function __construct(ClientInterface $httpClient) {
    $this->httpClient = $httpClient;
  }
  
  public function fetchData() {
    // Implementation
  }
}
```

### Interactive Components
For components with JavaScript functionality:
```php
// Add library attachments
function hp_my_component_preprocess_paragraph(&$variables) {
  if ($variables['paragraph']->getType() === 'hp_my_component') {
    $variables['#attached']['library'][] = 'hp_my_component/interactive';
  }
}
```

## Testing Strategy

### Unit Tests
```php
class MyComponentServiceTest extends UnitTestCase {
  public function testDataFetching() {
    // Test service functionality
  }
}
```

### Functional Tests
```php
class MyComponentTest extends BrowserTestBase {
  public function testComponentDisplay() {
    // Test component rendering
  }
}
```

### JavaScript Testing
```javascript
// Test interactive functionality
describe('My Component', function() {
  it('should handle user interactions', function() {
    // Test implementation
  });
});
```

## Debugging Tools

### Development Modules
- **Devel**: General development utilities
- **Devel PHP**: PHP execution and debugging
- **Webprofiler**: Performance profiling
- **Stage File Proxy**: File handling in development

### Debugging Techniques
```php
// Use devel functions for debugging
dpm($variables); // Print variable in messages
kint($variables); // Advanced variable inspection
\Drupal::logger('hp_my_component')->debug('Debug message');
```

### Performance Monitoring
- Use Drupal's built-in profiling
- Monitor database queries
- Check cache hit rates
- Analyze external API response times

## Contributing Guidelines

### Code Standards
- Follow Drupal coding standards
- Use consistent naming conventions
- Write comprehensive documentation
- Include proper error handling

### Pull Request Process
1. Create feature branch
2. Implement changes
3. Write/update tests
4. Update documentation
5. Submit pull request
6. Address review feedback

### Documentation Requirements
- Update component documentation
- Add inline code comments
- Include usage examples
- Document configuration options

Ready to start developing? Begin with the development environment setup below and work through the component development process.

## Development Environment Setup

### Prerequisites

Before developing with Howard Paragraphs:

- **PHP 8.1+** with required extensions
- **Drupal 10.x or 11.x** development environment
- **Git** for version control
- **Composer** for dependency management
- **Node.js and npm** (for frontend development)

### Local Development Setup

1. **Clone the Repository**
   ```bash
   git clone https://github.com/howard-university-web-services/howard_paragraphs.git
   cd howard_paragraphs
   ```

2. **Install Dependencies**
   ```bash
   composer install
   composer require --dev drupal/coder phpunit/phpunit
   ```

3. **Set Up Development Environment**
   ```bash
   # Enable development modules
   drush en devel webprofiler stage_file_proxy
   
   # Configure local settings
   cp sites/example.settings.local.php sites/default/settings.local.php
   ```
