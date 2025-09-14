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

$page_title = 'Gestione Competenze';
$entity_name = 'Competenza';
$table_name = 'competenze';
$joins = [
    'LEFT JOIN tipologie_competenze ON competenze.tipologia_id = tipologie_competenze.id'
];
$selects = [
    'competenze.id as id',
    'competenze.nome as nome',
    'competenze.codifica as codifica',
    'competenze.origine as origine',
    'tipologie_competenze.nome as tipologia'
];
$columns = [
    'nome' => 'Nome',
    'codifica' => 'Codifica',
    'origine' => 'Origine',
    'tipologia' => 'Tipologia',
    'anni_corso' => 'Anni di Corso',
    'discipline' => 'Discipline'
];

$renderers = [
    'anni_corso' => 'anniCorsoBadge',
    'discipline' => 'arrayBadge'
];

$custom_actions = [
    ['href' => 'view.php?id=', 'class' => 'btn-info', 'icon' => 'fa-eye']
];

$tooltip_map = [
    'tipologia' => 'competenze'
];

// Include the generic handler
require_once '../handlers/index_handler.php';
?>

<?php include '../footer.php'; ?>
