<?php
require_once '../src/Database.php';
require_once '../src/Competenza.php';
require_once '../src/TipologiaCompetenza.php';
require_once '../src/Conoscenza.php';
require_once '../src/Abilita.php';
require_once '../src/Disciplina.php';

session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] !== 'teacher') {
    header('Location: ../login.php');
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
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
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
                <label for="conoscenze">Conoscenze</label>
                <select multiple class="form-control" id="conoscenze" name="conoscenze[]" size="5">
                    <?php foreach ($all_conoscenze as $conoscenza): ?>
                        <option value="<?php echo $conoscenza->id; ?>" <?php echo ($competenza && in_array($conoscenza->id, $competenza->conoscenze)) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($conoscenza->nome); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">(Lasciare vuoto per non assegnare)</small>
            </div>

            <div class="form-group">
                <label for="abilita">Abilità</label>
                <select multiple class="form-control" id="abilita" name="abilita[]" size="5">
                    <?php foreach ($all_abilita as $item): ?>
                        <option value="<?php echo $item->id; ?>" <?php echo ($competenza && in_array($item->id, $competenza->abilita)) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($item->nome); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">(Lasciare vuoto per non assegnare)</small>
            </div>

            <div class="form-group">
                <label for="discipline">Discipline</label>
                <select multiple class="form-control" id="discipline" name="discipline[]" size="5">
                    <?php foreach ($all_discipline as $disciplina): ?>
                        <option value="<?php echo $disciplina->id; ?>" <?php echo ($competenza && in_array($disciplina->id, $competenza->discipline)) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($disciplina->nome); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
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
</body>
</html>
