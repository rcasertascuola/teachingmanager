<?php
require_once '../src/Database.php';
require_once '../src/Abilita.php';
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
    ]
];

// Include the generic handler
require_once '../handlers/edit_handler.php';
?>
