<?php
require_once '../src/Database.php';
require_once '../src/Uda.php';
require_once '../src/Module.php';
require_once '../src/TooltipHelper.php';
include '../header.php';

$db = Database::getInstance()->getConnection();

if (isset($_GET['id'])) {
    // === SINGLE UDA VIEW ===
    $uda_manager = new Uda($db);
    $uda = $uda_manager->findById($_GET['id']);

    if (!$uda) {
        header('Location: index.php');
        exit;
    }

    // Fetch related module for display
    $module_manager = new Module($db);
    $module = $uda->module_id ? $module_manager->findById($uda->module_id) : null;

    ?>
    <div class="container mt-5">
        <h2>Dettaglio UDA: <?php echo htmlspecialchars($uda->name); ?></h2>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Descrizione</h5>
                <p class="card-text"><?php echo nl2br(htmlspecialchars($uda->description)); ?></p>

                <h5 class="card-title mt-4">Modulo di Appartenenza</h5>
                <p class="card-text"><?php echo $module ? add_dependency_tooltip($module->name, 'udas', 'modules') : 'Nessun modulo associato'; ?></p>
            </div>
        </div>
        <div class="mt-3">
            <a href="edit.php?id=<?php echo $uda->id; ?>" class="btn btn-primary">Modifica</a>
            <a href="index.php" class="btn btn-secondary">Torna all'elenco</a>
        </div>
    </div>
    <?php

} elseif (isset($_GET['module_id'])) {
    // === LIST UDAS BY MODULE VIEW (EXISTING CODE) ===
    $module_id = (int)$_GET['module_id'];
    $module_manager = new Module($db);
    $module = $module_manager->findById($module_id);

    if (!$module) {
        // Changed redirect to index.php to be consistent
        header("location: index.php");
        exit;
    }
    $uda_manager = new Uda($db);
    $udas = $uda_manager->findByModuleId($module_id);
    ?>
    <div class="container mt-4">
        <h1 class="h2 mb-4">Elenco UDA per il Modulo: <?php echo add_dependency_tooltip($module->name, 'udas', 'modules'); ?></h1>
        <div class="list-group">
            <?php if (empty($udas)): ?>
                <p class="text-center">Nessuna UDA trovata per questo Modulo.</p>
            <?php else: ?>
                <?php foreach ($udas as $uda): ?>
                    <a href="../lessons/index.php?uda_id=<?php echo $uda->id; ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1"><?php echo htmlspecialchars($uda->name); ?></h5>
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
    <?php
} else {
    // No valid parameter, redirect to index
    header('Location: index.php');
    exit;
}

include '../footer.php';
?>
