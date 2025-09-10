<?php
require_once '../src/Database.php';
require_once '../src/Abilita.php';
require_once '../src/Conoscenza.php';
require_once '../src/Disciplina.php';
include '../header.php';

// Additional check for teacher role
if ($_SESSION['role'] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}

// Fetch related data for form fields
$all_conoscenze = Conoscenza::findAll();
$anni_corso_options = range(1, 5);
$tipi_options = ['cognitiva', 'tecnico/pratica'];

$abilita = null;
if (isset($_GET['id'])) {
    $abilita = Abilita::findById($_GET['id']);
}

$pageTitle = $abilita ? 'Modifica Abilità' : 'Crea Nuova Abilità';
$formAction = 'save.php';
?>
    <div class="container mt-5">
        <h2><?php echo $pageTitle; ?></h2>
        <form action="<?php echo $formAction; ?>" method="post">
            <?php if ($abilita && $abilita->id): ?>
                <input type="hidden" name="id" value="<?php echo $abilita->id; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($abilita->nome ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="descrizione">Descrizione</label>
                <textarea class="form-control" id="descrizione" name="descrizione" rows="3"><?php echo htmlspecialchars($abilita->descrizione ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="tipo">Tipo</label>
                <select class="form-control" id="tipo" name="tipo">
                    <?php foreach ($tipi_options as $tipo_option): ?>
                        <option value="<?php echo $tipo_option; ?>" <?php echo ($abilita && $abilita->tipo == $tipo_option) ? 'selected' : ''; ?>>
                            <?php echo ucfirst($tipo_option); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Conoscenze Collegate</label>
                <div class="border p-2" style="max-height: 150px; overflow-y: auto;">
                    <?php foreach ($all_conoscenze as $conoscenza): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="conoscenza_<?php echo $conoscenza->id; ?>" name="conoscenze[]" value="<?php echo $conoscenza->id; ?>" <?php echo ($abilita && in_array($conoscenza->id, $abilita->conoscenze)) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="conoscenza_<?php echo $conoscenza->id; ?>">
                                <?php echo htmlspecialchars($conoscenza->nome); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <small class="form-text text-muted">Seleziona una o più conoscenze.</small>
            </div>

            <div class="form-group">
                <label>Anni di Corso</label>
                <div>
                    <?php foreach ($anni_corso_options as $anno): ?>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="anno_<?php echo $anno; ?>" name="anni_corso[]" value="<?php echo $anno; ?>" <?php echo ($abilita && in_array($anno, $abilita->anni_corso)) ? 'checked' : ''; ?>>
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
