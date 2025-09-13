<?php
require_once '../src/Database.php';
require_once '../src/Contenuto.php';
require_once '../src/Abilita.php';
require_once '../src/Conoscenza.php';
include '../header.php';

// Auth check
if ($_SESSION['role'] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}

// Configuration for the generic edit handler
$db = Database::getInstance()->getConnection();
$manager = new Contenuto($db);

// Fetch related data for form fields
$conoscenza_manager = new Conoscenza($db);
$all_conoscenze = $conoscenza_manager->findAll();
$conoscenze_options = [];
foreach ($all_conoscenze as $c) {
    $conoscenze_options[$c->id] = $c->nome;
}

$abilita_manager = new Abilita($db);
$all_abilita = $abilita_manager->findAll();
$abilita_options = [];
foreach ($all_abilita as $a) {
    $abilita_options[$a->id] = $a->nome . ' (' . $a->tipo . ')';
}

$entity = null;
if (isset($_GET['id'])) {
    $entity = $manager->findById((int)$_GET['id']);
} else {
    $entity = new Contenuto($db);
}

$page_title = $entity->id ? 'Modifica Contenuto' : 'Crea Nuovo Contenuto';
$form_action = 'save.php';

$form_fields = [
    'nome' => [
        'label' => 'Nome',
        'type' => 'text',
        'required' => true
    ],
    'descrizione' => [
        'label' => 'Descrizione',
        'type' => 'textarea'
    ],
    'conoscenze' => [
        'label' => 'Conoscenze Collegate',
        'type' => 'checkbox_group',
        'options' => $conoscenze_options,
        'help_text' => 'Seleziona una o più conoscenze.'
    ],
    'abilita' => [
        'label' => 'Abilità Collegate',
        'type' => 'checkbox_group',
        'options' => $abilita_options,
        'help_text' => 'Seleziona una o più abilità.'
    ]
];

// Include the generic handler
require_once '../handlers/edit_handler.php';
?>
