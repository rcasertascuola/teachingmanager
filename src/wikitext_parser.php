<?php

function parse_wikitext($text) {
    // The order of operations is important.

    // 1. Escape HTML characters to prevent XSS.
    $text = htmlspecialchars($text, ENT_NOQUOTES);

    // --- Block-level elements ---
    // These need to be processed before inline elements and paragraphs.

    // A temporary placeholder to protect already processed blocks from further parsing.
    $placeholders = [];
    $placeholder_id = 0;

    // Helper function for placeholders
    $make_placeholder = function ($content) use (&$placeholders, &$placeholder_id) {
        $key = '%%PLACEHOLDER_' . $placeholder_id++ . '%%';
        $placeholders[$key] = $content;
        return $key;
    };

    // 2. Headings: Process from most specific (h3) to least specific (h1).
    $text = preg_replace('/^===\s*(.*?)\s*===/m', '<h3>$1</h3>', $text);
    $text = preg_replace('/^==\s*(.*?)\s*==/m', '<h2>$1</h2>', $text);
    $text = preg_replace('/^=\s*(.*?)\s*=/m', '<h1>$1</h1>', $text);

    // 3. Horizontal Rule
    $text = preg_replace('/^----/m', '<hr>', $text);

    // 4. Lists (Bulleted and Numbered)
    $text = preg_replace_callback('/((?:(?:^\*|\#)\s.*(?:\n|$))+)/m', function ($matches) use ($make_placeholder) {
        $list_text = trim($matches[1]);
        $lines = explode("\n", $list_text);
        $type = (strpos($lines[0], '*') === 0) ? 'ul' : 'ol';

        $items_html = '';
        foreach ($lines as $line) {
            $line = trim($line);
            if(empty($line)) continue;
            // Remove the marker (* or #) and trim
            $item_content = trim(substr($line, 1));
            $items_html .= '<li>' . $item_content . '</li>';
        }

        $list_html = "<{$type}>" . $items_html . "</{$type}>";
        return $make_placeholder($list_html);
    }, $text);


    // 5. Tables
    $text = preg_replace_callback('/((?:^\|[^\n]*\n?)+)/m', function ($matches) use ($make_placeholder) {
        $table_text = trim($matches[1]);
        $rows = explode("\n", $table_text);

        // Removed 'table' class, added inline-table style for content-width
        $table_html = "<table class=\"table-bordered\" style=\"display: inline-table;\">\n";
        $thead_html = '';
        $tbody_html = '';
        $in_header = true;

        foreach ($rows as $row) {
            $row = trim($row);
            if (empty($row)) continue;

            if (preg_match('/^\|-.*-\|$/', $row)) {
                $in_header = false;
                continue;
            }

            $cells = explode('|', trim($row, '|'));
            $tag = $in_header ? 'th' : 'td';
            $row_html = "<tr>";
            foreach ($cells as $cell) {
                $row_html .= "<{$tag}>" . trim($cell) . "</{$tag}>";
            }
            $row_html .= "</tr>";

            if ($in_header) {
                $thead_html .= $row_html;
            } else {
                $tbody_html .= $row_html;
            }
        }

        if (!empty($thead_html)) {
            $table_html .= "<thead>{$thead_html}</thead>\n";
        }
        if (!empty($tbody_html)) {
            $table_html .= "<tbody>{$tbody_html}</tbody>\n";
        }

        $table_html .= "</table>";
        return $make_placeholder($table_html);
    }, $text);


    // --- Inline elements ---

    // 6. Bold and Italic
    $text = preg_replace("/'''(.*?)'''/", '<strong>$1</strong>', $text);
    $text = preg_replace("/''(.*?)''/", '<em>$1</em>', $text);

    // 7. Images and Videos (as block-level figures/divs, so placeholder them)
    $text = preg_replace_callback('/\[\[Image:(https?:\/\/[^\s|\]]+)\|([^\]]+)\]\]/', function ($matches) use ($make_placeholder) {
        $url = htmlspecialchars_decode($matches[1]);
        $caption = trim($matches[2]);
        $figure = '<figure class="figure">
                     <img src="' . $url . '" class="img-fluid" alt="' . htmlspecialchars($caption) . '">
                     <figcaption class="figure-caption">' . htmlspecialchars($caption) . '</figcaption>
                   </figure>';
        return $make_placeholder($figure);
    }, $text);

    $text = preg_replace_callback('/\[\[Video:(https?:\/\/(?:www\.youtube\.com\/watch\?v=|youtu\.be\/)[^\s\]]+)\]\]/', function ($matches) use ($make_placeholder) {
        $url = htmlspecialchars_decode($matches[1]);
        if (strpos($url, 'watch?v=') !== false) {
            parse_str(parse_url($url, PHP_URL_QUERY), $query_params);
            $video_id = $query_params['v'];
        } else {
            $video_id = basename($url);
        }
        $embed_url = 'https://www.youtube.com/embed/' . $video_id;
        $video_div = '<div class="ratio ratio-16x9">
                        <iframe src="' . $embed_url . '" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                      </div>';
        return $make_placeholder($video_div);
    }, $text);

    // 8. Colored Text
    $text = preg_replace_callback('/\{\{color:([a-zA-Z]+|#[0-9a-fA-F]{3,6})\|(.*?)\}\}/', function ($matches) {
        $color = htmlspecialchars_decode($matches[1]);
        $content = $matches[2];
        if (!preg_match('/^(?:[a-zA-Z]+|#[0-9a-fA-F]{3,6})$/', $color)) {
            return $matches[0]; // Invalid color
        }
        return '<span style="color:' . $color . ';">' . $content . '</span>';
    }, $text);

    // 9. Links (Internal and External)
    // Exclude [[Image...]] and [[Video...]]
    $text = preg_replace_callback('/\[\[([^\]|:\n]+)(?:\|([^\]\n]+))?\]\]/', function ($matches) {
        $page = trim($matches[1]);
        $display = isset($matches[2]) ? trim($matches[2]) : $page;
        $url = 'view.php?title=' . urlencode($page);
        return '<a href="' . $url . '">' . htmlspecialchars($display) . '</a>';
    }, $text);

    $text = preg_replace('/\[(https?:\/\/[^\s\]]+)\s+([^\]]+)\]/', '<a href="$1" target="_blank" rel="noopener noreferrer">$2</a>', $text);

    // --- Final Processing ---

    // 10. Paragraphs
    // Split the text into paragraphs, but respect the placeholders for block elements.
    $blocks = preg_split('/(\n\s*){2,}/', trim($text));
    $html = "";
    foreach ($blocks as $block) {
        $block = trim($block);
        if (empty($block)) continue;

        if (strpos($block, '%%PLACEHOLDER_') === false) {
            // This is a simple paragraph, apply nl2br for single line breaks
            $html .= '<p>' . nl2br($block, false) . "</p>\n";
        } else {
            // This block contains placeholders, so just add it.
            // The nl2br is not needed as block elements handle their own spacing.
            $html .= $block . "\n";
        }
    }

    // 11. Restore placeholders
    // str_replace is safe for this.
    $final_html = str_replace(array_keys($placeholders), array_values($placeholders), $html);

    return $final_html;
}
