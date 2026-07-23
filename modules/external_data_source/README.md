# Howard Paragraphs: External Data Source

Provides an External Data Source field type, formatters, and plugin-based widget system that connects Drupal fields to external API data sources, for use across Howard University projects.

## Origin

This module is a **local copy** of the [`drupal/external_data_source`](https://www.drupal.org/project/external_data_source) contrib module, bundled directly into `howard_paragraphs` to:

- Remove the Composer dependency on the contrib package.
- Allow Howard-specific plugin implementations to live alongside the base classes.
- Ensure Drupal 10/11 compatibility without depending on upstream releases.

**Do not update this from Drupal.org.** Any upstream improvements should be cherry-picked manually and tested against existing Howard plugins.

## How It Works

Implements a `external_data_source` field type. Each data source is a plugin that extends `ExternalDataSourceBase` and returns an array of options to power Select, Checkbox, or Autocomplete widgets in Drupal forms.

Plugins are discovered from any enabled module's `src/Plugin/ExternalDataSource/` directory.

## Creating a Plugin

Extend `ExternalDataSourceBase` and annotate with `@ExternalDataSource`:

```php
namespace Drupal\my_module\Plugin\ExternalDataSource;

use Drupal\external_data_source\Plugin\ExternalDataSourceBase;

/**
 * @ExternalDataSource(
 *   id = "my_plugin",
 *   name = @Translation("My Plugin"),
 *   description = @Translation("Fetches data from My API.")
 * )
 */
class MyPlugin extends ExternalDataSourceBase {

  /**
   * {@inheritdoc}
   */
  public function getResponse() {
    // Return array of ['value' => '...', 'label' => '...'] items.
    return [];
  }

}
```

## Existing Howard Plugins

Howard-specific plugins live in the submodules that use them:

- `hp_programs_feed` — Programs school and degree classification feeds
- `hp_profiles_feed` — Profiles department feed
- `hp_profiles_feed_by_id` — Profiles department feed (by ID)

## Markup Overrides

- Field formatters can be overridden in the client theme.
- Hooks can be overridden by copying into the client `.theme` file and modifying as needed.
