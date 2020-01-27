<?php

namespace Drupal\hp_twitter_feed\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure example settings for this site.
 */
class HpTwitterFeedSettingsForm extends ConfigFormBase
{

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'hp_twitter_feed.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'hp_twitter_feed_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames()
  {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $config = $this->config(static::SETTINGS);
    $help_markup = "<p>In order to use the Twitter Feed Paragraphs Module, credentials from an authentic Twitter application need to be filled in here.</p>";
    $help_markup .= "<p>Accessing the Twitter APIs requires a set of <a href='https://developer.twitter.com/en/docs/basics/authentication/oauth-1-0a'>credentials</a> that you must pass with each request. To create and app, first <a href='https://developer.twitter.com/en/docs/basics/developer-portal/overview'>apply for a developer account.</a> Then you will be able to create <a href='https://developer.twitter.com/en/docs/basics/apps/overview'></a>Twitter developer apps.</a> In order to access data from Twitter with an app, credentials need to be passed to Twitter on each request. </p>";
    $help_markup .= "<p>These credentials include four pieces of data: the consumer key, the consumer secret, the auth token, and the auth token secret. To access this information, click on the 'Apps' link from the developer portal. Then, click on an app name and then the 'keys and tokens' tab from the app page.</p>";

    $form['help'] = [
      '#type' => 'markup',
      '#markup' => $help_markup,
    ];

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

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    // Retrieve the configuration.
    $this->configFactory->getEditable(static::SETTINGS)
      // Set the submitted configuration setting.
      ->set('api_key', $form_state->getValue('api_key'))
      ->set('api_secret', $form_state->getValue('api_secret'))
      ->set('access_token', $form_state->getValue('access_token'))
      ->set('access_secret', $form_state->getValue('access_secret'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
