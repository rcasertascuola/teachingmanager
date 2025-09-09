<?php

function parse_inline_wikitext($text) {
    // This function assumes the text has already been through htmlspecialchars.

    // Bold and Italic
    $text = preg_replace("/'''(.*?)'''/", '<strong>$1</strong>', $text);
    $text = preg_replace("/''(.*?)''/", '<em>$1</em>', $text);

    // Colored Text
    $text = preg_replace_callback('/\{\{color:([a-zA-Z]+|#[0-9a-fA-F]{3,6})\|(.*?)\}\}/', function ($matches) {
        $color = htmlspecialchars_decode($matches[1]);
        $content = $matches[2];
        if (!preg_match('/^(?:[a-zA-Z]+|#[0-9a-fA-F]{3,6})$/', $color)) {
            return $matches[0]; // Invalid color
        }
        return '<span style="color:' . $color . ';">' . $content . '</span>';
    }, $text);

    // Links (Internal and External)
    // Exclude [[Image...]] and [[Video...]]
    $text = preg_replace_callback('/\[\[([^\]|:\n]+)(?:\|([^\]\n]+))?\]\]/', function ($matches) {
        $page = trim($matches[1]);
        $display = isset($matches[2]) ? trim($matches[2]) : $page;
        $url = 'view.php?title=' . urlencode($page);
        // The display text is already escaped from the initial call in parse_wikitext
        return '<a href="' . $url . '">' . $display . '</a>';
    }, $text);

    $text = preg_replace('/\[(https?:\/\/[^\s\]]+)\s+([^\]]+)\]/', '<a href="$1" target="_blank" rel="noopener noreferrer">$2</a>', $text);

    return $text;
}


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
    $text = preg_replace_callback('/((?:(?:^[*#]+)\s.*(?:\n|$))+)/m', function ($matches) use ($make_placeholder) {
        $list_text = trim($matches[0]);
        $lines = explode("\n", $list_text);

        // --- Tree-based parser for nested lists ---

        // 1. Build a tree from the list lines
        $list_tree = [];
        $path = []; // A stack of indices to the current node

        foreach ($lines as $line) {
            if (trim($line) === '') continue;

            preg_match('/^([*#]+)\s*(.*)/', $line, $item_matches);
            if (!$item_matches) continue;

            $markers = $item_matches[1];
            $content = $item_matches[2];
            $level = strlen($markers);
            $type = ($markers[0] === '*') ? 'ul' : 'ol';

            // Adjust path to find the correct parent
            while (count($path) >= $level) {
                array_pop($path);
            }

            // Traverse to the parent node
            $parent = &$list_tree;
            foreach ($path as $index) {
                $parent = &$parent[count($parent) - 1]['children'];
            }

            // Add the new item
            $parent[] = ['type' => $type, 'content' => $content, 'children' => []];

            // Update the path to point to the new item
            $path[] = count($parent) - 1;
        }

        // 2. Render the tree into HTML
        $render_list_tree = function($tree) use (&$render_list_tree) {
            if (empty($tree)) {
                return '';
            }

            $html = '';
            $current_list_type = null;
            $buffer = [];

            $flush_buffer = function() use (&$html, &$buffer, &$current_list_type, &$render_list_tree) {
                if (empty($buffer)) return;

                $html .= '<' . $current_list_type . '>';
                foreach ($buffer as $item) {
                    $html .= '<li>' . parse_inline_wikitext($item['content']);
                    $html .= $render_list_tree($item['children']);
                    $html .= '</li>';
                }
                $html .= '</' . $current_list_type . '>';
                $buffer = [];
            };

            foreach ($tree as $item) {
                if ($item['type'] !== $current_list_type) {
                    $flush_buffer();
                    $current_list_type = $item['type'];
                }
                $buffer[] = $item;
            }
            $flush_buffer(); // Flush the last group

            return $html;
        };

        $list_html = $render_list_tree($list_tree);

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
                $row_html .= "<{$tag}>" . parse_inline_wikitext(trim($cell)) . "</{$tag}>";
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

    // 7. Images and Videos (as block-level figures/divs, so placeholder them)
    $text = preg_replace_callback('/\[\[Image:(https?:\/\/[^\s|\]]+)((?:\|[^|\]]*)*)\]\]/', function ($matches) use ($make_placeholder) {
        // Parse URL and options
        $url = htmlspecialchars_decode($matches[1]);
        $options_str = $matches[2];
        $parts = explode('|', $options_str);
        array_shift($parts); // Remove the empty string from the first pipe

        $options = [
            'type' => 'default', 'align' => null, 'size' => null, 'upright' => null,
            'alt' => null, 'link' => null, 'class' => null, 'border' => false, 'caption' => ''
        ];
        $caption_parts = [];

        foreach ($parts as $part) {
            $part = trim($part);
            if (in_array($part, ['thumb', 'thumbnail', 'frame', 'frameless'])) {
                $options['type'] = ($part === 'thumbnail' ? 'thumb' : $part);
            } elseif (in_array($part, ['left', 'right', 'center', 'none'])) {
                $options['align'] = $part;
            } elseif ($part === 'border') {
                $options['border'] = true;
            } elseif (preg_match('/^upright\s*=\s*([0-9\.]+)/', $part, $m)) {
                $options['upright'] = (float)$m[1];
            } elseif ($part === 'upright') {
                $options['upright'] = 0.75;
            } elseif (preg_match('/^(\d+)\s*px$/', $part, $m)) {
                $options['size'] = $m[1] . 'px';
            } elseif (preg_match('/^x(\d+)\s*px$/', $part, $m)) {
                $options['size'] = 'x' . $m[1] . 'px';
            } elseif (preg_match('/^(\d+)\s*x\s*(\d+)\s*px$/', $part, $m)) {
                $options['size'] = $m[1] . 'x' . $m[2] . 'px';
            } elseif (preg_match('/^alt\s*=\s*(.*)/', $part, $m)) {
                $options['alt'] = $m[1];
            } elseif (preg_match('/^link\s*=\s*(.*)/', $part, $m)) {
                $options['link'] = $m[1];
            } elseif (preg_match('/^class\s*=\s*(.*)/', $part, $m)) {
                $options['class'] = $m[1];
            } else {
                $caption_parts[] = $part;
            }
        }
        $options['caption'] = implode('|', $caption_parts);

        // --- HTML Generation ---
        $img_attrs = ['src' => $url, 'class' => 'img-fluid'];
        $figure_classes = ['figure'];
        $wrapper_classes = [];

        // Alt text
        $img_attrs['alt'] = htmlspecialchars($options['alt'] ?: $options['caption']);

        // Alignment
        if ($options['align']) {
            if ($options['type'] === 'thumb' || $options['type'] === 'frame') {
                 $wrapper_classes[] = 'figure-wrapper'; // Wrapper for alignment
                 if ($options['align'] === 'left') $wrapper_classes[] = 'float-start me-3';
                 if ($options['align'] === 'right') $wrapper_classes[] = 'float-end ms-3';
                 if ($options['align'] === 'center') $wrapper_classes[] = 'mx-auto d-table';
            } else {
                 // Plain image alignment
                 if ($options['align'] === 'left') $img_attrs['class'] .= ' float-start me-3';
                 if ($options['align'] === 'right') $img_attrs['class'] .= ' float-end ms-3';
                 if ($options['align'] === 'center') $img_attrs['class'] .= ' mx-auto d-block';
            }
        }

        // Sizing
        $img_styles = [];
        $figure_styles = [];
        if ($options['size']) {
            if (preg_match('/^(\d+)px$/', $options['size'], $m)) {
                $img_styles[] = 'width: ' . $m[1] . 'px; height: auto;';
                if ($options['type'] === 'thumb') $figure_styles[] = 'width: ' . $m[1] . 'px;';
            } elseif (preg_match('/^x(\d+)px$/', $options['size'], $m)) {
                $img_styles[] = 'height: ' . $m[1] . 'px; width: auto;';
            } elseif (preg_match('/^(\d+)x(\d+)px$/', $options['size'], $m)) {
                $img_styles[] = 'max-width: ' . $m[1] . 'px; max-height: ' . $m[2] . 'px;';
                 if ($options['type'] === 'thumb') $figure_styles[] = 'max-width: ' . $m[1] . 'px;';
            }
        } elseif ($options['upright']) {
            $width = round(220 * $options['upright']); // Assuming default 220px
            $img_styles[] = 'width: ' . $width . 'px; height: auto;';
            if ($options['type'] === 'thumb') $figure_styles[] = 'width: ' . $width . 'px;';
        }

        if ($options['class']) $img_attrs['class'] .= ' ' . htmlspecialchars($options['class']);
        if ($options['border']) $img_attrs['class'] .= ' border';
        if (!empty($img_styles)) $img_attrs['style'] = implode(' ', $img_styles);

        $img_attr_str = '';
        foreach ($img_attrs as $key => $val) {
            $img_attr_str .= ' ' . $key . '="' . $val . '"';
        }
        $img_tag = '<img' . $img_attr_str . '>';

        if ($options['link']) {
            $img_tag = '<a href="' . htmlspecialchars($options['link']) . '">' . $img_tag . '</a>';
        }

        $html = '';
        if ($options['type'] === 'thumb' || $options['type'] === 'frame') {
            if($options['type'] === 'thumb') $figure_classes[] = 'figure-thumbnail';
            $figure_attrs = ['class' => implode(' ', $figure_classes)];
            if(!empty($figure_styles)) $figure_attrs['style'] = implode(' ', $figure_styles);

            $figure_attr_str = '';
            foreach ($figure_attrs as $key => $val) {
                $figure_attr_str .= ' ' . $key . '="' . $val . '"';
            }

            $html = '<div class="' . implode(' ', $wrapper_classes) . '">';
            $html .= '<figure' . $figure_attr_str . '>';
            $html .= $img_tag;
            if (!empty($options['caption'])) {
                $html .= '<figcaption class="figure-caption">' . parse_inline_wikitext($options['caption']) . '</figcaption>';
            }
            $html .= '</figure></div>';
            if(empty($wrapper_classes)) $html = substr($html, 5, -6); // remove wrapper if not needed

        } else { // Plain image
            $html = $img_tag;
        }

        return $make_placeholder($html);
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

    // --- Final Processing ---

    // 10. Paragraphs
    // Split the text into paragraphs, but respect the placeholders for block elements.
    $blocks = preg_split('/(\n\s*){2,}/', trim($text));
    $html = "";
    foreach ($blocks as $block) {
        $block = trim($block);
        if (empty($block)) continue;

        // Parse inline elements for the whole block first.
        $parsed_block = parse_inline_wikitext($block);

        if (strpos($block, '%%PLACEHOLDER_') === false) {
            // This is a simple paragraph, wrap in <p> and apply nl2br for single line breaks
            $html .= '<p>' . nl2br($parsed_block, false) . "</p>\n";
        } else {
            // This block contains placeholders, so just add it without <p> tags.
            // The nl2br is not needed as block elements handle their own spacing.
            $html .= $parsed_block . "\n";
        }
    }

    // 11. Restore placeholders
    // str_replace is safe for this.
    $final_html = str_replace(array_keys($placeholders), array_values($placeholders), $html);

    return $final_html;
}
