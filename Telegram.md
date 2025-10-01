### Как создать свой чат бот в Телеграм

1. В Telegram найдите бота @BotFather
2. Напишите ему /start → /newbot.
3. Укажите имя (например, SupportBot) и username (должно заканчиваться на _bot, например @bogataya_company_bot).

**В ответ BotFather пришлёт примерно такое сообщение**

```shell
Done! Congratulations on your new bot.
You can control your bot using this token:
1234567890:AAH2AbcdEfghIJkLMnoPqrsTuvWxYz
👉 Скопируйте эту строку — это и есть TELEGRAM_TOKEN.

For a description of the Bot API, see this page: https://core.telegram.org/bots/api
https://api.telegram.org/bot<TELEGRAM_TOKEN>/getUpdates
```

4. Обязательно напишите в бот первое приветственное сообщение

5. Получите telegram chat_id

```shell
curl -X POST \
-d "url=https://test-mind.ru/telegram_webhook.php" \
https://api.telegram.org/bot<TELEGRAM_TOKEN>/getUpdated
```

6. Затем регистрируем вебхук в Telegram:**

```shell
curl -X POST \
-d "url=https://test-mind.ru/telegram_webhook.php" \
https://api.telegram.org/bot<TOKEN>/setWebhook
```

7. В .env файле проекта укажите

```dotenv
TELEGRAM_TOKEN=your_token_id
TELEGRAM_CHAT_ID=your_chat_id
```