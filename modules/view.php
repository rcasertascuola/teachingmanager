<?php
require_once '../src/Database.php';
require_once '../src/Module.php';
include '../header.php';


// Get the database connection
$db = Database::getInstance()->getConnection();
$module_manager = new Module($db);
$modules = $module_manager->findAll();
?>

    <div class="container mt-4">
        <h1 class="h2 mb-4">Elenco Moduli</h1>

        <div class="list-group">
            <?php if (empty($modules)): ?>
                <p class="text-center">Nessun Modulo trovato.</p>
            <?php else: ?>
                <?php foreach ($modules as $module): ?>
                    <a href="../udas/view.php?module_id=<?php echo $module->id; ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1"><?php echo htmlspecialchars($module->name); ?></h5>
                            <small>Anno: <?php echo htmlspecialchars($module->anno_corso); ?></small>
                        </div>
                        <p class="mb-1"><?php echo htmlspecialchars($module->description); ?></p>
                        <small>Disciplina: <?php echo htmlspecialchars($module->disciplina_name ?? 'Non specificata'); ?></small>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php include '../footer.php'; ?>

