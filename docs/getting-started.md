# Getting Started with Howard Paragraphs

This guide will help you get up and running with the Howard Paragraphs module quickly and efficiently.

## What is Howard Paragraphs?

Howard Paragraphs is a comprehensive suite of Drupal paragraph bundles designed specifically for Howard University projects. It provides a collection of reusable content components that integrate with the idfive Component Library to create consistent, engaging web experiences.

## Prerequisites

Before installing Howard Paragraphs, ensure you have:

- **Drupal 10.x or 11.x** installed and configured
- **PHP 8.1+**
- **Composer** for package management
- **Administrative access** to your Drupal site
- **Basic understanding** of Drupal paragraphs and content types

## Core Concepts

### Paragraph Bundles
Each component in Howard Paragraphs is implemented as a paragraph bundle. These are reusable content blocks that can be added to any entity that supports paragraph fields.

### Submodules
The module follows a modular approach - each component is packaged as a separate submodule. This allows you to:
- Enable only the components you need
- Reduce system overhead
- Maintain cleaner codebases

### idfive Component Library Integration
All components are designed to work seamlessly with the idfive Component Library, providing:
- Consistent styling
- Responsive design
- Accessibility features
- Modern UI patterns

## First Steps

### 1. Installation
```bash
composer require howard/howard_paragraphs
drush en howard_paragraphs
```

### 2. Enable Core Components
Start with these commonly used components:
```bash
drush en hp_cards hp_button_link hp_callout hp_media
```

### 3. Add Paragraph Field
Add a paragraph field to your content type:
1. Go to **Structure > Content Types**
2. Select your content type
3. Click **Manage Fields**
4. Add a new field of type **Entity reference revisions**
5. Set the reference type to **Paragraphs**

### 4. Configure Display
Configure how paragraphs display:
1. Go to **Manage Display** for your content type
2. Set the paragraph field format to **Rendered entity**
3. Configure view mode as needed

## Basic Usage Example

Here's how to use a simple card component:

1. **Create/Edit Content**: Go to your content creation page
2. **Add Paragraph**: Click "Add Cards" in the paragraph field
3. **Configure**: Fill in the card fields (title, image, text, link)
4. **Save**: The card will render with idfive Component Library styling

## Common Patterns

### Content Creation Workflow
1. Plan your page layout
2. Choose appropriate paragraph components
3. Create content using the paragraph interface
4. Preview and adjust as needed
5. Publish when ready

### Component Selection Guide
- **Text Content**: Use `hp_html`
- **Call-to-Actions**: Use `hp_button_link` or `hp_callout`
- **Media**: Use `hp_media`, `hp_carousel_with_caption`, or `hp_video_slideshow`
- **Data Display**: Use `hp_cards`, `hp_table`, or `hp_data_point`
- **External Feeds**: Use `hp_news_feed`, `hp_alumni_feed`, etc.

## Next Steps

Once you're comfortable with the basics:

1. **Learn Theming**: Read the [Theming Guide](theming.md)
2. **Configure External Feeds**: Set up [External Content](external-content.md)
3. **Customize**: Follow the [Development Guide](development-guide.md)

## Quick Reference

### Useful Commands
```bash
# List available submodules
drush pml | grep hp_

# Clear external content cache
drush cache:rebuild

# Export configuration
drush config:export
```

### Important URLs
- **Clear External Content**: `/admin/config/clear_howard_external_content`
- **Paragraph Types**: `/admin/structure/paragraphs_type`
- **Module Status**: `/admin/modules`

## Getting Help

- **Documentation**: Continue reading these docs
- **Issues**: Check [GitHub Issues](https://github.com/howard-university-web-services/howard_paragraphs/issues)
- **Community**: Reach out to the Howard University Web Services team

Ready to dive deeper? Check out the [Architecture Overview](architecture.md) to understand how everything fits together.
