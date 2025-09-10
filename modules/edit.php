<?php
require_once '../src/Database.php';
require_once '../src/Module.php';
require_once '../src/Uda.php';
include '../header.php';

// Auth check
if ($_SESSION["role"] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}


$module = null;
$pageTitle = 'Aggiungi Nuovo Modulo';
$formAction = 'save.php';

$udas = Uda::findAll();

// Check if we are editing an existing module
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $module = Module::findById((int)$_GET['id']);
    if ($module) {
        $pageTitle = 'Modifica Modulo';
    } else {
        // Module not found, redirect to index
        header("location: index.php");
        exit;
    }
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

                    <div class="mb-3">
                        <label for="uda_id" class="form-label">UDA di appartenenza</label>
                        <select class="form-select" id="uda_id" name="uda_id" required>
                            <option value="">Seleziona un'UDA</option>
                            <?php foreach ($udas as $uda): ?>
                                <option value="<?php echo $uda->id; ?>" <?php echo (isset($module) && $module->uda_id == $uda->id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($uda->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <a href="index.php" class="btn btn-secondary">Annulla</a>
                    <button type="submit" class="btn btn-primary">Salva Modulo</button>
                </form>
            </div>
        </div>
    </div>

<?php include '../footer.php'; ?>
