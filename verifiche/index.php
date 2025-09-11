<?php
require_once '../src/Database.php';
require_once '../src/Verifica.php';
include '../header.php';

// Auth check
if ($_SESSION['role'] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}

// Configuration for the generic index handler
$db = Database::getInstance()->getConnection();
$manager = new Verifica($db);

$page_title = 'Gestione Verifiche';
$entity_name = 'Verifica';
$table_name = 'verifiche';
$columns = [
    'titolo' => 'Titolo',
    'tipo' => 'Tipo',
    'created_at' => 'Data Creazione'
];
$custom_actions = [
    [
        'href' => 'registro.php?id=',
        'class' => 'btn-success',
        'icon' => 'fa-book'
    ]
];

// Include the generic handler
require_once '../handlers/index_handler.php';
?>
