<?php
require_once '../src/Database.php';
require_once '../src/Module.php';
require_once '../src/Uda.php';
include '../header.php';


if (!isset($_GET['module_id']) || empty($_GET['module_id'])) {
    header("location: ../modules/view.php");
    exit;
}

$module_id = (int)$_GET['module_id'];
// Get the database connection
$db = Database::getInstance()->getConnection();
$module_manager = new Module($db);
$module = $module_manager->findById($module_id);

if (!$module) {
    header("location: ../modules/view.php");
    exit;
}
$uda_manager = new Uda($db);
$udas = $uda_manager->findByModuleId($module_id);
?>

    <div class="container mt-4">
        <h1 class="h2 mb-4">Elenco UDA per <?php echo add_dependency_tooltip($module->name, 'udas', 'modules'); ?></h1>
        <div class="list-group">
            <?php if (empty($udas)): ?>
                <p class="text-center">Nessuna UDA trovata per questo Modulo.</p>
            <?php else: ?>
                <?php foreach ($udas as $uda): ?>
                    <a href="../lessons/index.php?uda_id=<?php echo $uda->id; ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <h5 class="mb-1"><?php echo htmlspecialchars($uda->name); ?></h5>
                            <?php if ($uda->disciplina_nome): ?>
                                <span class="badge bg-primary"><?php echo htmlspecialchars($uda->disciplina_nome); ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="mb-1"><?php echo htmlspecialchars($uda->description); ?></p>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="mt-4">
            <a href="../modules/index.php" class="btn btn-secondary">Torna all'elenco Moduli</a>
        </div>
    </div>

<?php include '../footer.php'; ?>
