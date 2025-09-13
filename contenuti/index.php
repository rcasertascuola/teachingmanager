<?php
require_once '../src/Database.php';
require_once '../src/Contenuto.php';
include '../header.php';

// Auth check
if ($_SESSION['role'] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}

// Configuration for the generic index handler
$db = Database::getInstance()->getConnection();
$manager = new Contenuto($db);

$page_title = 'Gestione Contenuti';
$entity_name = 'Contenuto';
$table_name = 'contenuti';
$columns = [
    'nome' => 'Nome',
    'descrizione' => 'Descrizione'
];

$renderers = [];

$custom_actions = [];

// Include the generic handler
require_once '../handlers/index_handler.php';
?>

<?php include '../footer.php'; ?>
