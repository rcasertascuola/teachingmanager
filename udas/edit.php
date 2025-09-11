<?php
require_once '../src/Database.php';
require_once '../src/Uda.php';
require_once '../src/Module.php';
include '../header.php';

// Auth check
if ($_SESSION["role"] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}


// Get the database connection
$db = Database::getInstance()->getConnection();
$uda_manager = new Uda($db);

$uda = null;
$pageTitle = 'Aggiungi Nuova UDA';
$formAction = 'save.php';

$module_manager = new Module($db);
$modules = $module_manager->findAll();

// Check if we are editing an existing UDA
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $uda = $uda_manager->findById((int)$_GET['id']);
    if ($uda) {
        $pageTitle = 'Modifica UDA';
    } else {
        // UDA not found, redirect to index
        header("location: index.php");
        exit;
    }
} else {
    $uda = new Uda($db);
}

?>

    <div class="container mt-4">
        <h1 class="h2 mb-4"><?php echo $pageTitle; ?></h1>

        <div class="card">
            <div class="card-body">
                <form action="<?php echo $formAction; ?>" method="post">
                    <?php if ($uda && $uda->id): ?>
                        <input type="hidden" name="id" value="<?php echo $uda->id; ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="name" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($uda->name ?? ''); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Descrizione</label>
                        <textarea class="form-control" id="description" name="description" rows="5"><?php echo htmlspecialchars($uda->description ?? ''); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="module_id" class="form-label">Modulo di appartenenza</label>
                        <select class="form-select" id="module_id" name="module_id" required>
                            <option value="">Seleziona un Modulo</option>
                            <?php foreach ($modules as $module): ?>
                                <option value="<?php echo $module->id; ?>" <?php echo (isset($uda) && $uda->module_id == $module->id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($module->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <a href="index.php" class="btn btn-secondary">Annulla</a>
                    <button type="submit" class="btn btn-primary">Salva UDA</button>
                </form>
            </div>
        </div>
    </div>

<?php include '../footer.php'; ?>
