<?php
require_once '../src/Database.php';
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
$manager = new Abilita($db);

// Fetch related data for form fields
$conoscenza_manager = new Conoscenza($db);
$all_conoscenze = $conoscenza_manager->findAll();
$conoscenze_options = [];
foreach ($all_conoscenze as $c) {
    $conoscenze_options[$c->id] = $c->nome;
}

$entity = null;
if (isset($_GET['id'])) {
    $entity = $manager->findById((int)$_GET['id']);
} else {
    $entity = new Abilita($db);
}

$page_title = $entity->id ? 'Modifica Abilità' : 'Crea Nuova Abilità';
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
    'tipo' => [
        'label' => 'Tipo',
        'type' => 'select',
        'options' => [
            'cognitiva' => 'Cognitiva',
            'tecnico/pratica' => 'Tecnico/Pratica'
        ]
    ],
    'conoscenze' => [
        'label' => 'Conoscenze Collegate',
        'type' => 'checkbox_group',
        'options' => $conoscenze_options,
        'help_text' => 'Seleziona una o più conoscenze.'
    ]
];

// Include the generic handler
require_once '../handlers/edit_handler.php';
?>
