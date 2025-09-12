<?php

require_once __DIR__ . '/SchemaManager.php';

/**
 * Wraps HTML content with a Bootstrap tooltip that shows data dependencies.
 *
 * @param string $htmlContent The HTML content to be displayed (e.g., a field value).
 * @param string $tableName The database table name where the dependency originates.
 * @param string|null $targetTable The final table in the dependency chain we are interested in. If null, no tooltip is added.
 * @return string The original HTML content, possibly wrapped in a <span> with tooltip attributes.
 */
function add_dependency_tooltip($htmlContent, $tableName, $targetTable = null) {
    // Ensure content is properly escaped before wrapping. This is always safe to do.
    $escapedContent = htmlspecialchars($htmlContent ?? '', ENT_QUOTES, 'UTF-8');

    // If no target table is specified, return the escaped content without a tooltip.
    // This makes the function backward-compatible with old calls.
    if ($targetTable === null) {
        return $escapedContent;
    }

    $allChains = SchemaManager::getDependencyChains($tableName);

    // Filter chains to find ones that are relevant to the target table.
    // A chain is relevant if it ends with the target table name.
    $relevantChains = array_filter($allChains, function ($chain) use ($targetTable) {
        // The chain format is "table1 -> table2 -> table3".
        // We check if the chain string ends with the target table name.
        return str_ends_with($chain, $targetTable);
    });

    if (empty($relevantChains)) {
        return $escapedContent;
    }

    $tooltipTitle = "Provenienza del dato:<br><ul>";
    foreach ($relevantChains as $chain) {
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
