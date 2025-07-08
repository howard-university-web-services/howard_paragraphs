# Security Best Practices

This document outlines the security measures implemented in the Howard Paragraphs module and provides guidelines for maintaining security.

## 🔐 Implemented Security Measures

### Error Message Sanitization

**Issue**: Raw API error messages were being exposed to end users, potentially revealing sensitive information.

**Solution**: All error messages shown to users are now sanitized:

- **Instagram Feed**: Generic "Unable to connect to Instagram API" messages instead of raw Facebook API errors
- **Twitter Feed**: Generic "Unable to retrieve Twitter posts" messages instead of raw Twitter API errors
- **External Data Sources**: Detailed errors logged but not exposed to users

**Example**:
```php
// ❌ Before (security risk)
catch (FacebookResponseException $e) {
    $result['error'] = 'Graph returned an error: ' . $e->getMessage();
}

// ✅ After (secure)
catch (FacebookResponseException $e) {
    \Drupal::logger('hp_instagram_feed')->error('Graph API error: @message', ['@message' => $e->getMessage()]);
    $result['error'] = 'Unable to connect to Instagram API. Please check your access token configuration.';
}
```

### Structured Logging

All error logging now uses structured placeholders to prevent log injection:

```php
// ✅ Secure logging
\Drupal::logger('module_name')->error('HTTP request failed for API: @message', ['@message' => $e->getMessage()]);
```

### Input Validation

- All external API data is processed through Drupal's form API
- URL parameters are validated in external data source plugins
- User inputs are sanitized before processing

### Access Control

- Administrative functions require proper permissions
- Cache clearing operations are access-controlled
- Configuration forms extend `ConfigFormBase` for automatic CSRF protection

## 🛡️ Security Guidelines

### For Developers

1. **Never expose raw exception messages to users**
   - Log detailed errors for debugging
   - Show generic error messages to users
   - Use structured logging with placeholders

2. **Validate all external data**
   - Check API responses before processing
   - Sanitize data before caching
   - Use Drupal's built-in validation functions

3. **Use secure HTTP practices**
   - Enable SSL verification for production
   - Use HTTPS endpoints when available
   - Implement proper timeout handling

4. **Follow Drupal security patterns**
   - Use dependency injection for services
   - Implement proper permission checks
   - Use Drupal's form API for user inputs

### For Site Administrators

1. **Secure API Configuration**
   - Store API keys securely (consider using password field types)
   - Use environment variables for sensitive data
   - Regularly rotate API credentials

2. **Monitor Error Logs**
   - Check logs regularly for failed API calls
   - Monitor for unusual error patterns
   - Set up log monitoring alerts

3. **Keep Dependencies Updated**
   - Update Drupal core regularly
   - Update contributed modules
   - Monitor security advisories

## 🚨 Form API Security Issues & Recommendations

### Critical Issues Found

#### 1. Sensitive Data Field Types
**Issue**: API keys, secrets, and tokens are stored using `#type => 'textfield'`, making them visible in plain text.

**Files affected**:
- `modules/hp_twitter_feed/src/Form/HpTwitterFeedSettingsForm.php`
- `modules/hp_youtube_playlist/src/Form/HpYoutubePlaylistSettingsForm.php`

**Risk**: 🔴 **HIGH** - Credentials visible to anyone with access to the configuration form

#### 2. Missing Input Validation
**Issue**: No validation for API key formats or patterns.

**Risk**: 🟡 **MEDIUM** - Invalid data could cause API failures

#### 3. No Configuration Export Protection
**Issue**: Sensitive configuration may be exported in plaintext via configuration management.

**Risk**: 🔴 **HIGH** - Credentials exposed in version control

### 🛠️ Recommended Fixes

#### Fix 1: Use Password Field Types for Secrets

**Twitter Feed Form** (`modules/hp_twitter_feed/src/Form/HpTwitterFeedSettingsForm.php`):
```php
// 🚨 CURRENT (insecure)
$form['api_secret'] = [
  '#type' => 'textfield',
  '#title' => $this->t('Consumer Secret'),
  '#default_value' => $config->get('api_secret'),
  '#required' => TRUE,
];

// ✅ RECOMMENDED (secure)
$form['api_secret'] = [
  '#type' => 'password',
  '#title' => $this->t('Consumer Secret'),
  '#description' => $this->t('Leave blank to keep existing value.'),
  '#attributes' => ['autocomplete' => 'new-password'],
  '#required' => empty($config->get('api_secret')), // Only required if no existing value
];

$form['access_secret'] = [
  '#type' => 'password',
  '#title' => $this->t('Access Token Secret'),
  '#description' => $this->t('Leave blank to keep existing value.'),
  '#attributes' => ['autocomplete' => 'new-password'],
  '#required' => empty($config->get('access_secret')),
];
```

#### Fix 2: Add Input Validation

Add to both Twitter and YouTube forms:
```php
public function validateForm(array &$form, FormStateInterface $form_state) {
  parent::validateForm($form, $form_state);
  
  // Validate API key format
  $api_key = $form_state->getValue('api_key');
  if (!empty($api_key) && !preg_match('/^[a-zA-Z0-9_-]+$/', $api_key)) {
    $form_state->setErrorByName('api_key', $this->t('API key contains invalid characters.'));
  }
  
  // For Twitter form - validate required secrets only if not already configured
  $config = $this->config(static::SETTINGS);
  if (empty($config->get('api_secret')) && empty($form_state->getValue('api_secret'))) {
    $form_state->setErrorByName('api_secret', $this->t('API Secret is required.'));
  }
}
```

#### Fix 3: Secure Form Submission

Update submitForm methods to handle password fields:
```php
public function submitForm(array &$form, FormStateInterface $form_state) {
  $config = $this->configFactory->getEditable(static::SETTINGS);
  
  // Only update secrets if new values provided
  $api_secret = $form_state->getValue('api_secret');
  if (!empty($api_secret)) {
    $config->set('api_secret', $api_secret);
  }
  
  $access_secret = $form_state->getValue('access_secret');
  if (!empty($access_secret)) {
    $config->set('access_secret', $access_secret);
  }
  
  // Always update non-sensitive values
  $config
    ->set('api_key', $form_state->getValue('api_key'))
    ->set('access_token', $form_state->getValue('access_token'))
    ->save();

  parent::submitForm($form, $form_state);
}
```

#### Fix 4: Environment Variable Support

Add environment variable fallback:
```php
public function buildForm(array $form, FormStateInterface $form_state) {
  $config = $this->config(static::SETTINGS);
  
  // Support environment variables as fallback
  $api_key = $config->get('api_key') ?: getenv('TWITTER_API_KEY');
  
  $form['environment_notice'] = [
    '#type' => 'markup',
    '#markup' => '<div class="messages messages--warning">' . 
      $this->t('For production sites, consider using environment variables instead of storing credentials in configuration.') . 
      '</div>',
    '#weight' => -10,
  ];
  
  $form['api_key'] = [
    '#type' => 'textfield',
    '#title' => $this->t('Consumer Key'),
    '#default_value' => $api_key,
    '#description' => $this->t('Can also be set via TWITTER_API_KEY environment variable.'),
    '#required' => TRUE,
  ];
  
  // ... rest of form with password fields
}
```

#### Fix 5: Configuration Schema Updates

Create/update configuration schema files:

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

## 🚩 Additional Security Recommendations

### Production Security Checklist

#### SSL/TLS Verification
**Current Issue**: Some HTTP clients disable SSL verification for development.

```php
// 🚨 INSECURE (found in codebase)
$request = $this->client->get($url, ['verify' => FALSE]);

// ✅ SECURE (recommended)
$request = $this->client->get($url, [
  'verify' => TRUE,
  'timeout' => 30,
  'connect_timeout' => 10,
]);
```

#### Configuration Management Security

**For production deployments:**

1. **Exclude sensitive configuration from exports:**
```bash
# Add to .gitignore
/config/sync/hp_twitter_feed.settings.yml
/config/sync/hp_youtube_playlist.settings.yml
```

2. **Use environment variables in settings.local.php:**
```php
// settings.local.php
$config['hp_twitter_feed.settings']['api_key'] = getenv('TWITTER_API_KEY');
$config['hp_twitter_feed.settings']['api_secret'] = getenv('TWITTER_API_SECRET');
$config['hp_youtube_playlist.settings']['api_key'] = getenv('YOUTUBE_API_KEY');
```

#### Rate Limiting Protection

Add to service classes:
```php
protected function makeApiRequest($url) {
  // Check for rate limiting
  $rate_limit_key = 'api_rate_limit_' . $this->getServiceName();
  $cache = $this->cache->get($rate_limit_key);
  
  if ($cache && $cache->data > 100) { // 100 requests per hour
    throw new \Exception('API rate limit exceeded');
  }
  
  // Make request and increment counter
  $response = $this->httpClient->get($url);
  
  $current_count = $cache ? $cache->data : 0;
  $this->cache->set($rate_limit_key, $current_count + 1, time() + 3600);
  
  return $response;
}
```

### Security Monitoring

#### Logging Security Events

```php
// Log failed authentication attempts
\Drupal::logger('security')->warning('API authentication failed for service @service', [
  '@service' => $this->getServiceName(),
  'ip' => \Drupal::request()->getClientIp(),
]);

// Log suspicious activity
\Drupal::logger('security')->alert('Unusual API usage detected', [
  'service' => $this->getServiceName(),
  'requests_per_minute' => $rate,
]);
```

#### Content Security Policy

Add CSP headers for external content:
```php
// In module's .module file
function howard_paragraphs_page_attachments_alter(array &$attachments) {
  $attachments['#attached']['http_header'][] = [
    'Content-Security-Policy',
    "default-src 'self'; img-src 'self' https://instagram.com https://twitter.com https://youtube.com; script-src 'self' 'unsafe-inline'"
  ];
}
```

## 🎯 Implementation Priority

### 🔴 Critical (Immediate Action Required)

1. **Change sensitive form fields to password type**
   - Files: `HpTwitterFeedSettingsForm.php`, `HpYoutubePlaylistSettingsForm.php`
   - Risk: Credential exposure

2. **Add form validation**
   - Prevent invalid API keys from being saved
   - Risk: System malfunction

3. **Enable SSL verification**
   - Update all HTTP client calls
   - Risk: Man-in-the-middle attacks

### 🟡 High Priority (Within 2 weeks)

1. **Add environment variable support**
   - Better production credential management
   - Risk: Configuration management issues

2. **Implement proper form submission handling**
   - Only update passwords when provided
   - Risk: Data loss/corruption

3. **Add rate limiting protection**
   - Prevent API abuse
   - Risk: Service disruption

### 🟢 Medium Priority (Next release)

1. **Enhanced logging and monitoring**
2. **Content Security Policy implementation**
3. **Configuration schema updates**
4. **Security testing automation**

## 📋 Security Testing

### Manual Testing Checklist

- [ ] Test form submission with empty password fields
- [ ] Verify credentials are not visible in form source
- [ ] Test configuration export/import without sensitive data
- [ ] Verify SSL certificates are checked on API calls
- [ ] Test error handling doesn't expose sensitive information

### Automated Testing

```php
/**
 * Tests for form security.
 */
class SecurityTest extends BrowserTestBase {
  
  public function testSensitiveFieldTypes() {
    $this->drupalLogin($this->createUser(['administer site configuration']));
    $this->drupalGet('/admin/config/services/hp-twitter-feed');
    
    // Check that secret fields are password type
    $this->assertSession()->fieldExists('api_secret');
    $this->assertSession()->elementAttributeContains('css', 'input[name="api_secret"]', 'type', 'password');
  }
  
  public function testFormValidation() {
    // Test invalid API key format
    $this->submitForm([
      'api_key' => 'invalid key with spaces',
    ], 'Save configuration');
    
    $this->assertSession()->pageTextContains('API key contains invalid characters');
  }
}
```
