<?php
require_once '../src/Database.php';
require_once '../src/Competenza.php';
require_once '../src/TipologiaCompetenza.php';
require_once '../src/Conoscenza.php';
require_once '../src/Abilita.php';
require_once '../src/Disciplina.php';
include '../header.php';

// Teacher role check
if ($_SESSION['role'] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}

// Fetch all related data for form fields
$all_tipologie = TipologiaCompetenza::findAll();
$all_conoscenze = Conoscenza::findAll();
$all_abilita = Abilita::findAll();
$all_discipline = Disciplina::findAll();
$anni_corso_options = range(1, 5);

$competenza = null;
if (isset($_GET['id'])) {
    $competenza = Competenza::findById($_GET['id']);
}

$pageTitle = $competenza ? 'Modifica Competenza' : 'Crea Nuova Competenza';
$formAction = 'save.php';
?>
    <div class="container mt-5">
        <h2><?php echo $pageTitle; ?></h2>
        <form action="<?php echo $formAction; ?>" method="post">
            <?php if ($competenza && $competenza->id): ?>
                <input type="hidden" name="id" value="<?php echo $competenza->id; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($competenza->nome ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="descrizione">Descrizione</label>
                <textarea class="form-control" id="descrizione" name="descrizione" rows="3"><?php echo htmlspecialchars($competenza->descrizione ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="tipologia_id">Tipologia (obbligatorio)</label>
                <select class="form-control" id="tipologia_id" name="tipologia_id" required>
                    <option value="">Seleziona una tipologia...</option>
                    <?php foreach ($all_tipologie as $tipologia): ?>
                        <option value="<?php echo $tipologia->id; ?>" <?php echo ($competenza && $competenza->tipologia_id == $tipologia->id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($tipologia->nome); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">(Lasciare vuoto per non assegnare)</small>
            </div>

            <div class="form-group">
                <label>Conoscenze</label>
                <div class="border p-2" style="max-height: 150px; overflow-y: auto;">
                    <?php foreach ($all_conoscenze as $conoscenza): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="conoscenze[]" value="<?php echo $conoscenza->id; ?>" <?php echo ($competenza && in_array($conoscenza->id, $competenza->conoscenze)) ? 'checked' : ''; ?>>
                            <label class="form-check-label">
                                <?php echo htmlspecialchars($conoscenza->nome); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <small class="form-text text-muted">(Lasciare vuoto per non assegnare)</small>
            </div>

            <div class="form-group">
                <label>Abilità</label>
                <div class="border p-2" style="max-height: 150px; overflow-y: auto;">
                    <?php foreach ($all_abilita as $item): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="abilita[]" value="<?php echo $item->id; ?>" <?php echo ($competenza && in_array($item->id, $competenza->abilita)) ? 'checked' : ''; ?>>
                            <label class="form-check-label">
                                <?php echo htmlspecialchars($item->nome); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <small class="form-text text-muted">(Lasciare vuoto per non assegnare)</small>
            </div>

            <div class="form-group">
                <label>Discipline</label>
                <div class="border p-2" style="max-height: 150px; overflow-y: auto;">
                    <?php foreach ($all_discipline as $disciplina): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="discipline[]" value="<?php echo $disciplina->id; ?>" <?php echo ($competenza && in_array($disciplina->id, $competenza->discipline)) ? 'checked' : ''; ?>>
                            <label class="form-check-label">
                                <?php echo htmlspecialchars($disciplina->nome); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <small class="form-text text-muted">(Lasciare vuoto per non assegnare)</small>
            </div>

            <div class="form-group">
                <label>Anni di Corso</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="anno_type" id="anno_tutti" value="tutti" <?php echo (empty($competenza->anni_corso)) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="anno_tutti">
                        Tutti gli anni
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="anno_type" id="anno_specifici" value="specifici" <?php echo (!empty($competenza->anni_corso)) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="anno_specifici">
                        Anni specifici
                    </label>
                </div>
                <div id="anni-specifici-container" class="mt-2" style="<?php echo (empty($competenza->anni_corso)) ? 'display: none;' : ''; ?>">
                    <?php foreach ($anni_corso_options as $anno): ?>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="anno_<?php echo $anno; ?>" name="anni_corso[]" value="<?php echo $anno; ?>" <?php echo ($competenza && in_array($anno, $competenza->anni_corso)) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="anno_<?php echo $anno; ?>"><?php echo $anno; ?>° anno</label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Salva</button>
            <a href="index.php" class="btn btn-secondary">Annulla</a>
        </form>
    </div>
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
