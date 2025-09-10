<?php
session_start();
// Redirect to login if not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login.php");
    exit;
}

require_once '../src/Database.php';
require_once '../src/Module.php';
require_once '../src/Uda.php';

if (!isset($_GET['uda_id']) || empty($_GET['uda_id'])) {
    header("location: ../udas/view.php");
    exit;
}

$uda_id = (int)$_GET['uda_id'];
$uda = Uda::findById($uda_id);

if (!$uda) {
    header("location: ../udas/view.php");
    exit;
}

$modules = Module::findByUdaId($uda_id);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elenco Moduli per <?php echo htmlspecialchars($uda->name); ?></title>
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
        <h1 class="h2 mb-4">Elenco Moduli per <?php echo htmlspecialchars($uda->name); ?></h1>

        <div class="list-group">
            <?php if (empty($modules)): ?>
                <p class="text-center">Nessun modulo trovato per questa UDA.</p>
            <?php else: ?>
                <?php foreach ($modules as $module): ?>
                    <a href="../lessons/index.php?module_id=<?php echo $module->id; ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1"><?php echo htmlspecialchars($module->name); ?></h5>
                        </div>
                        <p class="mb-1"><?php echo htmlspecialchars($module->description); ?></p>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="mt-4">
            <a href="../udas/view.php" class="btn btn-secondary">Torna all'elenco UDA</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
