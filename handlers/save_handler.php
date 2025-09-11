<?php
// Generic Save Handler

// Ensure required variables are set
if (!isset($manager) || !isset($entity) || !isset($redirect_url) || !isset($post_data)) {
    die("Configuration error in save handler.");
}

// Populate the entity object with data from the POST array
foreach ($post_data as $key => $value) {
    if (property_exists($entity, $key)) {
        $entity->$key = $value;
    }
}

// Handle checkbox data (if a checkbox is unchecked, it's not present in POST)
// This needs to be configured in the calling script.
// Example: $entity->enabled = isset($post_data['enabled']) ? 1 : 0;

$result = $entity->save();

if ($result === true) {
    $action = isset($post_data['id']) && !empty($post_data['id']) ? 'update' : 'create';
    $_SESSION['feedback'] = ['type' => 'success', 'message' => "Elemento salvato con successo."];
    header("Location: " . $redirect_url . "?success=" . $action);
} else {
    // If save() returns false, it's a generic failure.
    // A common cause is a unique constraint violation.
    // We set a generic message. The entity-specific save() method could also return a specific error string.
    $error_message = is_string($result) ? htmlspecialchars($result) : "Errore durante il salvataggio. Verificare che i dati siano corretti e che non esista già un elemento con lo stesso nome.";
    $_SESSION['feedback'] = ['type' => 'danger', 'message' => $error_message];

    // Redirect back to the edit form to show the error
    $id_param = isset($post_data['id']) ? '?id=' . $post_data['id'] : '';
    header("Location: edit.php" . $id_param);
}
exit;
?>
