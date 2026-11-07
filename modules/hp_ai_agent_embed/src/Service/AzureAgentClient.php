<?php

namespace Drupal\hp_ai_agent_embed\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;

/**
 * Service for communicating with Azure AI Foundry.
 */
class AzureAgentClient {

  /**
   * Azure endpoint URL.
   *
   * @var string
   */
  protected string $endpoint;

  /**
   * Azure API key.
   *
   * @var string
   */
  protected string $apiKey;

  /**
   * HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected ClientInterface $httpClient;

  /**
   * Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected LoggerInterface $logger;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   The HTTP client.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    ClientInterface $httpClient,
    LoggerInterface $logger
  ) {
    $config = $configFactory->get('hp_ai_agent_embed.settings');
    $this->endpoint = $config->get('azure_endpoint') ?? '';
    $this->apiKey = $config->get('azure_api_key') ?? '';
    $this->httpClient = $httpClient;
    $this->logger = $logger;
  }

  /**
   * Create a conversation with the Azure agent.
   *
   * @param string $userMessage
   *   Initial user message.
   *
   * @return array
   *   Conversation data with ID.
   *
   * @throws \Exception
   */
  public function createConversation(string $userMessage): array {
    try {
      $response = $this->httpClient->post(
        $this->endpoint . '/conversations',
        [
          'headers' => [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
          ],
          'json' => [
            'items' => [
              [
                'type' => 'message',
                'role' => 'user',
                'content' => $userMessage,
              ],
            ],
          ],
        ]
      );

      $data = json_decode($response->getBody()->getContents(), TRUE);
      $this->logger->info('Conversation created: @id', ['@id' => $data['id'] ?? 'unknown']);
      return $data;
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to create conversation: @error', ['@error' => $e->getMessage()]);
      throw $e;
    }
  }

  /**
   * Create a response from the agent.
   *
   * @param string $conversationId
   *   The conversation ID.
   * @param string $agentName
   *   The agent name.
   * @param string $agentVersion
   *   The agent version.
   *
   * @return array
   *   Agent response data.
   *
   * @throws \Exception
   */
  public function createResponse(
    string $conversationId,
    string $agentName,
    string $agentVersion
  ): array {
    try {
      $response = $this->httpClient->post(
        $this->endpoint . '/responses',
        [
          'headers' => [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
          ],
          'json' => [
            'conversation' => $conversationId,
            'agent' => [
              'name' => $agentName,
              'version' => $agentVersion,
              'type' => 'agent_reference',
            ],
          ],
        ]
      );

      $data = json_decode($response->getBody()->getContents(), TRUE);
      $this->logger->info('Response generated for conversation: @id', ['@id' => $conversationId]);
      return $data;
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to generate response: @error', ['@error' => $e->getMessage()]);
      throw $e;
    }
  }

  /**
   * Stream a response from the agent.
   *
   * @param string $conversationId
   *   The conversation ID.
   * @param string $agentName
   *   The agent name.
   * @param string $agentVersion
   *   The agent version.
   *
   * @return \Generator
   *   Generator yielding response chunks.
   *
   * @throws \Exception
   */
  public function streamResponse(
    string $conversationId,
    string $agentName,
    string $agentVersion
  ): \Generator {
    try {
      $response = $this->httpClient->post(
        $this->endpoint . '/responses',
        [
          'headers' => [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
          ],
          'json' => [
            'conversation' => $conversationId,
            'agent' => [
              'name' => $agentName,
              'version' => $agentVersion,
              'type' => 'agent_reference',
            ],
            'stream' => TRUE,
          ],
          'stream' => TRUE,
        ]
      );

      $stream = $response->getBody();
      while (!$stream->eof()) {
        $chunk = $stream->read(1024);
        if ($chunk) {
          yield $chunk;
        }
      }

      $this->logger->info('Stream completed for conversation: @id', ['@id' => $conversationId]);
    }
    catch (\Exception $e) {
      $this->logger->error('Stream error: @error', ['@error' => $e->getMessage()]);
      throw $e;
    }
  }

  /**
   * Add a message to an existing conversation.
   *
   * @param string $conversationId
   *   The conversation ID.
   * @param string $userMessage
   *   The user message.
   *
   * @return array
   *   Updated conversation data.
   *
   * @throws \Exception
   */
  public function addMessage(string $conversationId, string $userMessage): array {
    try {
      $response = $this->httpClient->post(
        $this->endpoint . '/conversations/' . $conversationId . '/items',
        [
          'headers' => [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
          ],
          'json' => [
            'type' => 'message',
            'role' => 'user',
            'content' => $userMessage,
          ],
        ]
      );

      $data = json_decode($response->getBody()->getContents(), TRUE);
      return $data;
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to add message: @error', ['@error' => $e->getMessage()]);
      throw $e;
    }
  }

}
