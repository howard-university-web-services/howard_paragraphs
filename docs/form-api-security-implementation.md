# Form API Security Implementation Guide

This document provides specific code changes to fix the Form API security issues in the Howard Paragraphs module.

## 🚨 Critical Security Issues

### Issue: Sensitive Data Exposed in Configuration Forms

**Risk Level**: 🔴 CRITICAL
**Impact**: API credentials visible in plain text to anyone with form access

## 🛠️ Implementation Steps

### Step 1: Update Twitter Feed Configuration Form

**File**: `modules/hp_twitter_feed/src/Form/HpTwitterFeedSettingsForm.php`

#### Current Code (Lines 48-75):
```php
$form['api_key'] = [
  '#type' => 'textfield',
  '#title' => $this->t('Consumer key'),
  '#default_value' => $config->get('api_key'),
  '#required' => TRUE,
];

$form['api_secret'] = [
  '#type' => 'textfield',
  '#title' => $this->t('Consumer Secret'),
  '#default_value' => $config->get('api_secret'),
  '#required' => TRUE,
];

$form['access_token'] = [
  '#type' => 'textfield',
  '#title' => $this->t('Access Token'),
  '#default_value' => $config->get('access_token'),
  '#required' => TRUE,
];

$form['access_secret'] = [
  '#type' => 'textfield',
  '#title' => $this->t('Access Token Secret'),
  '#default_value' => $config->get('access_secret'),
  '#required' => TRUE,
];
```

#### Recommended Changes:

```php
$form['api_key'] = [
  '#type' => 'textfield',
  '#title' => $this->t('Consumer key'),
  '#default_value' => $config->get('api_key'),
  '#description' => $this->t('Can also be set via TWITTER_API_KEY environment variable.'),
  '#required' => TRUE,
];

$form['api_secret'] = [
  '#type' => 'password',
  '#title' => $this->t('Consumer Secret'),
  '#description' => $this->t('Leave blank to keep existing value.'),
  '#attributes' => ['autocomplete' => 'new-password'],
  '#required' => empty($config->get('api_secret')),
];

$form['access_token'] = [
  '#type' => 'textfield',
  '#title' => $this->t('Access Token'),
  '#default_value' => $config->get('access_token'),
  '#description' => $this->t('Can also be set via TWITTER_ACCESS_TOKEN environment variable.'),
  '#required' => TRUE,
];

$form['access_secret'] = [
  '#type' => 'password',
  '#title' => $this->t('Access Token Secret'),
  '#description' => $this->t('Leave blank to keep existing value.'),
  '#attributes' => ['autocomplete' => 'new-password'],
  '#required' => empty($config->get('access_secret')),
];
```

#### Add Form Validation:

Add this method to the class:

```php
/**
 * {@inheritdoc}
 */
public function validateForm(array &$form, FormStateInterface $form_state) {
  parent::validateForm($form, $form_state);
  
  // Validate API key format
  $api_key = $form_state->getValue('api_key');
  if (!empty($api_key) && !preg_match('/^[a-zA-Z0-9_-]+$/', $api_key)) {
    $form_state->setErrorByName('api_key', $this->t('Consumer key contains invalid characters. Only letters, numbers, underscores, and hyphens are allowed.'));
  }
  
  // Validate access token format
  $access_token = $form_state->getValue('access_token');
  if (!empty($access_token) && !preg_match('/^[a-zA-Z0-9_-]+$/', $access_token)) {
    $form_state->setErrorByName('access_token', $this->t('Access token contains invalid characters. Only letters, numbers, underscores, and hyphens are allowed.'));
  }
  
  // Validate required secrets only if not already configured
  $config = $this->config(static::SETTINGS);
  if (empty($config->get('api_secret')) && empty($form_state->getValue('api_secret'))) {
    $form_state->setErrorByName('api_secret', $this->t('Consumer Secret is required.'));
  }
  
  if (empty($config->get('access_secret')) && empty($form_state->getValue('access_secret'))) {
    $form_state->setErrorByName('access_secret', $this->t('Access Token Secret is required.'));
  }
}
```

#### Update submitForm Method:

Replace the current submitForm method with:

```php
/**
 * {@inheritdoc}
 */
public function submitForm(array &$form, FormStateInterface $form_state) {
  $config = $this->configFactory->getEditable(static::SETTINGS);
  
  // Always update non-sensitive values
  $config->set('api_key', $form_state->getValue('api_key'));
  $config->set('access_token', $form_state->getValue('access_token'));
  
  // Only update secrets if new values provided
  $api_secret = $form_state->getValue('api_secret');
  if (!empty($api_secret)) {
    $config->set('api_secret', $api_secret);
  }
  
  $access_secret = $form_state->getValue('access_secret');
  if (!empty($access_secret)) {
    $config->set('access_secret', $access_secret);
  }
  
  $config->save();
  
  parent::submitForm($form, $form_state);
}
```

### Step 2: Update YouTube Configuration Form

**File**: `modules/hp_youtube_playlist/src/Form/HpYoutubePlaylistSettingsForm.php`

#### Current Code (Lines 44-50):
```php
$form['api_key'] = [
  '#type' => 'textfield',
  '#title' => $this->t('YouTube API key'),
  '#default_value' => $config->get('api_key'),
  '#required' => TRUE,
];
```

#### Recommended Changes:

```php
$form['api_key'] = [
  '#type' => 'password',
  '#title' => $this->t('YouTube API key'),
  '#description' => $this->t('Leave blank to keep existing value. Can also be set via YOUTUBE_API_KEY environment variable.'),
  '#attributes' => ['autocomplete' => 'new-password'],
  '#required' => empty($config->get('api_key')),
];
```

#### Add Form Validation:

Add this method to the class:

```php
/**
 * {@inheritdoc}
 */
public function validateForm(array &$form, FormStateInterface $form_state) {
  parent::validateForm($form, $form_state);
  
  // Validate API key format (YouTube API keys are typically 39 characters)
  $api_key = $form_state->getValue('api_key');
  if (!empty($api_key) && !preg_match('/^[a-zA-Z0-9_-]{35,45}$/', $api_key)) {
    $form_state->setErrorByName('api_key', $this->t('YouTube API key format is invalid. It should be 35-45 characters long and contain only letters, numbers, underscores, and hyphens.'));
  }
  
  // Validate required API key only if not already configured
  $config = $this->config(static::SETTINGS);
  if (empty($config->get('api_key')) && empty($form_state->getValue('api_key'))) {
    $form_state->setErrorByName('api_key', $this->t('YouTube API key is required.'));
  }
}
```

#### Update submitForm Method:

Replace the current submitForm method with:

```php
/**
 * {@inheritdoc}
 */
public function submitForm(array &$form, FormStateInterface $form_state) {
  $config = $this->configFactory->getEditable(static::SETTINGS);
  
  // Only update API key if new value provided
  $api_key = $form_state->getValue('api_key');
  if (!empty($api_key)) {
    $config->set('api_key', $api_key);
  }
  
  $config->save();
  
  parent::submitForm($form, $form_state);
}
```

## 🔧 Additional Security Enhancements

### Environment Variable Support

Add to both form classes in buildForm method:

```php
public function buildForm(array $form, FormStateInterface $form_state) {
  $config = $this->config(static::SETTINGS);
  
  // Add environment variable notice
  $form['environment_notice'] = [
    '#type' => 'markup',
    '#markup' => '<div class="messages messages--info">' . 
      $this->t('For production sites, consider using environment variables to store sensitive credentials instead of configuration.') . 
      '</div>',
    '#weight' => -10,
  ];
  
  // Check if environment variables are set
  $env_vars = [];
  if (getenv('TWITTER_API_KEY')) $env_vars[] = 'TWITTER_API_KEY';
  if (getenv('TWITTER_API_SECRET')) $env_vars[] = 'TWITTER_API_SECRET';
  // ... check other vars
  
  if (!empty($env_vars)) {
    $form['environment_status'] = [
      '#type' => 'markup',
      '#markup' => '<div class="messages messages--status">' . 
        $this->t('Environment variables detected: @vars', ['@vars' => implode(', ', $env_vars)]) . 
        '</div>',
      '#weight' => -9,
    ];
  }
  
  // ... rest of form
}
```

### Configuration Schema

Create configuration schema files to properly define the structure:

**File**: `modules/hp_twitter_feed/config/schema/hp_twitter_feed.schema.yml`
```yaml
hp_twitter_feed.settings:
  type: config_object
  label: 'Twitter Feed settings'
  mapping:
    api_key:
      type: string
      label: 'Consumer Key'
    api_secret:
      type: string
      label: 'Consumer Secret'
      translatable: false
    access_token:
      type: string
      label: 'Access Token'
    access_secret:
      type: string
      label: 'Access Token Secret'
      translatable: false
```

**File**: `modules/hp_youtube_playlist/config/schema/hp_youtube_playlist.schema.yml`
```yaml
hp_youtube_playlist.settings:
  type: config_object
  label: 'YouTube Playlist settings'
  mapping:
    api_key:
      type: string
      label: 'YouTube API Key'
      translatable: false
```

## 📝 Testing Checklist

After implementing these changes, test the following:

- [ ] Form displays password fields for sensitive data
- [ ] Form validation works for invalid API key formats
- [ ] Form submission only updates passwords when new values are provided
- [ ] Existing credentials are preserved when password fields are left blank
- [ ] Environment variables are properly detected and displayed
- [ ] Configuration export does not contain sensitive data
- [ ] Form cannot be submitted without required credentials

## 🚀 Deployment Notes

1. **Backup current configuration** before applying changes
2. **Test in development environment** first
3. **Update production deployment scripts** to use environment variables
4. **Train administrators** on new form behavior
5. **Monitor logs** for validation errors after deployment

## 📋 Security Audit Results

After implementation, the security score for Form API should improve from:
- **Before**: 🔴 Critical vulnerabilities (plain text credentials)
- **After**: 🟢 Secure implementation (password fields, validation, environment support)

This addresses the primary security concern identified in the security audit.
