<?php

namespace Drupal\hp_ai_agent_embed\Service;

use Drupal\hp_ai_agent_embed\Service\AzureAgentClient;
use Psr\Log\LoggerInterface;

/**
 * Service for managing streaming conversations with Azure agents.
 */
class StreamingService {

  /**
   * Azure agent client.
   *
   * @var \Drupal\hp_ai_agent_embed\Service\AzureAgentClient
   */
  protected AzureAgentClient $azureClient;

  /**
   * Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected LoggerInterface $logger;

  /**
   * Constructor.
   *
   * @param \Drupal\hp_ai_agent_embed\Service\AzureAgentClient $azureClient
   *   The Azure agent client.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger.
   */
  public function __construct(
    AzureAgentClient $azureClient,
    LoggerInterface $logger
  ) {
    $this->azureClient = $azureClient;
    $this->logger = $logger;
  }

  /**
   * Initialize a streaming conversation.
   *
   * @param string $initialMessage
   *   The initial user message.
   * @param string $agentName
   *   The agent name.
   * @param string $agentVersion
   *   The agent version.
   *
   * @return array
   *   Conversation initialization data with conversation ID.
   *
   * @throws \Exception
   */
  public function initializeConversation(
    string $initialMessage,
    string $agentName,
    string $agentVersion
  ): array {
    $conversation = $this->azureClient->createConversation($initialMessage);
    return [
      'conversation_id' => $conversation['id'] ?? NULL,
      'agent_name' => $agentName,
      'agent_version' => $agentVersion,
    ];
  }

  /**
   * Stream an agent response.
   *
   * @param string $conversationId
   *   The conversation ID.
   * @param string $agentName
   *   The agent name.
   * @param string $agentVersion
   *   The agent version.
   * @param callable $callback
   *   Callback to handle each chunk.
   *
   * @throws \Exception
   */
  public function streamResponse(
    string $conversationId,
    string $agentName,
    string $agentVersion,
    callable $callback
  ): void {
    try {
      foreach ($this->azureClient->streamResponse($conversationId, $agentName, $agentVersion) as $chunk) {
        $callback($chunk);
      }
    }
    catch (\Exception $e) {
      $this->logger->error('Error streaming response: @error', ['@error' => $e->getMessage()]);
      throw $e;
    }
  }

  /**
   * Continue a conversation with a new message.
   *
   * @param string $conversationId
   *   The conversation ID.
   * @param string $userMessage
   *   The new user message.
   * @param string $agentName
   *   The agent name.
   * @param string $agentVersion
   *   The agent version.
   * @param callable $callback
   *   Callback to handle each response chunk.
   *
   * @throws \Exception
   */
  public function continueConversation(
    string $conversationId,
    string $userMessage,
    string $agentName,
    string $agentVersion,
    callable $callback
  ): void {
    try {
      $this->azureClient->addMessage($conversationId, $userMessage);
      $this->streamResponse($conversationId, $agentName, $agentVersion, $callback);
    }
    catch (\Exception $e) {
      $this->logger->error('Error continuing conversation: @error', ['@error' => $e->getMessage()]);
      throw $e;
    }
  }

}
