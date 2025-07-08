<?php

namespace Drupal\hp_youtube_playlist\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure example settings for this site.
 */
class HpYoutubePlaylistSettingsForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'hp_youtube_playlist.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'hp_youtube_playlist_settings';
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
    $help_markup = "<p>You will need to create a Youtube Data API Key.</p>";
    $help_markup .= "<p>More information can be found on the <a href='https://developers.google.com/youtube/v3/getting-started'>Youtbe Data API page</a>.</p>";
    $help_markup .= "<p>If no API key is added, then no playlist will appear. Please contact Howard DSWS if you need further assistance.</p>";

    $form['help'] = [
      '#type' => 'markup',
      '#markup' => $help_markup,
    ];

    // Add security notice for production deployments.
    $form['security_notice'] = [
      '#type' => 'markup',
      '#markup' => '<div class="messages messages--warning">' .
      $this->t('For production sites, consider using the YOUTUBE_API_KEY environment variable instead of storing credentials in configuration.') .
      '</div>',
      '#weight' => -10,
    ];

    $form['api_key'] = [
      '#type' => 'password',
      '#title' => $this->t('YouTube API key'),
      '#description' => $this->t('Leave blank to keep existing value. Can also be set via YOUTUBE_API_KEY environment variable.'),
      '#attributes' => ['autocomplete' => 'new-password'],
      '#required' => empty($config->get('api_key')),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // Validate API key format (YouTube API keys are typically 35-45 characters)
    $api_key = $form_state->getValue('api_key');
    if (!empty($api_key) && !preg_match('/^[a-zA-Z0-9_-]{35,45}$/', $api_key)) {
      $form_state->setErrorByName('api_key', $this->t('YouTube API key format is invalid. It should be 35-45 characters long and contain only letters, numbers, underscores, and hyphens.'));
    }

    // Validate required API key only if not already configured.
    $config = $this->config(static::SETTINGS);
    if (empty($config->get('api_key')) && empty($form_state->getValue('api_key'))) {
      $form_state->setErrorByName('api_key', $this->t('YouTube API key is required.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable(static::SETTINGS);

    // Only update API key if new value provided.
    $api_key = $form_state->getValue('api_key');
    if (!empty($api_key)) {
      $config->set('api_key', $api_key);
    }

    $config->save();

    parent::submitForm($form, $form_state);
  }

}
