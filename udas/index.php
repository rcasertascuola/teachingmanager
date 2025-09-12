<?php
require_once '../src/Database.php';
require_once '../src/Uda.php';
include '../header.php';

// Auth check
if ($_SESSION['role'] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}

// Configuration for the generic index handler
$db = Database::getInstance()->getConnection();
$manager = new Uda($db);

$page_title = 'Gestione UDA';
$entity_name = 'UDA';
$table_name = 'udas';
$joins = [
    'LEFT JOIN modules ON udas.module_id = modules.id'
];
$selects = [
    'udas.id as id',
    'udas.name as name',
    'udas.description as description',
    'modules.name as module_name'
];
$columns = [
    'name' => 'Nome',
    'description' => 'Descrizione',
    'module_name' => 'Modulo'
];

// Map columns that need a dependency tooltip to the table containing the FK.
$tooltip_map = [
    'module_name' => 'udas'
];


// The "view" for a UDA is the list of lessons associated with it.
$custom_actions = [
    ['href' => '../lessons/index.php?uda_id=', 'class' => 'btn-info', 'icon' => 'fa-eye']
];


// Include the generic handler
require_once '../handlers/index_handler.php';
?>

<?php include '../footer.php'; ?>
