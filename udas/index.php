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

// Note: A 'view' custom action is intentionally omitted.
// The udas/view.php file is a list view for a module's UDAs, not a detail view for a single UDA.
// The intended "view" action from this table is to see the lessons for the UDA,
// but this was found to be confusing for the user.

// Include the generic handler
require_once '../handlers/index_handler.php';
?>

<?php include '../footer.php'; ?>
