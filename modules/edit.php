<?php
session_start();
// Auth check
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'teacher') {
    header("location: ../login.php");
    exit;
}

require_once '../src/Database.php';
require_once '../src/Module.php';
require_once '../src/Uda.php';

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
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../dashboard.php">Gestionale Studio</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
