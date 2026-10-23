/**
 * @file
 * AI Agent Client for real-time streaming conversations
 */

(function (Drupal) {
  Drupal.aiAgent = Drupal.aiAgent || {};

  /**
   * AI Agent Streaming Client
   */
  Drupal.aiAgent.Client = class {
    constructor(endpoint, options = {}) {
      this.endpoint = endpoint;
      this.conversationId = null;
      this.agentName = options.agentName || null;
      this.agentVersion = options.agentVersion || '1';
      this.systemPrompt = options.systemPrompt || null;
      this.timeout = options.timeout || 300000; // 5 minutes default
      this.onMessage = options.onMessage || (() => {});
      this.onError = options.onError || (() => {});
      this.onComplete = options.onComplete || (() => {});
      this.isConnected = false;
    }

    /**
     * Send a message to the agent
     */
    async sendMessage(message) {
      if (!this.agentName) {
        this.onError(new Error('Agent name not configured'));
        return;
      }

      const payload = {
        message: message,
        agent_name: this.agentName,
        agent_version: this.agentVersion,
        conversation_id: this.conversationId,
      };

      if (this.systemPrompt && !this.conversationId) {
        payload.system_prompt = this.systemPrompt;
      }

      try {
        this.isConnected = true;
        const response = await fetch(this.endpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(payload),
        });

        if (!response.ok) {
          const error = await response.json();
          throw new Error(error.error || 'Stream failed');
        }

        await this._handleStream(response.body.getReader());
      } catch (error) {
        this.isConnected = false;
        this.onError(error);
      } finally {
        this.isConnected = false;
      }
    }

    /**
     * Handle streaming response
     */
    async _handleStream(reader) {
      const decoder = new TextDecoder();
      let buffer = '';

      try {
        while (true) {
          const { done, value } = await reader.read();
          if (done) break;

          buffer += decoder.decode(value, { stream: true });
          const lines = buffer.split('\n');

          // Process all complete lines
          for (let i = 0; i < lines.length - 1; i++) {
            const line = lines[i].trim();
            if (line) {
              this._processStreamLine(line);
            }
          }

          // Keep incomplete line in buffer
          buffer = lines[lines.length - 1];
        }

        // Process any remaining buffer
        if (buffer.trim()) {
          this._processStreamLine(buffer.trim());
        }

        this.onComplete();
      } catch (error) {
        this.onError(error);
      }
    }

    /**
     * Process a single stream line
     */
    _processStreamLine(line) {
      try {
        const data = JSON.parse(line);

        switch (data.type) {
          case 'init':
            this.conversationId = data.conversation_id;
            this.onMessage({
              type: 'system',
              content: 'Conversation started',
            });
            break;

          case 'data':
            this.onMessage({
              type: 'response',
              content: data.content,
            });
            break;

          case 'end':
            this.onMessage({
              type: 'complete',
              content: 'Response complete',
            });
            break;

          case 'error':
            this.onError(new Error(data.content));
            break;
        }
      } catch (error) {
        console.warn('Failed to parse stream line:', line, error);
      }
    }

    /**
     * Reset conversation
     */
    reset() {
      this.conversationId = null;
    }

    /**
     * Get current conversation ID
     */
    getConversationId() {
      return this.conversationId;
    }
  };

  /**
   * UI Component for AI Agent
   */
  Drupal.aiAgent.Widget = class {
    constructor(element, options = {}) {
      this.element = element;
      this.options = options;
      this.client = null;
      this.messageHistory = [];
      this.isWaiting = false;

      this._init();
    }

    _init() {
      const settings = this._getSettings();

      // Create UI
      this._buildUI();

      // Initialize client
      this.client = new Drupal.aiAgent.Client(
        '/api/ai-agent/stream',
        {
          agentName: settings.agentName,
          agentVersion: settings.agentVersion,
          systemPrompt: settings.systemPrompt,
          onMessage: (msg) => this._handleMessage(msg),
          onError: (error) => this._handleError(error),
          onComplete: () => this._handleComplete(),
        }
      );
    }

    _getSettings() {
      return {
        agentName: this.element.dataset.agentName || this.options.agentName,
        agentVersion: this.element.dataset.agentVersion || this.options.agentVersion || '1',
        systemPrompt: this.element.dataset.systemPrompt || this.options.systemPrompt,
      };
    }

    _buildUI() {
      this.element.innerHTML = `
        <div class="ai-agent-widget">
          <div class="ai-agent-messages" id="${this.element.id}-messages"></div>
          <div class="ai-agent-input-container">
            <input
              type="text"
              class="ai-agent-input"
              id="${this.element.id}-input"
              placeholder="Type your message..."
              autocomplete="off"
            />
            <button
              type="button"
              class="ai-agent-send"
              id="${this.element.id}-send"
            >Send</button>
          </div>
        </div>
      `;

      const messagesEl = this.element.querySelector('.ai-agent-messages');
      const inputEl = this.element.querySelector('.ai-agent-input');
      const sendBtn = this.element.querySelector('.ai-agent-send');

      sendBtn.addEventListener('click', () => this._sendMessage());
      inputEl.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !this.isWaiting) {
          this._sendMessage();
        }
      });

      this.messagesEl = messagesEl;
      this.inputEl = inputEl;
      this.sendBtn = sendBtn;
    }

    _sendMessage() {
      const message = this.inputEl.value.trim();
      if (!message || this.isWaiting) return;

      this.isWaiting = true;
      this.sendBtn.disabled = true;

      this._addMessageToUI('user', message);
      this.messageHistory.push({ role: 'user', content: message });

      this.inputEl.value = '';
      this.client.sendMessage(message);
    }

    _handleMessage(msg) {
      if (msg.type === 'response') {
        this._addMessageToUI('assistant', msg.content);
      } else if (msg.type === 'system') {
        this._addSystemMessage(msg.content);
      }
    }

    _handleError(error) {
      this._addSystemMessage(`Error: ${error.message}`, 'error');
      this.isWaiting = false;
      this.sendBtn.disabled = false;
    }

    _handleComplete() {
      this._addSystemMessage('Response complete');
      this.isWaiting = false;
      this.sendBtn.disabled = false;
      this.inputEl.focus();
    }

    _addMessageToUI(role, content) {
      const msg = document.createElement('div');
      msg.className = `ai-agent-message ai-agent-${role}`;
      msg.innerHTML = this._escapeHtml(content);
      this.messagesEl.appendChild(msg);
      this.messagesEl.scrollTop = this.messagesEl.scrollHeight;
    }

    _addSystemMessage(content, type = 'info') {
      const msg = document.createElement('div');
      msg.className = `ai-agent-system ai-agent-${type}`;
      msg.innerHTML = this._escapeHtml(content);
      this.messagesEl.appendChild(msg);
      this.messagesEl.scrollTop = this.messagesEl.scrollHeight;
    }

    _escapeHtml(text) {
      const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
      };
      return text.replace(/[&<>"']/g, (m) => map[m]);
    }

    reset() {
      this.client.reset();
      this.messageHistory = [];
      this.messagesEl.innerHTML = '';
    }
  };

  /**
   * Behavior to initialize AI Agent widgets
   */
  Drupal.behaviors.aiAgentEmbed = {
    attach(context) {
      const widgets = context.querySelectorAll('.ai-agent-embed');
      widgets.forEach((widget) => {
        if (!widget.dataset.initialized) {
          new Drupal.aiAgent.Widget(widget);
          widget.dataset.initialized = 'true';
        }
      });
    },
  };
})(Drupal);
