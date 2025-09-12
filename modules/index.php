<?php
require_once '../src/Database.php';
require_once '../src/Module.php';
include '../header.php';

// Auth check
if ($_SESSION['role'] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}

// Configuration for the generic index handler
$db = Database::getInstance()->getConnection();
$manager = new Module($db);

$page_title = 'Gestione Moduli';
$entity_name = 'Modulo';
$table_name = 'modules';
$joins = [
    'LEFT JOIN discipline ON modules.disciplina_id = discipline.id'
];
$selects = [
    'modules.id as id',
    'modules.name as name',
    'modules.description as description',
    'discipline.nome as disciplina_name',
    'modules.anno_corso as anno_corso'
];
$columns = [
    'name' => 'Nome',
    'description' => 'Descrizione',
    'disciplina_name' => 'Disciplina',
    'anno_corso' => 'Anno Corso'
];

// Map columns that need a dependency tooltip to the table containing the FK.
$tooltip_map = [
    'disciplina_name' => 'modules'
];


$custom_actions = [
    ['href' => '../udas/view.php?module_id=', 'class' => 'btn-info', 'icon' => 'fa-eye']
];


// Include the generic handler
require_once '../handlers/index_handler.php';
?>

<?php include '../footer.php'; ?>
