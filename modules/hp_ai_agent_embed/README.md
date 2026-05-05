# Howard Paragraphs: AI Agent Embed

A Drupal 10/11 module that provides a paragraph type for embedding Azure AI Foundry agents with real-time WebSocket streaming support.

## Features

- **Embedded AI Agents**: Add AI agents as content paragraphs
- **Real-Time Streaming**: WebSocket-based streaming for live agent responses
- **API Key Authentication**: Secure configuration via Drupal settings
- **Conversation Management**: Maintain context across multiple messages
- **Responsive UI**: Mobile-friendly chat widget with message history
- **System Prompts**: Customize agent behavior per paragraph

## Installation

### Prerequisites

- Drupal 10 or 11
- PHP 8.1+
- Azure AI Foundry project with configured agents
- API access credentials

### Setup

1. **Enable the module**:
   ```bash
   drush en hp_ai_agent_embed
   ```

2. **Install dependencies** (if not using Composer Require):
   ```bash
   composer require azure/ai-projects guzzlehttp/guzzle
   ```

3. **Configure Azure credentials**:
   - Navigate to `/admin/config/services/ai-agent`
   - Enter your Azure Foundry endpoint URL
   - Add your API key
   - Set default agent name and version (optional)
   - Adjust stream timeout as needed

## Configuration

### Admin Settings

Go to **Administration > Configuration > Web services > AI Agent Configuration** to set:

- **Azure Foundry Endpoint**: The URL to your Azure AI project (e.g., `https://your-project.services.ai.azure.com/api/projects/agent`)
- **Azure API Key**: Your authentication key (stored securely)
- **Default Agent Name**: Fallback agent if not specified per-paragraph
- **Default Agent Version**: Fallback version number
- **Stream Timeout**: WebSocket timeout in seconds (0 = no timeout)

### Permissions

Grant the **"Administer AI Agent settings"** permission to roles that should manage agent configuration.

## Usage

### Adding an AI Agent Paragraph

1. Edit a page/node with Paragraphs enabled
2. Add a new paragraph of type **"AI Agent"**
3. Configure:
   - **Agent Name**: The specific agent to use (required)
   - **Agent Version**: Version number (uses default if empty)
   - **System Prompt**: Custom instructions for the agent (optional)
4. Save

### Frontend Interaction

The agent widget provides:
- Chat input field
- Message history display
- Real-time response streaming
- Conversation persistence

## API Endpoint

The module exposes a streaming endpoint for custom integrations:

```
POST /api/ai-agent/stream
```

### Request Payload

```json
{
  "message": "User message text",
  "agent_name": "agent-name",
  "agent_version": "1",
  "conversation_id": "existing-conversation-id (optional)"
}
```

### Response Format (NDJSON)

The endpoint returns newline-delimited JSON:

```json
{"type": "init", "conversation_id": "conv-123"}
{"type": "data", "content": "response chunk"}
{"type": "data", "content": " more content"}
{"type": "end"}
```

## JavaScript API

For custom implementations, use the provided classes:

### AiAgent.Client

```javascript
const client = new Drupal.aiAgent.Client('/api/ai-agent/stream', {
  agentName: 'my-agent',
  agentVersion: '1',
  systemPrompt: 'You are a helpful assistant',
  onMessage: (msg) => console.log(msg),
  onError: (error) => console.error(error),
  onComplete: () => console.log('done'),
});

client.sendMessage('Hello!');
client.getConversationId(); // Get current conversation
client.reset(); // Start new conversation
```

### AiAgent.Widget

```javascript
new Drupal.aiAgent.Widget(element, {
  agentName: 'my-agent',
  agentVersion: '1',
  systemPrompt: 'Custom prompt',
});
```

## Styling

The widget uses these CSS classes:

- `.ai-agent-widget` - Main container
- `.ai-agent-messages` - Message display area
- `.ai-agent-message` - Individual message
- `.ai-agent-user` - User message styling
- `.ai-agent-assistant` - Agent response styling
- `.ai-agent-input-container` - Input area
- `.ai-agent-input` - Text input field
- `.ai-agent-send` - Send button

Override styles in your theme:

```css
.ai-agent-widget {
  max-height: 500px;
}

.ai-agent-user {
  background-color: #your-color;
}
```

## Architecture

### Services

- **AzureAgentClient**: Communicates with Azure AI Foundry API
- **StreamingService**: Manages conversation lifecycle and streaming

### Controllers

- **StreamingController**: Handles POST `/api/ai-agent/stream` requests

### Configuration

- **AiAgentConfigForm**: Admin settings form for credentials

## Troubleshooting

### No response from agent

1. Verify Azure credentials in `/admin/config/services/ai-agent`
2. Check endpoint URL format
3. Confirm API key has correct permissions
4. Review logs: `drush watchdog:show hp_ai_agent_embed`

### Widget not appearing

1. Ensure the paragraph is set to use the "Default" display mode
2. Check browser console for JavaScript errors
3. Verify the library is loaded: `drush theme:refresh`

### Streaming timeout

- Increase stream timeout in settings (admin page)
- Set to 0 for unlimited timeout

## Security Considerations

- API keys are stored in Drupal config and should be:
  - Protected with proper file permissions
  - Never committed to version control
  - Stored in environment variables in production
  - Regularly rotated
- Only grant admin settings permission to trusted administrators
- The streaming endpoint is accessible to anonymous users—implement rate limiting if needed

## API Reference

### Drupal Hooks

The module implements standard Drupal hooks:

- `hook_theme()` - Defines paragraph template
- `hook_entity_view_alter()` - Attaches libraries to paragraphs
- `hook_help()` - Provides module documentation
- `hook_permission()` - Defines "Administer AI Agent settings"

## Support

For issues, feature requests, or documentation:
- Check the module documentation in Drupal
- Review Azure AI Foundry API documentation
- Check module logs for detailed error messages
