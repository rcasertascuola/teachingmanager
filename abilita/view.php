<?php
require_once '../src/Database.php';
require_once '../src/Abilita.php';
require_once '../src/Conoscenza.php';
require_once '../src/Disciplina.php';
include '../header.php';


if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

// Get the database connection
$db = Database::getInstance()->getConnection();
// Create an instance of Abilita to use its methods
$abilita_manager = new Abilita($db);
// Fetch the abilita item
$abilita = $abilita_manager->findById($_GET['id']);

if (!$abilita) {
    header('Location: index.php');
    exit;
}

// Fetch related data for display
$conoscenza_manager = new Conoscenza($db);
$all_conoscenze = $conoscenza_manager->findAll();
$conoscenze_map = [];
foreach ($all_conoscenze as $c) {
    $conoscenze_map[$c->id] = $c->nome;
}

$disciplina_manager = new Disciplina($db);
$all_discipline = $disciplina_manager->findAll();
$discipline_map = [];
foreach ($all_discipline as $d) {
    $discipline_map[$d->id] = $d->nome;
}

// Derive disciplines from linked conoscenze
$derived_discipline_ids = [];
if (!empty($abilita->conoscenze)) {
    foreach ($abilita->conoscenze as $conoscenza_id) {
        $conoscenza = $conoscenza_manager->findById($conoscenza_id);
        if ($conoscenza && !empty($conoscenza->discipline)) {
            $derived_discipline_ids = array_merge($derived_discipline_ids, $conoscenza->discipline);
        }
    }
}
$derived_discipline_ids = array_unique($derived_discipline_ids);

?>
    <div class="container mt-5">
        <h2>Dettaglio: <?php echo htmlspecialchars($abilita->nome); ?></h2>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Descrizione</h5>
                <p class="card-text"><?php echo nl2br(htmlspecialchars($abilita->descrizione)); ?></p>

                <h5 class="card-title mt-4">Tipo</h5>
                <p class="card-text"><?php echo ucfirst(htmlspecialchars($abilita->tipo)); ?></p>

                <h5 class="card-title mt-4">Conoscenze Collegate</h5>
                <?php if (!empty($abilita->conoscenze)): ?>
                    <ul>
                        <?php foreach ($abilita->conoscenze as $conoscenza_id): ?>
                            <li><?php echo add_dependency_tooltip($conoscenze_map[$conoscenza_id] ?? 'ID Sconosciuto', 'abilita', 'conoscenze'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Nessuna conoscenza collegata.</p>
                <?php endif; ?>

                <h5 class="card-title mt-4">Discipline (derivate dalle conoscenze)</h5>
                <?php if (!empty($derived_discipline_ids)): ?>
                    <ul>
                        <?php foreach ($derived_discipline_ids as $disciplina_id): ?>
                            <li><?php echo htmlspecialchars($discipline_map[$disciplina_id] ?? 'ID Sconosciuto'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Nessuna disciplina derivata (collegare a conoscenze per vederle).</p>
                <?php endif; ?>

                <h5 class="card-title mt-4">Anni di Corso</h5>
                <div>
                <?php if (!empty($abilita->anni_corso)): ?>
                    <?php foreach ($abilita->anni_corso as $anno): ?>
                        <span class="badge bg-info me-1"><?php echo htmlspecialchars($anno); ?></span>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Nessun anno di corso calcolato.</p>
                <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <a href="edit.php?id=<?php echo $abilita->id; ?>" class="btn btn-primary">Modifica</a>
            <a href="index.php" class="btn btn-secondary">Torna all'elenco</a>
        </div>
    </div>
<?php include '../footer.php'; ?>
