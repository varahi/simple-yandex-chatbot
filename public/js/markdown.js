/**
 * Преобразует Markdown в HTML
 * @param {string} markdownText - Текст в Markdown
 * @returns {string} HTML
 */
function renderMarkdown(markdownText) {
    // Экранирование HTML-тегов для безопасности
    let html = markdownText
        // .replace(/&/g, "&amp;")
        // .replace(/</g, "&lt;")
        // .replace(/>/g, "&gt;");

    // Преобразование Markdown в HTML
    return html
        // Заголовки
        .replace(/^# (.*$)/gm, '<h1>$1</h1>')
        .replace(/^## (.*$)/gm, '<h2>$1</h2>')
        .replace(/^### (.*$)/gm, '<h3>$1</h3>')

        // Жирный и курсив
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        .replace(/_([^_]+)_/g, '<em>$1</em>')
        .replace(/~~(.*?)~~/g, '<del>$1</del>')

        // Списки
        .replace(/^\s*\*\s(.*$)/gm, '<li>$1</li>')
        .replace(/^\s*-\s(.*$)/gm, '<li>$1</li>')
        .replace(/(<li>.*<\/li>)/gs, '<ul>$1</ul>')

        // Код
        .replace(/`([^`]+)`/g, '<code>$1</code>')
        .replace(/```(\w+)?\n([\s\S]*?)\n```/g, '<pre><code class="$1">$2</code></pre>')

        // Ссылки и изображения
        .replace(/!\[(.*?)\]\((.*?)\)/g, '<img src="$2" alt="$1">')
        .replace(/\[(.*?)\]\((.*?)\)/g, '<a href="$2" target="_blank" rel="noopener">$1</a>')

        // Переносы строк и абзацы
        .replace(/\n\n/g, '</p><p>')
        .replace(/\n/g, '<br>')
        .replace(/<p>(.*?)<\/p>/gs, function(match, p1) {
            return p1.trim().startsWith('<') ? match : `<p>${p1}</p>`;
        });
}