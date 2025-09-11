<?php
require_once '../src/Database.php';
require_once '../src/TipologiaCompetenza.php';
include '../header.php';

// Auth check
if ($_SESSION['role'] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}

// Configuration for the generic edit handler
$db = Database::getInstance()->getConnection();
$manager = new TipologiaCompetenza($db);

$entity = null;
if (isset($_GET['id'])) {
    $entity = $manager->findById((int)$_GET['id']);
} else {
    $entity = new TipologiaCompetenza($db);
}

$page_title = $entity->id ? 'Modifica Tipologia' : 'Crea Nuova Tipologia';
$form_action = 'save.php';

$form_fields = [
    'nome' => [
        'label' => 'Nome',
        'type' => 'text',
        'required' => true
    ]
];

// Include the generic handler
require_once '../handlers/edit_handler.php';
?>
