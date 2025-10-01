### Как получить TELEGRAM_TOKEN

**В Telegram найдите бота @BotFather**

Напишите ему /start → /newbot.

Укажите имя (например, SupportBot) и username (должно заканчиваться на _bot, например @bogataya_company_bot).

В ответ BotFather пришлёт примерно такое сообщение:

Done! Congratulations on your new bot.
You can control your bot using this token:
1234567890:AAH2AbcdEfghIJkLMnoPqrsTuvWxYz
👉 Скопируйте эту строку — это и есть TELEGRAM_TOKEN.

For a description of the Bot API, see this page: https://core.telegram.org/bots/api
https://api.telegram.org/bot<TELEGRAM_TOKEN>/getUpdates


**Как через curl проверить, что сообщения идут в телеграм бот**

```shell
curl -X POST \
-H "Content-Type: application/json" \
-d '{"chat_id":"45884915","text":"Тестовое сообщение от curl"}' \
https://api.telegram.org/bot7640441378:AAHYwfWko5mRxrqYIwcE-K06KanAUkK3Efo/sendMessage
```

```shell
curl -X POST \
-H "Content-Type: application/json" \
-d '{"chat_id":510778786,"text":"Привет, это ответ от curl"}' \
https://api.telegram.org/bot7640441378:AAHYwfWko5mRxrqYIwcE-K06KanAUkK3Efo/sendMessage
```

**Затем регистрируем вебхук в Telegram:**

curl -X POST \
-d "url=https://test-mind.ru/telegram_webhook.php" \
https://api.telegram.org/bot7640441378:AAHYwfWko5mRxrqYIwcE-K06KanAUkK3Efo/getUpdated

