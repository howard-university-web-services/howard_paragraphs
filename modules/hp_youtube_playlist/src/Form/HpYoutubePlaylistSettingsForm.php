<?php

namespace Drupal\hp_youtube_playlist\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure example settings for this site.
 */
class HpYoutubePlaylistSettingsForm extends ConfigFormBase
{

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'hp_youtube_playlist.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'hp_youtube_playlist_settings';
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
    $help_markup = "<p>You will need to create a Youtube Data API Key.</p>";
    $help_markup .= "<p>More information can be found on the <a href='https://developers.google.com/youtube/v3/getting-started'>Youtbe Data API page</a>.</p>";

    $form['help'] = [
      '#type' => 'markup',
      '#markup' => $help_markup,
    ];

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('YouTube API key'),
      '#default_value' => $config->get('api_key'),
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
      ->save();

    parent::submitForm($form, $form_state);
  }
}
