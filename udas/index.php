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
    'LEFT JOIN discipline ON udas.disciplina_id = discipline.id'
];
$selects = [
    'udas.id as id',
    'udas.name as name',
    'udas.description as description',
    'discipline.nome as disciplina_name',
    'udas.anno_corso as anno_corso'
];
$columns = [
    'id' => 'ID',
    'name' => 'Nome',
    'description' => 'Descrizione',
    'disciplina_name' => 'Disciplina',
    'anno_corso' => 'Anno Corso'
];

// Include the generic handler
require_once '../handlers/index_handler.php';
?>

<?php include '../footer.php'; ?>
