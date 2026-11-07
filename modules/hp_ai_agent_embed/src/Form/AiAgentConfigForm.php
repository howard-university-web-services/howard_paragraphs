<?php

namespace Drupal\hp_ai_agent_embed\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configuration form for Azure AI Agent settings.
 */
class AiAgentConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'hp_ai_agent_embed_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['hp_ai_agent_embed.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('hp_ai_agent_embed.settings');

    $form['#description'] = $this->t('Configure Azure AI Foundry settings for agent integration.');

    $form['azure_credentials'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Azure Credentials'),
      '#description' => $this->t('Provide your Azure AI Foundry project credentials.'),
    ];

    $form['azure_credentials']['azure_endpoint'] = [
      '#type' => 'url',
      '#title' => $this->t('Azure Foundry Endpoint'),
      '#description' => $this->t('The Azure AI Foundry project endpoint URL (e.g., https://your-project.services.ai.azure.com/api/projects/agent)'),
      '#default_value' => $config->get('azure_endpoint') ?? '',
      '#required' => TRUE,
    ];

    $form['azure_credentials']['azure_api_key'] = [
      '#type' => 'password',
      '#title' => $this->t('Azure API Key'),
      '#description' => $this->t('Your Azure AI Foundry API key. This will be stored securely.'),
      '#default_value' => $config->get('azure_api_key') ?? '',
      '#required' => TRUE,
    ];

    $form['agent_defaults'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Default Agent Settings'),
      '#description' => $this->t('Default values for agent configuration when not specified in paragraphs.'),
    ];

    $form['agent_defaults']['default_agent_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default Agent Name'),
      '#description' => $this->t('Default agent name (can be overridden per paragraph)'),
      '#default_value' => $config->get('default_agent_name') ?? '',
      '#required' => TRUE,
    ];

    $form['agent_defaults']['default_agent_version'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default Agent Version'),
      '#description' => $this->t('Default agent version number (can be overridden per paragraph)'),
      '#default_value' => $config->get('default_agent_version') ?? '1',
      '#required' => TRUE,
    ];

    $form['stream_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Streaming Settings'),
      '#description' => $this->t('Configure real-time streaming behavior.'),
    ];

    $form['stream_settings']['stream_timeout'] = [
      '#type' => 'number',
      '#title' => $this->t('Stream Timeout (seconds)'),
      '#description' => $this->t('WebSocket connection timeout in seconds. Set to 0 for no timeout.'),
      '#default_value' => $config->get('stream_timeout') ?? 300,
      '#min' => 0,
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('hp_ai_agent_embed.settings')
      ->set('azure_endpoint', $form_state->getValue('azure_endpoint'))
      ->set('azure_api_key', $form_state->getValue('azure_api_key'))
      ->set('default_agent_name', $form_state->getValue('default_agent_name'))
      ->set('default_agent_version', $form_state->getValue('default_agent_version'))
      ->set('stream_timeout', $form_state->getValue('stream_timeout'))
      ->save();

    parent::submitForm($form, $form_state);
    $this->messenger()->addMessage($this->t('AI Agent settings have been saved.'));
  }

}
