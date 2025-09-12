<?php

require_once __DIR__ . '/SchemaManager.php';

/**
 * Wraps HTML content with a Bootstrap tooltip that shows data dependencies.
 *
 * @param string $htmlContent The HTML content to be displayed (e.g., a field value).
 * @param string $tableName The database table name corresponding to the content.
 * @return string The original HTML content, possibly wrapped in a <span> with tooltip attributes.
 */
function add_dependency_tooltip($htmlContent, $tableName) {
    // Ensure content is properly escaped before wrapping
    $escapedContent = htmlspecialchars($htmlContent ?? '', ENT_QUOTES, 'UTF-8');

    $chains = SchemaManager::getDependencyChains($tableName);

    if (empty($chains)) {
        return $escapedContent;
    }

    $tooltipTitle = "Provenienza del dato:<br><ul>";
    foreach ($chains as $chain) {
        $tooltipTitle .= "<li>" . htmlspecialchars($chain) . "</li>";
    }
    $tooltipTitle .= "</ul>";

    // The 'data-bs-html="true"' attribute is crucial for rendering HTML inside the tooltip.
    return '<span
        data-bs-toggle="tooltip"
        data-bs-html="true"
        data-bs-placement="top"
        title="' . $tooltipTitle . '"
    >' . $escapedContent . '</span>';
}
?>
