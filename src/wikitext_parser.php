<?php

function parse_wikitext($text) {
    // 1. Escape HTML to prevent XSS
    $text = htmlspecialchars($text);

    // 2. Bold: '''text''' -> <strong>text</strong>
    $text = preg_replace("/'''(.*?)'''/", '<strong>$1</strong>', $text);

    // 3. Italic: ''text'' -> <em>text</em>
    $text = preg_replace("/''(.*?)''/", '<em>$1</em>', $text);

    // 4. Internal Links: [[Page Title]] -> <a href="view.php?title=Page+Title">Page Title</a>
    // This regex handles [[Page Title|Display Text]] as well
    $text = preg_replace_callback(
        '/\[\[([^\]|]+)(?:\|([^\]]+))?\]\]/',
        function ($matches) {
            $page = trim($matches[1]);
            $display = isset($matches[2]) ? trim($matches[2]) : $page;
            // Note: This assumes a URL structure based on title. We are using ID.
            // This rule may need to be adjusted later to link by ID if titles aren't unique.
            // For now, this is a good placeholder.
            $url = 'view.php?title=' . urlencode($page);
            return '<a href="' . $url . '">' . htmlspecialchars($display) . '</a>';
        },
        $text
    );

    // 5. External Links: [http://example.com Display Text]
    $text = preg_replace(
        '/\[(https?:\/\/[^\s\]]+)\s+([^\]]+)\]/',
        '<a href="$1" target="_blank" rel="noopener noreferrer">$2</a>',
        $text
    );

    // 6. Headings: ==Heading== -> <h2>Heading</h2>
    $text = preg_replace('/^==\s*(.*?)\s*==/m', '<h2>$1</h2>', $text);
    $text = preg_replace('/^===\s*(.*?)\s*===/m', '<h3>$1</h3>', $text);


    // 7. Horizontal Rule: ----
    $text = preg_replace('/^----/m', '<hr>', $text);

    // 8. Paragraphs: Convert double newlines to paragraphs
    $text = '<p>' . preg_replace('/\n\s*\n/', '</p><p>', $text) . '</p>';

    // Cleanup empty paragraphs
    $text = preg_replace('/<p><\/p>/', '', $text);

    return $text;
}
