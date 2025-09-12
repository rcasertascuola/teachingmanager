<?php
require_once '../src/Database.php';
require_once '../src/Exercise.php';
include '../header.php';

// Auth check - only teachers can manage exercises
if ($_SESSION["role"] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}

// Configuration for the generic index handler
$page_title = 'Gestione Esercizi';
$entity_name = 'Esercizio';
$table_name = 'exercises';
$columns = [
    'title' => 'Titolo',
    'type' => 'Tipo',
    'enabled' => 'Stato'
];
$renderers = [
    'enabled' => 'statusBadge'
];

$custom_actions = [
    ['href' => 'view.php?id=', 'class' => 'btn-info', 'icon' => 'fa-eye']
];

?>

<div class="container mt-4">
    <?php
    // Feedback messages from other pages
    if (isset($_SESSION['feedback'])) {
        $feedback = $_SESSION['feedback'];
        echo '<div class="alert alert-' . htmlspecialchars($feedback['type']) . ' alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($feedback['message']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION['feedback']);
    }
    ?>

    <div class="card mb-4">
        <div class="card-header">Importa da JSON</div>
        <div class="card-body">
            <form action="import.php" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="jsonFile" class="form-label">Seleziona file JSON</label>
                    <input class="form-control" type="file" id="jsonFile" name="jsonFile" accept="application/json" required>
                </div>
                <button type="submit" class="btn btn-success"><i class="fas fa-upload"></i> Carica File</button>
            </form>
        </div>
    </div>

    <?php
    // We are now using the generic handler for the table display.
    require_once '../handlers/index_handler.php';
    ?>
</div>

<?php include '../footer.php'; ?>
