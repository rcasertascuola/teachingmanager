<?php
require_once '../src/Database.php';
require_once '../src/Competenza.php';
require_once '../src/TipologiaCompetenza.php';
include '../header.php';

// Auth check
if ($_SESSION['role'] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}

// Configuration for the generic index handler
$db = Database::getInstance()->getConnection();
$manager = new Competenza($db);

// Fetch extra data needed for custom column rendering
$tipologia_manager = new TipologiaCompetenza($db);
$tipologie = $tipologia_manager->findAll();
$tipologia_map = [];
foreach ($tipologie as $t) {
    $tipologia_map[$t->id] = $t->nome;
}

$page_title = 'Gestione Competenze';
$entity_name = 'Competenza';
$columns = [
    'id' => 'ID',
    'nome' => 'Nome',
    'tipologia_id' => function($item) use ($tipologia_map) {
        return htmlspecialchars($tipologia_map[$item->tipologia_id] ?? 'N/A');
    }
];
$items = $manager->findAll();

// Include the generic handler
require_once '../handlers/index_handler.php';
?>
