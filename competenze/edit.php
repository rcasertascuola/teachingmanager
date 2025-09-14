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
    'origine' => [
        'label' => 'Origine',
        'type' => 'select',
        'required' => true,
        'options' => [
            'dipartimento' => 'Dipartimento',
            'ministeriali' => 'Ministeriali',
            'docente' => 'Docente',
            'altro' => 'Altro'
        ]
    ]
];

// Include the generic handler
require_once '../handlers/edit_handler.php';
?>
<?php include '../footer.php'; ?>
