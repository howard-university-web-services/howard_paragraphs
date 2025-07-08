# Howard Paragraphs Documentation

Welcome to the comprehensive documentation for the Howard Paragraphs module - a powerful suite of Drupal paragraph bundles specifically designed for Howard University projects.

## Table of Contents

- [Getting Started](getting-started.md)
- [Architecture Overview](architecture.md)
- [Installation & Configuration](installation.md)
- [Development Guide](development-guide.md)
- [Theming & Customization](theming.md)
- [External Content & Caching](external-content.md)
- [Performance Optimization](performance-optimization.md)
- [Troubleshooting](troubleshooting.md)
- [API Reference](api-reference.md)
- [Contributing](contributing.md)
- [Security Best Practices](security-best-practices.md)

## Quick Overview

The Howard Paragraphs module provides a comprehensive collection of reusable content components (paragraphs) that integrate seamlessly with the idfive Component Library. These components are designed to create rich, interactive content experiences while maintaining consistency across Howard University's digital properties.

### Key Features

- **40+ Paragraph Components**: From simple text blocks to complex data feeds
- **External Content Integration**: Automatic feeds from Howard's various digital properties
- **Responsive Design**: Mobile-first approach with the idfive Component Library
- **Caching & Performance**: Built-in cache management for external content
- **Modular Architecture**: Enable only the components you need
- **Themeable**: Easy customization through template overrides

### System Requirements

- **Drupal**: 10.x or 11.x ✅
- **PHP**: 8.1+ (8.2+ recommended for Drupal 11)
- **Required Modules**: See [Installation Guide](installation.md#dependencies)
- **Recommended**: idfive Component Library theme

## Quick Start

1. **Install the module**: `composer require howard/howard_paragraphs`
2. **Enable base module**: `drush en howard_paragraphs`
3. **Enable desired submodules**: `drush en hp_cards hp_news_feed hp_callout`
4. **Configure external feeds**: Visit `/admin/config/clear_howard_external_content`

## Support & Contributing

- **Issues**: [GitHub Issues](https://github.com/howard-university-web-services/howard_paragraphs/issues)
- **Documentation**: You're reading it!
- **Contributing**: See our [Contributing Guide](contributing.md)

---

**Version**: 11.0.3  
**Maintainer**: Dan Rogers  
**License**: GPL-2.0+
