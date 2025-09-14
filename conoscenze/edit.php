<?php
require_once '../src/Database.php';
require_once '../src/Conoscenza.php';
include '../header.php';

// Teacher role check
if ($_SESSION['role'] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}

// Configuration for the generic edit handler
$db = Database::getInstance()->getConnection();
$manager = new Conoscenza($db);

$entity = null;
if (isset($_GET['id'])) {
    $entity = $manager->findById((int)$_GET['id']);
} else {
    $entity = new Conoscenza($db);
}

$page_title = $entity->id ? 'Modifica Conoscenza' : 'Crea Nuova Conoscenza';
$form_action = 'save.php';

$form_fields = [
    'nome' => ['label' => 'Nome', 'type' => 'text', 'required' => true],
    'descrizione' => ['label' => 'Descrizione', 'type' => 'textarea'],
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
