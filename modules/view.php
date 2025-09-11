<?php
require_once '../src/Database.php';
require_once '../src/Module.php';
require_once '../src/Uda.php';
include '../header.php';


if (!isset($_GET['uda_id']) || empty($_GET['uda_id'])) {
    header("location: ../udas/view.php");
    exit;
}

$uda_id = (int)$_GET['uda_id'];
// Get the database connection
$db = Database::getInstance()->getConnection();
$uda_manager = new Uda($db);
$uda = $uda_manager->findById($uda_id);

if (!$uda) {
    header("location: ../udas/view.php");
    exit;
}
$module_manager = new Module($db);
$modules = $module_manager->findByUdaId($uda_id);
?>

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

<?php include '../footer.php'; ?>
