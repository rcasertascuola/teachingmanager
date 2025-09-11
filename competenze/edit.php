<?php
require_once '../src/Database.php';
require_once '../src/Competenza.php';
require_once '../src/TipologiaCompetenza.php';
require_once '../src/Conoscenza.php';
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
$manager = new Competenza($db);

// Fetch related data for form fields
$tipologia_manager = new TipologiaCompetenza($db);
$all_tipologie = $tipologia_manager->findAll();
$tipologie_options = [];
foreach ($all_tipologie as $t) {
    $tipologie_options[$t->id] = $t->nome;
}

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
    $abilita_options[$a->id] = $a->nome;
}

$entity = null;
if (isset($_GET['id'])) {
    $entity = $manager->findById((int)$_GET['id']);
} else {
    $entity = new Competenza($db);
}

$page_title = $entity->id ? 'Modifica Competenza' : 'Crea Nuova Competenza';
$form_action = 'save.php';

$form_fields = [
    'nome' => ['label' => 'Nome', 'type' => 'text', 'required' => true],
    'descrizione' => ['label' => 'Descrizione', 'type' => 'textarea'],
    'tipologia_id' => [
        'label' => 'Tipologia (obbligatorio)',
        'type' => 'select',
        'options' => $tipologie_options,
        'required' => true,
        'default_option' => 'Seleziona una tipologia...'
    ],
    'conoscenze' => [
        'label' => 'Conoscenze',
        'type' => 'checkbox_group',
        'options' => $conoscenze_options,
        'help_text' => '(Lasciare vuoto per non assegnare)'
    ],
    'abilita' => [
        'label' => 'Abilità',
        'type' => 'checkbox_group',
        'options' => $abilita_options,
        'help_text' => '(Lasciare vuoto per non assegnare)'
    ]
];

// Include the generic handler
require_once '../handlers/edit_handler.php';
?>
<?php include '../footer.php'; ?>
