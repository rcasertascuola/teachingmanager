<?php
require_once '../src/Database.php';
require_once '../src/Uda.php';
include '../header.php';


// Get the database connection
$db = Database::getInstance()->getConnection();
$uda_manager = new Uda($db);
$udas = $uda_manager->findAll();
?>

    <div class="container mt-4">
        <h1 class="h2 mb-4">Elenco UDA</h1>

        <div class="list-group">
            <?php if (empty($udas)): ?>
                <p class="text-center">Nessuna UDA trovata.</p>
            <?php else: ?>
                <?php foreach ($udas as $uda): ?>
                    <a href="../modules/view.php?uda_id=<?php echo $uda->id; ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1"><?php echo htmlspecialchars($uda->name); ?></h5>
                        </div>
                        <p class="mb-1"><?php echo htmlspecialchars($uda->description); ?></p>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
