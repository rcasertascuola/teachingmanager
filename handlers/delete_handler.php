<?php
// Generic Delete Handler

// Ensure required variables are set
if (!isset($manager) || !isset($id) || !isset($redirect_url)) {
    die("Configuration error in delete handler.");
}

// Perform the delete operation
if ($manager->delete($id)) {
    // Set success feedback
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['feedback'] = ['type' => 'success', 'message' => 'Elemento cancellato con successo.'];
} else {
    // Set error feedback
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['feedback'] = ['type' => 'danger', 'message' => 'Errore durante la cancellazione dell\'elemento.'];
}

// Redirect back
header("Location: " . $redirect_url);
exit;
?>
