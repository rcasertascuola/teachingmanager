<?php
require_once '../src/Database.php';
require_once '../src/Conoscenza.php';
require_once '../src/Disciplina.php'; // To get discipline names
include '../header.php';


if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$conoscenza = Conoscenza::findById($_GET['id']);

if (!$conoscenza) {
    header('Location: index.php');
    exit;
}

// For displaying names instead of just IDs
$all_discipline = Disciplina::findAll();
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

                <h5 class="card-title mt-4">Discipline Correlate</h5>
                <?php if (!empty($conoscenza->discipline)): ?>
                    <ul>
                        <?php foreach ($conoscenza->discipline as $disciplina_id): ?>
                            <li><?php echo htmlspecialchars($discipline_map[$disciplina_id] ?? 'ID Sconosciuto'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Nessuna disciplina correlata.</p>
                <?php endif; ?>

                <h5 class="card-title mt-4">Anni di Corso</h5>
                <?php if (!empty($conoscenza->anni_corso)): ?>
                    <p><?php echo implode(', ', array_map(function($y) { return $y . '° anno'; }, $conoscenza->anni_corso)); ?></p>
                <?php else: ?>
                    <p>Nessun anno di corso specificato.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-3">
            <a href="edit.php?id=<?php echo $conoscenza->id; ?>" class="btn btn-primary">Modifica</a>
            <a href="index.php" class="btn btn-secondary">Torna all'elenco</a>
        </div>
    </div>
<?php include '../footer.php'; ?>
