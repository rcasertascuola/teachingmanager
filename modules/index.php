<?php
require_once '../src/Database.php';
require_once '../src/Module.php';
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
$manager = new Module($db);

$page_title = 'Gestione Moduli';
$entity_name = 'Modulo';
$table_name = 'modules';
$joins = [
    'LEFT JOIN udas ON modules.uda_id = udas.id'
];
$selects = [
    'modules.id as id',
    'modules.name as name',
    'modules.description as description',
    'udas.name as uda_name'
];
$columns = [
    'id' => 'ID',
    'name' => 'Nome',
    'description' => 'Descrizione',
    'uda_name' => 'UDA'
];

// Include the generic handler
require_once '../handlers/index_handler.php';
?>

<?php include '../footer.php'; ?>
