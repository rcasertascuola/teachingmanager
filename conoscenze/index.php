<?php
require_once '../src/Database.php';
require_once '../src/Conoscenza.php';
include '../header.php';

// Auth check
if ($_SESSION['role'] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}

// Configuration for the generic index handler
$db = Database::getInstance()->getConnection();
$manager = new Conoscenza($db);

$page_title = 'Gestione Conoscenze';
$entity_name = 'Conoscenza';
$table_name = 'conoscenze';
$columns = [
    'nome' => 'Nome',
    'origine' => 'Origine',
    'anni_corso' => 'Anni di Corso',
    'discipline' => 'Discipline'
];

$renderers = [
    'anni_corso' => 'anniCorsoBadge',
    'discipline' => 'arrayBadge'
];

$custom_actions = [
    ['href' => 'view.php?id=', 'class' => 'btn-info', 'icon' => 'fa-eye']
];

// Include the generic handler
require_once '../handlers/index_handler.php';
?>

<?php include '../footer.php'; ?>
