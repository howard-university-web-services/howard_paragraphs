# Contributing to Howard Paragraphs

Thank you for your interest in contributing to Howard Paragraphs! This guide will help you get started with contributing code, documentation, and improvements to the project.

## Getting Started

### Prerequisites

Before contributing, ensure you have:

- **PHP 8.1+** with required extensions
- **Drupal 10.x or 11.x** development environment
- **Git** for version control
- **Composer** for dependency management
- **Node.js and npm** (for frontend development)

### Development Environment Setup

1. **Fork the Repository**
   ```bash
   # Fork on GitHub, then clone your fork
   git clone https://github.com/your-username/howard_paragraphs.git
   cd howard_paragraphs
   ```

2. **Set Up Local Development**
   ```bash
   # Install dependencies
   composer install
   
   # Set up pre-commit hooks
   composer run-script setup-git-hooks
   
   # Install development tools
   composer require --dev drupal/coder phpunit/phpunit
   ```

3. **Configure Development Environment**
   ```bash
   # Copy example settings
   cp .env.example .env
   
   # Configure database and settings
   # Edit .env with your local settings
   ```

## Contribution Guidelines

### Code Standards

Howard Paragraphs follows Drupal coding standards:

- **PHP**: [Drupal PHP Coding Standards](https://www.drupal.org/docs/develop/standards/php)
- **JavaScript**: [Drupal JavaScript Coding Standards](https://www.drupal.org/docs/develop/standards/javascript)
- **CSS**: [Drupal CSS Coding Standards](https://www.drupal.org/docs/develop/standards/css)
- **HTML/Twig**: [Drupal HTML/Twig Coding Standards](https://www.drupal.org/docs/develop/standards/twig)

### Code Quality Tools

We use automated tools to maintain code quality:

```bash
# PHP CodeSniffer (check standards)
./vendor/bin/phpcs --standard=Drupal modules/

# PHP CodeSniffer (fix issues)
./vendor/bin/phpcbf --standard=Drupal modules/

# PHPStan (static analysis)
./vendor/bin/phpstan analyse modules/

# Run all quality checks
composer run-script quality-check
```

### Testing Requirements

All contributions must include appropriate tests:

```bash
# Run unit tests
./vendor/bin/phpunit tests/src/Unit/

# Run functional tests
./vendor/bin/phpunit tests/src/Functional/

# Run kernel tests
./vendor/bin/phpunit tests/src/Kernel/

# Run all tests
composer run-script test
```

## Types of Contributions

### 1. Bug Fixes

When fixing bugs:

1. **Identify the Issue**: Ensure the bug is reproducible
2. **Create Issue**: File a detailed bug report if one doesn't exist
3. **Write Test**: Create a test that demonstrates the bug
4. **Fix Code**: Implement the minimal fix necessary
5. **Verify Fix**: Ensure the test passes and doesn't break existing functionality

Example bug fix workflow:

```bash
# Create feature branch
git checkout -b fix/issue-123-cards-not-displaying

# Write test that fails
# tests/src/Functional/CardsComponentTest.php

# Implement fix
# modules/hp_cards/src/Component/CardsComponent.php

# Run tests
composer run-script test

# Commit changes
git add .
git commit -m "Fix cards component display issue (#123)"
```

### 2. New Components

When creating new components:

1. **Plan Component**: Define requirements and functionality
2. **Create Module Structure**: Follow existing patterns
3. **Implement Fields**: Define necessary fields and configuration
4. **Create Templates**: Build accessible, semantic templates
5. **Add Documentation**: Include comprehensive documentation
6. **Write Tests**: Cover all functionality

Component creation checklist:

```bash
# Create component directory
mkdir modules/hp_new_component

# Required files
touch modules/hp_new_component/hp_new_component.info.yml
touch modules/hp_new_component/hp_new_component.module
touch modules/hp_new_component/README.md

# Configuration directory
mkdir -p modules/hp_new_component/config/install

# Templates directory
mkdir modules/hp_new_component/templates

# Tests directory
mkdir -p modules/hp_new_component/tests/src/Functional
```

### 3. Documentation Improvements

Documentation contributions are always welcome:

- **API Documentation**: Improve inline code documentation
- **User Guides**: Update or expand user-facing documentation
- **Developer Guides**: Add or improve developer documentation
- **Examples**: Provide more usage examples

### 4. Performance Improvements

When optimizing performance:

1. **Benchmark Current Performance**: Establish baseline metrics
2. **Identify Bottlenecks**: Use profiling tools to find issues
3. **Implement Optimization**: Make targeted improvements
4. **Measure Improvement**: Verify performance gains
5. **Update Documentation**: Document any configuration changes

## Development Workflow

### Git Workflow

We use a Git flow-inspired workflow:

```bash
# Create feature branch from main
git checkout main
git pull origin main
git checkout -b feature/new-component-name

# Make your changes
# ... development work ...

# Commit changes with descriptive messages
git add .
git commit -m "Add new component for data visualization

- Implement Chart component with multiple chart types
- Add configuration options for data sources
- Include accessibility features and ARIA labels
- Add comprehensive tests and documentation"

# Push to your fork
git push origin feature/new-component-name

# Create pull request on GitHub
```

### Commit Message Guidelines

Use clear, descriptive commit messages:

```
<type>(<scope>): <description>

[optional body]

[optional footer]
```

Types:
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

Examples:

```bash
feat(hp_charts): Add new chart component with multiple visualization types

fix(hp_cards): Resolve issue with image scaling on mobile devices

docs(api): Update service documentation with new methods

test(hp_news_feed): Add integration tests for external API calls
```

### Code Review Process

All contributions go through code review:

1. **Submit Pull Request**: Create detailed PR with description
2. **Automated Checks**: CI runs tests and quality checks
3. **Peer Review**: Maintainers and community review code
4. **Address Feedback**: Make requested changes
5. **Final Approval**: Maintainer approves and merges

## Testing Guidelines

### Test Types

#### Unit Tests
Test individual functions and methods:

```php
<?php
namespace Drupal\Tests\hp_cards\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\hp_cards\Services\CardsService;

class CardsServiceTest extends UnitTestCase {
  
  public function testCardDataProcessing() {
    $service = new CardsService();
    $input = ['title' => 'Test Card'];
    $result = $service->processCardData($input);
    
    $this->assertEquals('Test Card', $result['title']);
  }
}
```

#### Functional Tests
Test complete functionality in browser:

```php
<?php
namespace Drupal\Tests\hp_cards\Functional;

use Drupal\Tests\BrowserTestBase;

class CardsComponentTest extends BrowserTestBase {
  
  protected $defaultTheme = 'stark';
  
  protected static $modules = ['hp_cards', 'paragraphs'];
  
  public function testCardsDisplay() {
    // Create content with cards
    $node = $this->createNode([
      'type' => 'page',
      'field_content' => [
        'target_id' => $this->createCardsParagraph(),
        'target_revision_id' => 1,
      ],
    ]);
    
    // Visit page and check display
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->elementExists('css', '.paragraph--type--hp-cards');
  }
}
```

#### Kernel Tests
Test service integration:

```php
<?php
namespace Drupal\Tests\hp_cards\Kernel;

use Drupal\KernelTests\KernelTestBase;

class CardsServiceKernelTest extends KernelTestBase {
  
  protected static $modules = ['hp_cards', 'paragraphs'];
  
  public function testServiceIntegration() {
    $service = \Drupal::service('hp_cards.cards_service');
    $this->assertInstanceOf(CardsService::class, $service);
  }
}
```

### Accessibility Testing

Ensure all components meet WCAG 2.1 AA standards:

```javascript
// JavaScript accessibility tests
describe('Cards Component Accessibility', function() {
  it('should have proper ARIA labels', function() {
    cy.visit('/page-with-cards');
    cy.get('.card').should('have.attr', 'role', 'article');
    cy.get('.card-title').should('have.attr', 'id');
  });
  
  it('should be keyboard navigable', function() {
    cy.visit('/page-with-cards');
    cy.get('.card').first().focus();
    cy.get('.card').first().should('have.focus');
  });
});
```

## Documentation Standards

### Inline Documentation

Use PHPDoc for all PHP code:

```php
<?php
/**
 * Service for managing card component data.
 *
 * This service handles the processing and formatting of card data,
 * including image handling, text processing, and link generation.
 */
class CardsService {
  
  /**
   * Processes raw card data into renderable format.
   *
   * @param array $data
   *   Raw card data containing title, image, text, and link.
   * @param array $options
   *   Processing options including image styles and text filters.
   *
   * @return array
   *   Processed card data ready for rendering.
   *
   * @throws \InvalidArgumentException
   *   When required data fields are missing.
   */
  public function processCardData(array $data, array $options = []): array {
    // Implementation
  }
}
```

### README Standards

Each component should have a comprehensive README:

```markdown
# Component Name

Brief description of what the component does.

## Features

- List key features
- Include any special capabilities
- Note integration points

## Configuration

### Field Configuration
Describe available fields and their purposes.

### Display Options
Explain different display modes and settings.

## Usage Examples

Provide concrete examples of how to use the component.

## Customization

Explain how to override templates and styling.

## API

Document any public methods or hooks.
```

## Release Process

### Version Management

We follow semantic versioning (SemVer):

- **Major** (X.0.0): Breaking changes
- **Minor** (0.X.0): New features, backward compatible
- **Patch** (0.0.X): Bug fixes, backward compatible

### Release Workflow

1. **Feature Freeze**: Stop adding new features for release
2. **Testing**: Comprehensive testing of all changes
3. **Documentation**: Update all relevant documentation
4. **Changelog**: Update CHANGELOG.md with all changes
5. **Tag Release**: Create git tag with version number
6. **Package**: Create release packages
7. **Deploy**: Deploy to package repositories

### Changelog Format

```markdown
# Changelog

## [2.1.0] - 2025-01-15

### Added
- New Chart component for data visualization
- Enhanced accessibility features across all components
- Performance improvements for external content loading

### Changed
- Updated Cards component with new layout options
- Improved error handling in external content services

### Fixed
- Fixed image scaling issues in Cards component
- Resolved caching issues with News Feed component

### Deprecated
- Old Chart API (will be removed in 3.0.0)

### Removed
- Legacy Facebook Feed component

### Security
- Updated HTTP client to address security vulnerabilities
```

## Community Guidelines

### Code of Conduct

We follow the Drupal Code of Conduct. Be respectful, inclusive, and professional in all interactions.

### Communication Channels

- **GitHub Issues**: Bug reports and feature requests
- **Pull Requests**: Code contributions and reviews
- **Documentation**: Updates and improvements

### Getting Help

If you need help:

1. **Check Documentation**: Review existing documentation first
2. **Search Issues**: Look for similar issues or questions
3. **Ask Questions**: Create an issue with the "question" label
4. **Join Discussions**: Participate in pull request discussions

Thank you for contributing to Howard Paragraphs! Your contributions help make this project better for everyone.
