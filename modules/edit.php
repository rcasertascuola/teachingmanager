<?php
require_once '../src/Database.php';
require_once '../src/Module.php';
require_once '../src/Disciplina.php';
require_once '../src/Conoscenza.php';
require_once '../src/Abilita.php';
require_once '../src/Competenza.php';
include '../header.php';

// Auth check
if ($_SESSION["role"] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}


// Get the database connection
$db = Database::getInstance()->getConnection();
$module_manager = new Module($db);
$disciplina_manager = new Disciplina($db);
$conoscenza_manager = new Conoscenza($db);
$abilita_manager = new Abilita($db);
$competenza_manager = new Competenza($db);

$all_discipline = $disciplina_manager->findAll();
$all_conoscenze = $conoscenza_manager->findAll();
$all_abilita = $abilita_manager->findAll();
$all_competenze = $competenza_manager->findAll();

$module = null;
$pageTitle = 'Aggiungi Nuovo Modulo';
$formAction = 'save.php';

// Check if we are editing an existing UDA
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $module = $module_manager->findById((int)$_GET['id']);
    if ($module) {
        $pageTitle = 'Modifica Modulo';
    } else {
        // UDA not found, redirect to index
        header("location: index.php");
        exit;
    }
} else {
    $module = new Module($db);
}

?>

    <div class="container mt-4">
        <h1 class="h2 mb-4"><?php echo $pageTitle; ?></h1>

        <div class="card">
            <div class="card-body">
                <form action="<?php echo $formAction; ?>" method="post">
                    <?php if ($module && $module->id): ?>
                        <input type="hidden" name="id" value="<?php echo $module->id; ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="name" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($module->name ?? ''); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Descrizione</label>
                        <textarea class="form-control" id="description" name="description" rows="5"><?php echo htmlspecialchars($module->description ?? ''); ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="disciplina_id" class="form-label">Disciplina</label>
                            <select class="form-select" id="disciplina_id" name="disciplina_id">
                                <option value="">Seleziona una disciplina</option>
                                <?php foreach ($all_discipline as $disciplina): ?>
                                    <option value="<?php echo $disciplina->id; ?>" <?php echo ($module->disciplina_id == $disciplina->id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($disciplina->nome); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="anno_corso" class="form-label">Anno di Corso</label>
                            <select class="form-select" id="anno_corso" name="anno_corso">
                                <option value="">Tutti gli anni</option>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo ($module->anno_corso == $i) ? 'selected' : ''; ?>>
                                        <?php echo $i; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="tempo_stimato" class="form-label">Tempo Stimato (ore)</label>
                            <input type="number" class="form-control" id="tempo_stimato" name="tempo_stimato" min="0" value="<?php echo htmlspecialchars($module->tempo_stimato ?? ''); ?>">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-4 mb-3">
                            <label for="conoscenze" class="form-label">Conoscenze</label>
                            <div class="form-control" style="height: 200px; overflow-y: auto;">
                                <?php foreach ($all_conoscenze as $item): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="conoscenze[]" value="<?php echo $item->id; ?>" id="conoscenza_<?php echo $item->id; ?>" <?php echo in_array($item->id, $module->conoscenze) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="conoscenza_<?php echo $item->id; ?>">
                                            <?php echo htmlspecialchars($item->nome); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="abilita" class="form-label">Abilità</label>
                            <div class="form-control" style="height: 200px; overflow-y: auto;">
                                <?php foreach ($all_abilita as $item): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="abilita[]" value="<?php echo $item->id; ?>" id="abilita_<?php echo $item->id; ?>" <?php echo in_array($item->id, $module->abilita) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="abilita_<?php echo $item->id; ?>">
                                            <?php echo htmlspecialchars($item->nome); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="competenze" class="form-label">Competenze</label>
                            <div class="form-control" style="height: 200px; overflow-y: auto;">
                                <?php foreach ($all_competenze as $item): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="competenze[]" value="<?php echo $item->id; ?>" id="competenza_<?php echo $item->id; ?>" <?php echo in_array($item->id, $module->competenze) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="competenza_<?php echo $item->id; ?>">
                                            <?php echo htmlspecialchars($item->nome); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <a href="index.php" class="btn btn-secondary">Annulla</a>
                    <button type="submit" class="btn btn-primary">Salva Modulo</button>
                </form>
            </div>
        </div>
    </div>

<?php include '../footer.php'; ?>
