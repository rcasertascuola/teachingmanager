<?php
require_once '../src/Database.php';
require_once '../src/Conoscenza.php';
require_once '../src/Disciplina.php';
include '../header.php';

// Teacher role check
if ($_SESSION['role'] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}

// Fetch all disciplines for the multi-select
$all_discipline = Disciplina::findAll();
$anni_corso_options = range(1, 5);

$conoscenza = null;
if (isset($_GET['id'])) {
    $conoscenza = Conoscenza::findById($_GET['id']);
}

$pageTitle = $conoscenza ? 'Modifica Conoscenza' : 'Crea Nuova Conoscenza';
$formAction = 'save.php';
?>
    <div class="container mt-5">
        <h2><?php echo $pageTitle; ?></h2>
        <form action="<?php echo $formAction; ?>" method="post">
            <?php if ($conoscenza && $conoscenza->id): ?>
                <input type="hidden" name="id" value="<?php echo $conoscenza->id; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($conoscenza->nome ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="descrizione">Descrizione</label>
                <textarea class="form-control" id="descrizione" name="descrizione" rows="3"><?php echo htmlspecialchars($conoscenza->descrizione ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="discipline">Discipline Correlate</label>
                <select multiple class="form-control" id="discipline" name="discipline[]" size="5">
                    <?php foreach ($all_discipline as $disciplina): ?>
                        <option value="<?php echo $disciplina->id; ?>" <?php echo ($conoscenza && in_array($disciplina->id, $conoscenza->discipline)) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($disciplina->nome); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Anni di Corso</label>
                <div>
                    <?php foreach ($anni_corso_options as $anno): ?>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="anno_<?php echo $anno; ?>" name="anni_corso[]" value="<?php echo $anno; ?>" <?php echo ($conoscenza && in_array($anno, $conoscenza->anni_corso)) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="anno_<?php echo $anno; ?>"><?php echo $anno; ?>° anno</label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Salva</button>
            <a href="index.php" class="btn btn-secondary">Annulla</a>
        </form>
    </div>
<?php include '../footer.php'; ?>
