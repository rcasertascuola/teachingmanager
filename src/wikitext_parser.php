<?php

function parse_wikitext($text) {
    // The order of these operations is important.

    // 0. Prevent parsing within <pre> or other special blocks if we add them later.
    // For now, we don't have such blocks.

    // 1. Escape HTML to prevent XSS, but do it selectively.
    // We will be generating HTML, so we can't escape everything upfront.
    $text = htmlspecialchars($text, ENT_NOQUOTES);

    // Block-level elements
    // The regexes use the /m (multiline) flag to match ^ at the beginning of each line.

    // 8. Headings: =H1=, ==H2==, ===H3===
    $text = preg_replace('/^=\s*(.*?)\s*=/m', '<h1>$1</h1>', $text);
    $text = preg_replace('/^==\s*(.*?)\s*==/m', '<h2>$1</h2>', $text);
    $text = preg_replace('/^===\s*(.*?)\s*===/m', '<h3>$1</h3>', $text);

    // 9. Horizontal Rule: ----
    $text = preg_replace('/^----/m', '<hr>', $text);

    // 10. Lists (Bulleted and Numbered)
    // Process bulleted lists
    $text = preg_replace_callback('/((?:^\*\s.*(?:\n|$))+)/m', function ($matches) {
        $items = $matches[1];
        $items = preg_replace('/^\*\s(.*)/m', '<li>$1</li>', $items);
        return "<ul>\n" . $items . "</ul>\n";
    }, $text);
    // Process numbered lists
    $text = preg_replace_callback('/((?:^#\s.*(?:\n|$))+)/m', function ($matches) {
        $items = $matches[1];
        $items = preg_replace('/^#\s(.*)/m', '<li>$1</li>', $items);
        return "<ol>\n" . $items . "</ol>\n";
    }, $text);


    // 11. Tables
    $text = preg_replace_callback('/((?:^\|.*\|(?:\n|$))+)/m', function ($matches) {
        $table_text = $matches[1];
        $rows = explode("\n", trim($table_text));
        $html = "<table class=\"table table-bordered\">\n";
        $is_header = true;
        foreach ($rows as $row) {
            if (empty(trim($row))) continue;
            // Check for separator line
            if (preg_match('/^\|-.*-\|$/', $row)) {
                $is_header = false;
                continue;
            }
            $tag = $is_header ? 'th' : 'td';
            $html .= "<tr>\n";
            $cells = explode('|', trim($row, '|'));
            foreach ($cells as $cell) {
                $html .= "<$tag>" . trim($cell) . "</$tag>\n";
            }
            $html .= "</tr>\n";
        }
        $html .= "</table>\n";
        return $html;
    }, $text);


    // Inline elements

    // 2. Bold and Italic
    $text = preg_replace("/'''(.*?)'''/", '<strong>$1</strong>', $text);
    $text = preg_replace("/''(.*?)''/", '<em>$1</em>', $text);

    // 3. Images: [[Image:url|caption]]
    $text = preg_replace_callback(
        '/\[\[Image:(https?:\/\/[^\s|\]]+)\|([^\]]+)\]\]/',
        function ($matches) {
            $url = htmlspecialchars_decode($matches[1]);
            $caption = trim($matches[2]);
            return '<figure class="figure">
                      <img src="' . $url . '" class="img-fluid" alt="' . htmlspecialchars($caption) . '">
                      <figcaption class="figure-caption">' . htmlspecialchars($caption) . '</figcaption>
                    </figure>';
        },
        $text
    );

    // 4. Videos: [[Video:youtube_url]]
    $text = preg_replace_callback(
        '/\[\[Video:(https?:\/\/(?:www\.youtube\.com\/watch\?v=|youtu\.be\/)[^\s\]]+)\]\]/',
        function ($matches) {
            $url = htmlspecialchars_decode($matches[1]);
            // Convert youtube.com/watch?v=ID to youtu.be/ID to embeddable URL
            if (strpos($url, 'watch?v=') !== false) {
                parse_str(parse_url($url, PHP_URL_QUERY), $query_params);
                $video_id = $query_params['v'];
            } else {
                $video_id = basename($url);
            }
            $embed_url = 'https://www.youtube.com/embed/' . $video_id;
            return '<div class="ratio ratio-16x9">
                      <iframe src="' . $embed_url . '" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>';
        },
        $text
    );

    // 5. Colored Text: {{color:red|text}}
    $text = preg_replace_callback(
        '/\{\{color:([a-zA-Z]+|#[0-9a-fA-F]{3,6})\|(.*?)\}\}/',
        function ($matches) {
            $color = htmlspecialchars_decode($matches[1]);
            $content = $matches[2];
            // Basic validation for color
            if (!preg_match('/^[a-zA-Z]+$/', $color) && !preg_match('/^#[0-9a-fA-F]{3,6}$/', $color)) {
                return $matches[0]; // Return original text if color is not valid
            }
            return '<span style="color:' . $color . ';">' . $content . '</span>';
        },
        $text
    );


    // 6. Internal Links: [[Page Title|Display Text]]
    $text = preg_replace_callback(
        '/\[\[([^\]|:\n]+)(?:\|([^\]\n]+))?\]\]/', // Avoid matching Image/Video tags
        function ($matches) {
            // Check if it's not an Image or Video link already processed
            if (strpos($matches[1], 'Image:') === 0 || strpos($matches[1], 'Video:') === 0) {
                 return $matches[0];
            }
            $page = trim($matches[1]);
            $display = isset($matches[2]) ? trim($matches[2]) : $page;
            $url = 'view.php?title=' . urlencode($page);
            return '<a href="' . $url . '">' . htmlspecialchars($display) . '</a>';
        },
        $text
    );

    // 7. External Links: [http://example.com Display Text]
    $text = preg_replace(
        '/\[(https?:\/\/[^\s\]]+)\s+([^\]]+)\]/',
        '<a href="$1" target="_blank" rel="noopener noreferrer">$2</a>',
        $text
    );


    // 12. Paragraphs: Convert double newlines to paragraphs
    // We must remove block-level elements before wrapping in <p> tags
    $text = preg_replace('/(<\/?(h[1-6]|ul|ol|li|table|tr|td|th|hr|figure|div)>)/', "\n$1\n", $text);
    $text = trim($text);

    $blocks = preg_split('/(\n\s*){2,}/', $text);
    $html = '';
    foreach ($blocks as $block) {
        $block = trim($block);
        if (preg_match('/^(<h[1-6]|<ul|<ol|<table|<hr|<figure|<div)/', $block)) {
            // This block is already formatted
            $html .= $block;
        } else {
            // This is a paragraph
            if (!empty($block)) {
                 $html .= '<p>' . nl2br($block) . '</p>';
            }
        }
    }

    // Cleanup empty paragraphs that might have been created
    $html = preg_replace('/<p><\/p>/', '', $html);

    return $html;
}
