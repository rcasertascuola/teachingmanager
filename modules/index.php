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

// Fetch extra data needed for custom column rendering
$uda_manager = new Uda($db);
$udas = $uda_manager->findAll();
$udaNameMap = [];
foreach ($udas as $uda) {
    $udaNameMap[$uda->id] = $uda->name;
}

$page_title = 'Gestione Moduli';
$entity_name = 'Modulo';
$columns = [
    'name' => 'Nome',
    'description' => 'Descrizione',
    'uda_id' => function($item) use ($udaNameMap) {
        return htmlspecialchars($udaNameMap[$item->uda_id] ?? 'N/A');
    }
];
$items = $manager->findAll();

// Include the generic handler
require_once '../handlers/index_handler.php';
?>
