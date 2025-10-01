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
    // function displayMessage(role, content, saveToHistory = true) {
    //     const messageDiv = document.createElement('div');
    //     messageDiv.className = `message ${role}-message`;
    //     messageDiv.textContent = content;
    //     chatMessages.appendChild(messageDiv);
    //
    //     if (saveToHistory) {
    //         chatHistory.push({ role, content });
    //         saveHistory();
    //     }
    //
    //     scrollToBottom();
    // }

    function displayMessage(role, content, saveToHistory = true) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${role}-message`;

        // Применяем Markdown-разметку
        messageDiv.innerHTML = renderMarkdown(content);
        //messageDiv.innerHTML = renderContent(content);

        // Подсветка синтаксиса (если подключена библиотека)
        if (typeof hljs !== 'undefined') {
            messageDiv.querySelectorAll('pre code').forEach(block => {
                hljs.highlightElement(block);
            });
        }

        chatMessages.appendChild(messageDiv);

        // Сохраняем оригинальный текст (без HTML) в историю
        if (saveToHistory) {
            chatHistory.push({
                role,
                content, // Сохраняем оригинальный Markdown
                timestamp: new Date().toISOString()
            });
            saveHistory();
        }

        scrollToBottom();
    }

    function renderContent(content) {
        // Проверяем, если это уже HTML
        const hasHtmlTags = /<[a-z][\s\S]*>/i.test(content);

        if (hasHtmlTags) {
            return content; // Возвращаем HTML как есть
        } else {
            return renderMarkdown(content); // Преобразуем Markdown
        }
    }

    // Сохранение истории в localStorage
    function saveHistory() {
        localStorage.setItem('chatHistory', JSON.stringify(chatHistory));

        // Ограничиваем историю (последние 50 сообщений)
        if (chatHistory.length > 50) {
            chatHistory = chatHistory.slice(-50);
        }
    }

    // Очистка истории
    function clearHistory() {
        if (confirm('Очистить всю историю чата?')) {
            localStorage.removeItem('chatHistory');
            chatHistory = [
                { role: 'bot', content: 'История очищена. Чем могу помочь?' }
            ];
            loadHistory();
        }
    }

    //Отправка сообщения
    async function sendMessage() {
        const message = userInput.value.trim();
        if (!message) return;

        displayMessage('user', message);
        userInput.value = '';

        // Показать индикатор
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
            // Скрыть индикатор после ответа
            hideTypingIndicator();
        }
    }

    // Функции для управления индикатором
    function showTypingIndicator() {
        const indicator = document.getElementById('typing-indicator');
        indicator.style.display = 'flex';
        scrollToBottom();
    }

    function hideTypingIndicator() {
        const indicator = document.getElementById('typing-indicator');
        indicator.style.display = 'none';
    }

    // Прокрутка вниз
    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Инициализация
    loadHistory();

    // Обработчики событий
    sendButton.addEventListener('click', sendMessage);
    userInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendMessage();
    });

    // Кнопка очистки (добавьте в HTML)
    document.getElementById('clear-btn')?.addEventListener('click', clearHistory);
});

async function initUser() {
    try {
        const res = await fetch('/get_user.php');
        const data = await res.json();
        window.currentUserId = data.userId;
        console.log("User ID:", window.currentUserId);

        // после этого можно запускать polling сообщений от оператора
        startPolling();
    } catch (err) {
        console.error("Ошибка получения userId:", err);
    }
}

function startPolling() {
    setInterval(() => {
        fetch(`/get_operator_messages.php?user_id=${window.currentUserId}`)
            .then(res => res.json())
            .then(data => {
                if (data.messages) {
                    renderMessages(data.messages); // твоя функция рендера
                }
            });
    }, 3000);
}

// вызов при старте
initUser();