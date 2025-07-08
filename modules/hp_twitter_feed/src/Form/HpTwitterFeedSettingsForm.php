<?php

namespace Drupal\hp_twitter_feed\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure example settings for this site.
 */
class HpTwitterFeedSettingsForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'hp_twitter_feed.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'hp_twitter_feed_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);
    $help_markup = "<p>In order to use the Twitter Feed Paragraphs Module, credentials from an authentic Twitter application need to be filled in here.</p>";
    $help_markup .= "<p>Accessing the Twitter APIs requires a set of <a href='https://developer.twitter.com/en/docs/basics/authentication/oauth-1-0a'>credentials</a> that you must pass with each request. To create and app, first <a href='https://developer.twitter.com/en/docs/basics/developer-portal/overview'>apply for a developer account.</a> Then you will be able to create <a href='https://developer.twitter.com/en/docs/basics/apps/overview'></a>Twitter developer apps.</a> In order to access data from Twitter with an app, credentials need to be passed to Twitter on each request. </p>";
    $help_markup .= "<p>These credentials include four pieces of data: the consumer key, the consumer secret, the auth token, and the auth token secret. To access this information, click on the 'Apps' link from the developer portal. Then, click on an app name and then the 'keys and tokens' tab from the app page.</p>";

    $form['help'] = [
      '#type' => 'markup',
      '#markup' => $help_markup,
    ];

    // Add security notice for production deployments
    $form['security_notice'] = [
      '#type' => 'markup',
      '#markup' => '<div class="messages messages--warning">' . 
        $this->t('For production sites, consider using environment variables (TWITTER_API_KEY, TWITTER_API_SECRET, etc.) instead of storing credentials in configuration.') . 
        '</div>',
      '#weight' => -10,
    ];

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Consumer key'),
      '#default_value' => $config->get('api_key'),
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

    return parent::buildForm($form, $form_state);
  }

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

}
