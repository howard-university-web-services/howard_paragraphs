# Theming & Customization

This guide covers how to customize the appearance and behavior of Howard Paragraphs components through theming, template overrides, and custom CSS.

## Overview

Howard Paragraphs is designed to work seamlessly with the idfive Component Library, but provides extensive customization options for specific design requirements.

## Template System

### Template Hierarchy

Howard Paragraphs follows Drupal's template suggestion system:

```
paragraph--hp-cards--featured.html.twig     (Most specific)
paragraph--hp-cards.html.twig
paragraph--default.html.twig
paragraph.html.twig                         (Least specific)
```

### Base Templates

The module provides several base templates:

- `paragraph--default.html.twig`: Default paragraph wrapper
- `field--entity-reference-revisions.html.twig`: Field wrapper template
- Component-specific templates in each submodule

### Template Variables

All paragraph templates receive these variables:

```twig
{# Common variables available in all paragraph templates #}
{{ content }}           {# Rendered field content #}
{{ paragraph }}         {# Paragraph entity object #}
{{ view_mode }}         {# Current view mode #}
{{ logged_in }}         {# User login status #}
{{ is_admin }}          {# Admin context #}
{{ hp_show_wrapper }}   {# Howard-specific wrapper control #}
```

## Customization Methods

### Method 1: Template Overrides

Copy templates to your theme and modify as needed:

```bash
# Copy template to your theme
cp modules/contrib/howard_paragraphs/modules/hp_cards/templates/paragraph--hp-cards.html.twig themes/your_theme/templates/
```

Example customization:

```twig
{# themes/your_theme/templates/paragraph--hp-cards.html.twig #}
{% set classes = [
  'paragraph',
  'paragraph--type--' ~ paragraph.bundle|clean_class,
  'paragraph--id--' ~ paragraph.id,
  'custom-cards-wrapper'
] %}

<div{{ attributes.addClass(classes) }}>
  {% if hp_show_wrapper %}
    <div class="paragraph-wrapper">
  {% endif %}
  
  {# Custom header implementation #}
  {% if content.field_title|render %}
    <header class="custom-header">
      <h2 class="custom-title">{{ content.field_title }}</h2>
      {% if content.field_link|render %}
        <div class="custom-link">
          {{ content.field_link }}
        </div>
      {% endif %}
    </header>
  {% endif %}
  
  {# Custom cards grid #}
  <div class="custom-cards-grid">
    {{ content.field_cards }}
  </div>
  
  {% if hp_show_wrapper %}
    </div>
  {% endif %}
</div>
```

> **Note**: When customizing templates, be aware that many components include Schema.org structured data markup for SEO benefits. See the [Schema.org Implementation](schema-org-implementation.md) guide for details on maintaining or customizing structured data when modifying templates.

### Method 2: Preprocess Functions

Add custom logic through preprocessing:

```php
<?php
// themes/your_theme/your_theme.theme

/**
 * Implements hook_preprocess_paragraph().
 */
function your_theme_preprocess_paragraph(&$variables) {
  $paragraph = $variables['paragraph'];
  
  // Add custom variables for cards
  if ($paragraph->getType() === 'hp_cards') {
    $variables['custom_grid_class'] = 'custom-grid-' . $paragraph->id();
    $variables['card_count'] = count($paragraph->get('field_cards')->getValue());
    
    // Add custom library
    $variables['#attached']['library'][] = 'your_theme/custom-cards';
  }
}

/**
 * Implements hook_theme_suggestions_paragraph_alter().
 */
function your_theme_theme_suggestions_paragraph_alter(array &$suggestions, array $variables) {
  $paragraph = $variables['elements']['#paragraph'];
  $sanitized_view_mode = strtr($variables['elements']['#view_mode'], '.', '_');
  
  // Add custom suggestions
  $suggestions[] = 'paragraph__' . $paragraph->getType() . '__custom';
  $suggestions[] = 'paragraph__' . $paragraph->getType() . '__' . $sanitized_view_mode . '__custom';
}
```

### Method 3: CSS Customization

Override styles through CSS:

```scss
// themes/your_theme/css/howard-paragraphs.scss

// Custom cards styling
.paragraph--type--hp-cards {
  .cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
  }
  
  .card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    
    &:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
  }
  
  .card-image {
    height: 200px;
    overflow: hidden;
    
    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
  }
  
  .card-content {
    padding: 1.5rem;
  }
  
  .card-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #333;
  }
  
  .card-text {
    color: #666;
    line-height: 1.6;
    margin-bottom: 1rem;
  }
  
  .card-action {
    .btn {
      background: #003366;
      color: white;
      padding: 0.5rem 1rem;
      border-radius: 4px;
      text-decoration: none;
      display: inline-block;
      transition: background 0.3s ease;
      
      &:hover {
        background: #002244;
      }
    }
  }
}
```

## idfive Component Library Integration

### CSS Classes

Components use idfive Component Library classes:

```scss
// Standard ICL classes used by Howard Paragraphs
.silc-card              // Card component
.silc-grid              // Grid layout
.silc-btn               // Button styling
.silc-accordion         // Accordion component
.silc-carousel          // Carousel component
```

### JavaScript Integration

Components can use ICL JavaScript:

```javascript
// themes/your_theme/js/custom-cards.js
(function ($, Drupal) {
  'use strict';
  
  Drupal.behaviors.customCards = {
    attach: function (context, settings) {
      $('.custom-cards-grid .card', context).once('custom-cards').each(function () {
        // Custom card behavior
        $(this).on('click', function () {
          // Handle card interaction
        });
      });
    }
  };
})(jQuery, Drupal);
```

### Library Definition

```yaml
# themes/your_theme/your_theme.libraries.yml
custom-cards:
  css:
    theme:
      css/custom-cards.css: {}
  js:
    js/custom-cards.js: {}
  dependencies:
    - core/jquery
    - core/drupal
```

## Component-Specific Customization

### External Feed Components

Customize external content display:

```php
<?php
// Preprocess external feed content
function your_theme_preprocess_paragraph(&$variables) {
  $paragraph = $variables['paragraph'];
  
  if ($paragraph->getType() === 'hp_news_feed') {
    // Add custom classes based on feed type
    $feed_type = $paragraph->get('field_feed_type')->value;
    $variables['attributes']['class'][] = 'news-feed--' . $feed_type;
    
    // Modify feed display
    if (isset($variables['content']['news_items'])) {
      foreach ($variables['content']['news_items'] as $key => $item) {
        if (is_numeric($key)) {
          // Add custom date formatting
          $variables['content']['news_items'][$key]['formatted_date'] = 
            date('M j, Y', strtotime($item['date']));
        }
      }
    }
  }
}
```

### Interactive Components

Add custom JavaScript for enhanced interactions:

```javascript
// Enhanced carousel functionality
Drupal.behaviors.customCarousel = {
  attach: function (context, settings) {
    $('.paragraph--type--hp-carousel-with-modal', context).once('custom-carousel').each(function () {
      var $carousel = $(this);
      var $modal = $carousel.find('.modal');
      
      // Custom modal behavior
      $carousel.find('.carousel-item').on('click', function () {
        var imageUrl = $(this).data('full-image');
        var caption = $(this).data('caption');
        
        $modal.find('.modal-image').attr('src', imageUrl);
        $modal.find('.modal-caption').text(caption);
        $modal.show();
      });
      
      // Close modal
      $modal.find('.close').on('click', function () {
        $modal.hide();
      });
    });
  }
};
```

## Responsive Design

### Mobile-First Approach

```scss
// Mobile-first responsive design
.paragraph--type--hp-cards {
  .cards-grid {
    // Mobile: 1 column
    grid-template-columns: 1fr;
    gap: 1rem;
    
    // Tablet: 2 columns
    @media (min-width: 768px) {
      grid-template-columns: repeat(2, 1fr);
      gap: 1.5rem;
    }
    
    // Desktop: 3 columns
    @media (min-width: 1024px) {
      grid-template-columns: repeat(3, 1fr);
      gap: 2rem;
    }
    
    // Large desktop: 4 columns (if configured)
    @media (min-width: 1200px) {
      &.cards-per-row-4 {
        grid-template-columns: repeat(4, 1fr);
      }
    }
  }
}
```

### Breakpoint Variables

```scss
// themes/your_theme/css/_variables.scss
$breakpoint-mobile: 480px;
$breakpoint-tablet: 768px;
$breakpoint-desktop: 1024px;
$breakpoint-large: 1200px;
```

## Accessibility Customization

### ARIA Enhancements

```twig
{# Enhanced accessibility in templates #}
<div class="cards-grid" role="region" aria-label="Featured content">
  {% for card in content.field_cards %}
    <article class="card" role="article" aria-labelledby="card-title-{{ loop.index }}">
      {% if card.field_image|render %}
        <div class="card-image">
          {{ card.field_image }}
        </div>
      {% endif %}
      
      <div class="card-content">
        {% if card.field_title|render %}
          <h3 id="card-title-{{ loop.index }}" class="card-title">
            {{ card.field_title }}
          </h3>
        {% endif %}
        
        {% if card.field_text|render %}
          <div class="card-text">
            {{ card.field_text }}
          </div>
        {% endif %}
        
        {% if card.field_link|render %}
          <div class="card-action">
            {{ card.field_link }}
          </div>
        {% endif %}
      </div>
    </article>
  {% endfor %}
</div>
```

### Focus Management

```javascript
// Improved focus management
Drupal.behaviors.accessibleCards = {
  attach: function (context, settings) {
    $('.card', context).once('accessible-cards').each(function () {
      var $card = $(this);
      
      // Make cards keyboard accessible
      $card.attr('tabindex', '0');
      
      // Handle keyboard interaction
      $card.on('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          var link = $card.find('a')[0];
          if (link) {
            link.click();
          }
        }
      });
    });
  }
};
```

## Performance Optimization

### Lazy Loading

```twig
{# Lazy loading implementation #}
{% if card.field_image|render %}
  <div class="card-image">
    <img src="{{ card.field_image.entity.uri.value | image_style('thumbnail') }}" 
         data-src="{{ card.field_image.entity.uri.value | image_style('large') }}"
         alt="{{ card.field_image.alt }}"
         loading="lazy"
         class="lazy-image">
  </div>
{% endif %}
```

### CSS Optimization

```scss
// Optimize CSS for performance
.paragraph--type--hp-cards {
  // Use will-change for animations
  .card {
    will-change: transform;
    
    &:hover {
      transform: translateY(-5px);
    }
  }
  
  // Optimize images
  .card-image img {
    max-width: 100%;
    height: auto;
    display: block;
  }
}
```

## Testing Customizations

### Visual Testing

1. **Multiple Devices**: Test on various screen sizes
2. **Browser Compatibility**: Test across different browsers
3. **Accessibility**: Use screen readers and keyboard navigation
4. **Performance**: Check load times and animations

### Automated Testing

```javascript
// Visual regression testing
describe('Howard Paragraphs Cards', function() {
  it('should display correctly on desktop', function() {
    cy.visit('/page-with-cards');
    cy.get('.paragraph--type--hp-cards').should('be.visible');
    cy.matchImageSnapshot('cards-desktop');
  });
  
  it('should be responsive on mobile', function() {
    cy.viewport(375, 667);
    cy.visit('/page-with-cards');
    cy.get('.cards-grid').should('have.css', 'grid-template-columns', '1fr');
  });
});
```

## Troubleshooting

### Common Issues

**Templates not updating**
- Clear caches: `drush cr`
- Check template location and naming
- Verify theme is active

**CSS not applying**
- Check CSS file is included in libraries
- Verify selectors are specific enough
- Check for CSS conflicts

**JavaScript not working**
- Verify library dependencies
- Check for JavaScript errors in console
- Ensure Drupal behaviors are properly attached

### Debug Tools

```php
// Debug template suggestions
function your_theme_preprocess_paragraph(&$variables) {
  if (\Drupal::currentUser()->hasPermission('access devel information')) {
    $suggestions = \Drupal::service('theme.manager')->getActiveTheme()->getName() . '_theme_suggestions_paragraph_alter';
    dpm($suggestions);
  }
}
```

With these theming and customization techniques, you can create unique, branded experiences while maintaining the power and flexibility of Howard Paragraphs components.
