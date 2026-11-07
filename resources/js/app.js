import './bootstrap';
import './incidencia-photographs';

const chatbotToggle = document.getElementById('chatbot-toggle');
const chatbotPanel = document.getElementById('chatbot-panel');

if (chatbotToggle && chatbotPanel) {
    const chatbotClose = document.getElementById('chatbot-close');
    const chatbotForm = document.getElementById('chatbot-form');
    const chatbotInput = document.getElementById('chatbot-input');
    const chatbotMessages = document.getElementById('chatbot-messages');
    const chatbotSendButton = chatbotForm.querySelector('button[type="submit"]');
    const csrfToken = chatbotForm.querySelector('input[name="_token"]').value;
    
    // Recuperar el ID de la conversación del localStorage
    let conversationId = localStorage.getItem('tungabot_conversacion_id') 
        ? parseInt(localStorage.getItem('tungabot_conversacion_id'), 10) 
        : null;

    // Recuperar el historial visual de los mensajes
    const savedHistory = localStorage.getItem('tungabot_html_history');
    if (savedHistory) {
        chatbotMessages.innerHTML = savedHistory;
    }

    let isSending = false;

    const saveChatHistory = () => {
        localStorage.setItem('tungabot_html_history', chatbotMessages.innerHTML);
    };

    const setChatbotOpen = (isOpen) => {
        chatbotPanel.classList.toggle('is-open', isOpen);
        chatbotPanel.setAttribute('aria-hidden', String(!isOpen));
        chatbotToggle.setAttribute('aria-expanded', String(isOpen));

        // Guardar el estado de apertura en el localStorage
        localStorage.setItem('tungabot_is_open', isOpen ? 'true' : 'false');

        if (isOpen) {
            chatbotInput.focus();
            chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
        } else {
            chatbotToggle.focus();
        }
    };

    // Mantener el chatbot abierto si ya lo estaba antes de cambiar de sección
    if (localStorage.getItem('tungabot_is_open') === 'true') {
        setChatbotOpen(true);
    }

    // Limpiar el historial y datos del chat al cerrar sesión por seguridad
    const logoutForm = document.querySelector('form[action*="logout"]');
    if (logoutForm) {
        logoutForm.addEventListener('submit', () => {
            localStorage.removeItem('tungabot_conversacion_id');
            localStorage.removeItem('tungabot_html_history');
            localStorage.removeItem('tungabot_is_open');
        });
    }

    const appendMessage = (message, sender, status = null) => {
        const messageElement = document.createElement('div');
        messageElement.className = `chatbot-message chatbot-message-${sender}`;

        const senderElement = document.createElement('span');
        senderElement.className = 'chatbot-message-sender';
        senderElement.textContent = sender === 'user' ? 'Tú' : 'TungaBot';

        const textElement = document.createElement('p');
        textElement.className = 'mb-0';
        textElement.textContent = message;

        messageElement.append(senderElement, textElement);

        if (status) {
            messageElement.classList.add(`is-${status}`);
            const statusElement = document.createElement('span');
            statusElement.className = 'chatbot-message-status';
            statusElement.textContent = status === 'pending' ? 'Enviando…' : 'No se pudo guardar';
            messageElement.append(statusElement);
        }

        chatbotMessages.append(messageElement);
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
        
        saveChatHistory();
        return messageElement;
    };

    const updateMessageStatus = (messageElement, status, label) => {
        messageElement.classList.remove('is-pending', 'is-failed');

        if (status) {
            messageElement.classList.add(`is-${status}`);
        }

        const statusElement = messageElement.querySelector('.chatbot-message-status');
        if (statusElement) {
            statusElement.textContent = label;
        }
        saveChatHistory();
    };

    const appendNotice = (message, isError = false) => {
        const notice = document.createElement('div');
        notice.className = `chatbot-notice${isError ? ' chatbot-notice-error' : ''}`;
        notice.setAttribute('role', isError ? 'alert' : 'status');
        notice.textContent = message;
        chatbotMessages.append(notice);
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
        saveChatHistory();
    };

    chatbotToggle.addEventListener('click', () => setChatbotOpen(true));
    chatbotClose.addEventListener('click', () => setChatbotOpen(false));

    const syncChatbotViewport = () => {
        if (window.visualViewport) {
            const viewport = window.visualViewport;
            document.documentElement.style.setProperty('--chatbot-viewport-height', `${viewport.height}px`);
            document.documentElement.style.setProperty(
                '--chatbot-viewport-bottom',
                `${Math.max(0, window.innerHeight - (viewport.offsetTop + viewport.height))}px`,
            );
        }
    };

    syncChatbotViewport();
    window.visualViewport?.addEventListener('resize', syncChatbotViewport);
    window.visualViewport?.addEventListener('scroll', syncChatbotViewport);

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && chatbotPanel.classList.contains('is-open')) {
            event.preventDefault();
            setChatbotOpen(false);
        }
    });

    chatbotForm.addEventListener('submit', (event) => {
        event.preventDefault();
        if (isSending) {
            return;
        }

        const message = chatbotInput.value.trim();
        if (!message) {
            return;
        }

        const messageElement = appendMessage(message, 'user', 'pending');
        isSending = true;
        chatbotInput.disabled = true;
        chatbotSendButton.disabled = true;

        const payload = { contenido: message };
        if (conversationId !== null) {
            payload.conversacion_id = conversationId;
        }

        fetch(chatbotForm.dataset.endpoint, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload),
        })
            .then(async (response) => {
                const result = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const validationMessage = result.errors
                        ? Object.values(result.errors).flat()[0]
                        : null;
                    const error = new Error(validationMessage || result.message || 'No se pudo guardar el mensaje.');
                    error.status = response.status;
                    throw error;
                }

                if (!result.data?.conversacion_id || !result.data?.mensaje_id) {
                    throw new Error('El servidor no confirmó el guardado del mensaje.');
                }

                conversationId = result.data.conversacion_id;
                localStorage.setItem('tungabot_conversacion_id', conversationId);

                updateMessageStatus(messageElement, null, 'Guardado');
                chatbotInput.value = '';

                if (result.data.respuesta) {
                    appendMessage(result.data.respuesta, 'assistant');
                }
            })
            .catch((error) => {
                updateMessageStatus(messageElement, 'failed', 'No se pudo guardar');

                if (error.status === 401) {
                    appendNotice('Tu sesión venció. Inicia sesión nuevamente para continuar.', true);
                } else if (error.status === 419) {
                    appendNotice('La sesión de seguridad venció. Actualiza la página e inténtalo nuevamente.', true);
                } else if (error.status === 422) {
                    appendNotice(error.message, true);
                } else if (error.status >= 500) {
                    appendNotice('El sistema no pudo guardar el mensaje. Inténtalo nuevamente.', true);
                } else {
                    appendNotice('No se pudo conectar con el sistema. Revisa tu conexión e inténtalo nuevamente.', true);
                }
            })
            .finally(() => {
                isSending = false;
                chatbotInput.disabled = false;
                chatbotSendButton.disabled = false;

                if (chatbotPanel.classList.contains('is-open')) {
                    chatbotInput.focus();
                }
            });
    });
}