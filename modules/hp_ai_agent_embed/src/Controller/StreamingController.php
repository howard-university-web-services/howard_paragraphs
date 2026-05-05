<?php

namespace Drupal\hp_ai_agent_embed\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\hp_ai_agent_embed\Service\StreamingService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller for WebSocket streaming endpoint.
 */
class StreamingController extends ControllerBase {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * Streaming service.
   *
   * @var \Drupal\hp_ai_agent_embed\Service\StreamingService
   */
  protected StreamingService $streamingService;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\hp_ai_agent_embed\Service\StreamingService $streamingService
   *   The streaming service.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    StreamingService $streamingService
  ) {
    $this->configFactory = $configFactory;
    $this->streamingService = $streamingService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('hp_ai_agent_embed.streaming_service')
    );
  }

  /**
   * Stream agent response.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The streamed response.
   */
  public function stream(Request $request) {
    // Get JSON data from request
    $data = json_decode($request->getContent(), TRUE);

    // Validate required parameters
    if (empty($data['message']) || empty($data['agent_name'])) {
      return new JsonResponse([
        'error' => 'Missing required parameters: message and agent_name',
      ], 400);
    }

    $message = $data['message'];
    $agentName = $data['agent_name'];
    $conversationId = $data['conversation_id'] ?? NULL;
    
    $config = $this->configFactory->get('hp_ai_agent_embed.settings');
    $agentVersion = $data['agent_version'] ?? $config->get('default_agent_version') ?? '1';

    try {
      // Initialize or continue conversation
      if (empty($conversationId)) {
        $initData = $this->streamingService->initializeConversation(
          $message,
          $agentName,
          $agentVersion
        );
        $conversationId = $initData['conversation_id'];

        // Create a streamed response that sends the conversation ID first
        $response = new StreamedResponse(function () use ($conversationId, $agentName, $agentVersion) {
          echo json_encode([
            'type' => 'init',
            'conversation_id' => $conversationId,
          ]) . "\n";
          flush();

          // Stream the response
          $this->streamingService->streamResponse(
            $conversationId,
            $agentName,
            $agentVersion,
            function ($chunk) {
              echo json_encode([
                'type' => 'data',
                'content' => $chunk,
              ]) . "\n";
              flush();
            }
          );

          echo json_encode([
            'type' => 'end',
          ]) . "\n";
          flush();
        });
      }
      else {
        // Continue existing conversation
        $response = new StreamedResponse(function () use ($conversationId, $message, $agentName, $agentVersion) {
          $this->streamingService->continueConversation(
            $conversationId,
            $message,
            $agentName,
            $agentVersion,
            function ($chunk) {
              echo json_encode([
                'type' => 'data',
                'content' => $chunk,
              ]) . "\n";
              flush();
            }
          );

          echo json_encode([
            'type' => 'end',
          ]) . "\n";
          flush();
        });
      }

      // Set response headers for streaming
      $response->headers->set('Content-Type', 'application/x-ndjson; charset=utf-8');
      $response->headers->set('Cache-Control', 'no-cache');
      $response->headers->set('Connection', 'keep-alive');
      $response->headers->set('Access-Control-Allow-Origin', '*');

      return $response;
    }
    catch (\Exception $e) {
      $this->getLogger('hp_ai_agent_embed')->error('Streaming error: @error', ['@error' => $e->getMessage()]);

      return new JsonResponse([
        'error' => 'Streaming failed',
        'message' => $e->getMessage(),
      ], 500);
    }
  }

}
