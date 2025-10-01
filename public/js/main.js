document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.getElementById('chat-messages');
    const userInput = document.getElementById('user-input');
    const sendButton = document.getElementById('send-button');

    // Загрузка истории из localStorage
    let chatHistory = JSON.parse(localStorage.getItem('chatHistory')) || [
        { role: 'bot', content: 'Привет! Чем могу помочь?' } // Стартовое сообщение
    ];

    // Восстановление истории при загрузке страницы
    function loadHistory() {
        chatMessages.innerHTML = '';
        chatHistory.forEach(message => {
            displayMessage(message.role, message.content, false);
        });
    }

    // Отображение сообщения
    function displayMessage(role, content, saveToHistory = true) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${role}-message`;

        // Применяем Markdown-разметку
        messageDiv.innerHTML = renderMarkdown(content);

        // Подсветка синтаксиса (если подключена библиотека)
        if (typeof hljs !== 'undefined') {
            messageDiv.querySelectorAll('pre code').forEach(block => {
                hljs.highlightElement(block);
            });
        }

        chatMessages.appendChild(messageDiv);

        // Сохраняем историю
        if (saveToHistory) {
            chatHistory.push({
                role,
                content,
                timestamp: new Date().toISOString()
            });
            saveHistory();
        }

        scrollToBottom();
    }

    // Сохранение истории
    function saveHistory() {
        localStorage.setItem('chatHistory', JSON.stringify(chatHistory));
        if (chatHistory.length > 50) {
            chatHistory = chatHistory.slice(-50);
        }
    }

    // Очистка истории
    function clearHistory() {
        if (confirm('Очистить всю историю чата и оператора?')) {
            // Чистим localStorage
            localStorage.removeItem('chatHistory');
            chatHistory = [
                { role: 'bot', content: 'История очищена. Чем могу помочь?' }
            ];
            loadHistory();

            // Чистим историю оператора на сервере
            fetch('/clear_operator_history.php', { method: 'POST' })
                .then(res => res.json())
                .then(data => console.log(data))
                .catch(err => console.error('Ошибка очистки истории оператора', err));
        }
    }

    // Отправка сообщения
    async function sendMessage() {
        const message = userInput.value.trim();
        if (!message) return;

        displayMessage('user', message);
        userInput.value = '';
        showTypingIndicator();

        try {
            const response = await fetch('/index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message })
            });

            const data = await response.json();
            displayMessage('bot', data.response);
        } catch (error) {
            displayMessage('bot', '⚠️ Ошибка соединения');
        } finally {
            hideTypingIndicator();
        }
    }

    // Индикатор "печатает"
    function showTypingIndicator() {
        const indicator = document.getElementById('typing-indicator');
        indicator.style.display = 'flex';
        scrollToBottom();
    }
    function hideTypingIndicator() {
        document.getElementById('typing-indicator').style.display = 'none';
    }

    // Прокрутка вниз
    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // --- 🔥 Polling сообщений от оператора ---
    let lastOperatorMessages = [];

    function startPolling() {
        setInterval(async () => {
            if (!window.currentUserId) return;

            try {
                const res = await fetch(`/get_operator_messages.php?user_id=${window.currentUserId}`);
                const data = await res.json();

                if (data.messages && data.messages.length > 0) {
                    // Берём только новые сообщения (чтобы не дублировать)
                    const newMessages = data.messages.filter(
                        msg => !lastOperatorMessages.some(m => m.text === msg.text && m.time === msg.time)
                    );

                    newMessages.forEach(msg => {
                        displayMessage('operator', msg.text);
                    });

                    lastOperatorMessages = data.messages;
                }
            } catch (e) {
                console.error("Ошибка при получении сообщений оператора:", e);
            }
        }, 3000);
    }

    // Инициализация
    loadHistory();
    sendButton.addEventListener('click', sendMessage);
    userInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendMessage();
    });
    document.getElementById('clear-btn')?.addEventListener('click', clearHistory);

    // Получение userId → запуск polling
    async function initUser() {
        try {
            const res = await fetch('/get_user.php');
            const data = await res.json();
            window.currentUserId = data.userId;
            console.log("User ID:", window.currentUserId);
            startPolling();
        } catch (err) {
            console.error("Ошибка получения userId:", err);
        }
    }

    initUser();
});