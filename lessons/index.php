<?php
require_once '../src/Database.php';
require_once '../src/Lesson.php';
include '../header.php';

$is_teacher = $_SESSION['role'] === 'teacher';

// Configuration for the generic index handler
$page_title = 'Gestione Lezioni';
$entity_name = 'Lezione';
$table_name = 'lessons';

$joins = [
    'LEFT JOIN uda_lessons ON lessons.id = uda_lessons.lesson_id',
    'LEFT JOIN udas ON uda_lessons.uda_id = udas.id',
    'LEFT JOIN modules ON udas.module_id = modules.id'
];

$selects = [
    'lessons.id as id',
    'lessons.title as title',
    'modules.name as module_name',
    'udas.name as uda_name',
    'lessons.tags as tags'
];

$columns = [
    'title' => 'Titolo',
    'module_name' => 'Modulo',
    'uda_name' => 'UDA',
    'tags' => 'Tags'
];

$custom_actions = [
    ['href' => 'view.php?id=', 'class' => 'btn-info', 'icon' => 'fa-eye']
];

$tooltip_map = [
    'module_name' => 'modules',
    'uda_name' => 'udas'
];
?>

<div class="container mt-4">
    <?php
    if ($is_teacher && isset($_SESSION['import_feedback'])) {
        $feedback = $_SESSION['import_feedback'];
        echo '<div class="alert alert-' . htmlspecialchars($feedback['type']) . ' alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($feedback['message']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION['import_feedback']);
    }
    ?>

    <?php if ($is_teacher): ?>
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
    <?php endif; ?>

    <?php
    // We are now using the generic handler for the table display.
    // The handler itself will be included below and will render the main card and table.
    require_once '../handlers/index_handler.php';
    ?>
</div>

<?php include '../footer.php'; ?>
