<?php
require_once '../src/Database.php';
require_once '../src/Competenza.php';
require_once '../src/TipologiaCompetenza.php';
require_once '../src/Conoscenza.php';
require_once '../src/Abilita.php';
require_once '../src/Disciplina.php';
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

$disciplina_manager = new Disciplina($db);
$all_discipline = $disciplina_manager->findAll();
$discipline_options = [];
foreach ($all_discipline as $d) {
    $discipline_options[$d->id] = $d->nome;
}

$anni_corso_options = array_combine(range(1, 5), array_map(function($y) { return "$y° anno"; }, range(1, 5)));

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
    ],
    'discipline' => [
        'label' => 'Discipline',
        'type' => 'checkbox_group',
        'options' => $discipline_options,
        'help_text' => '(Lasciare vuoto per non assegnare)'
    ]
];

// Custom part of the form for the radio buttons
ob_start();
?>
<div class="form-group">
    <label>Anni di Corso</label>
    <div class="form-check">
        <input class="form-check-input" type="radio" name="anno_type" id="anno_tutti" value="tutti" <?php echo (empty($entity->anni_corso)) ? 'checked' : ''; ?>>
        <label class="form-check-label" for="anno_tutti">
            Tutti gli anni
        </label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="radio" name="anno_type" id="anno_specifici" value="specifici" <?php echo (!empty($entity->anni_corso)) ? 'checked' : ''; ?>>
        <label class="form-check-label" for="anno_specifici">
            Anni specifici
        </label>
    </div>
    <div id="anni-specifici-container" class="mt-2" style="<?php echo (empty($entity->anni_corso)) ? 'display: none;' : ''; ?>">
        <?php foreach ($anni_corso_options as $anno_value => $anno_label): ?>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="anno_<?php echo $anno_value; ?>" name="anni_corso[]" value="<?php echo $anno_value; ?>" <?php echo ($entity && in_array($anno_value, $entity->anni_corso)) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="anno_<?php echo $anno_value; ?>"><?php echo $anno_label; ?></label>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php
$custom_form_html = ob_get_clean();

$form_fields['custom_html'] = $custom_form_html;

// Include the generic handler
require_once '../handlers/edit_handler.php';
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const radioTutti = document.getElementById('anno_tutti');
    const radioSpecifici = document.getElementById('anno_specifici');
    const specificiContainer = document.getElementById('anni-specifici-container');

    function toggleAnniContainer() {
        if (radioSpecifici.checked) {
            specificiContainer.style.display = 'block';
        } else {
            specificiContainer.style.display = 'none';
        }
    }

    radioTutti.addEventListener('change', toggleAnniContainer);
    radioSpecifici.addEventListener('change', toggleAnniContainer);
});
</script>
<?php include '../footer.php'; ?>
