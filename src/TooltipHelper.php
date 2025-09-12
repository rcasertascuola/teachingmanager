<?php

require_once 'SchemaManager.php';

/**
 * Wraps HTML content with a Bootstrap tooltip that shows data dependencies.
 *
 * @param string $htmlContent The HTML content to be displayed (e.g., a field value).
 * @param string $tableName The database table name corresponding to the content.
 * @return string The original HTML content, possibly wrapped in a <span> with tooltip attributes.
 */
function add_dependency_tooltip($htmlContent, $tableName) {
    $chains = SchemaManager::getDependencyChains($tableName);

    if (empty($chains)) {
        return $htmlContent;
    }

    $tooltipTitle = "Provenienza del dato:<br><ul>";
    foreach ($chains as $chain) {
        $tooltipTitle .= "<li>" . htmlspecialchars($chain) . "</li>";
    }
    $tooltipTitle .= "</ul>";

    // The 'data-bs-html="true"' attribute is crucial for rendering HTML inside the tooltip.
    // The 'data-bs-placement="top"' is for positioning.
    // The 'data-bs-toggle="tooltip"' initializes the tooltip.
    return '<span
        data-bs-toggle="tooltip"
        data-bs-html="true"
        data-bs-placement="top"
        title="' . $tooltipTitle . '"
    >' . $htmlContent . '</span>';
}
?>
