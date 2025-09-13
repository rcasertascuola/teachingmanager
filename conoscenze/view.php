<?php
require_once '../src/Database.php';
require_once '../src/Conoscenza.php';
require_once '../src/Disciplina.php'; // To get discipline names
include '../header.php';


if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

// Get the database connection
$db = Database::getInstance()->getConnection();
$conoscenza_manager = new Conoscenza($db);
$conoscenza = $conoscenza_manager->findById($_GET['id']);

if (!$conoscenza) {
    header('Location: index.php');
    exit;
}

// For displaying names instead of just IDs
$disciplina_manager = new Disciplina($db);
$all_discipline = $disciplina_manager->findAll();
$discipline_map = [];
foreach ($all_discipline as $d) {
    $discipline_map[$d->id] = $d->nome;
}

?>
    <div class="container mt-5">
        <h2>Dettaglio: <?php echo htmlspecialchars($conoscenza->nome); ?></h2>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Descrizione</h5>
                <p class="card-text"><?php echo nl2br(htmlspecialchars($conoscenza->descrizione)); ?></p>

                <h5 class="card-title mt-4">Anni di Corso</h5>
                <div>
                <?php if (!empty($conoscenza->anni_corso)): ?>
                    <?php foreach ($conoscenza->anni_corso as $anno): ?>
                        <span class="badge bg-info me-1"><?php echo htmlspecialchars($anno); ?></span>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Nessun anno di corso calcolato.</p>
                <?php endif; ?>
                </div>

                <h5 class="card-title mt-4">Discipline Ereditate</h5>
                <div>
                <?php if (!empty($conoscenza->discipline)): ?>
                    <?php foreach ($conoscenza->discipline as $disciplina): ?>
                        <span class="badge bg-secondary me-1"><?php echo htmlspecialchars($disciplina); ?></span>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Nessuna disciplina calcolata.</p>
                <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <a href="edit.php?id=<?php echo $conoscenza->id; ?>" class="btn btn-primary">Modifica</a>
            <a href="index.php" class="btn btn-secondary">Torna all'elenco</a>
        </div>
    </div>
<?php include '../footer.php'; ?>
