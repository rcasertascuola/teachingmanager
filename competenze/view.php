<?php
require_once '../src/Database.php';
require_once '../src/Competenza.php';
require_once '../src/TipologiaCompetenza.php';
require_once '../src/Conoscenza.php';
require_once '../src/Abilita.php';
require_once '../src/TooltipHelper.php';
include '../header.php';


if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

// Get the database connection
$db = Database::getInstance()->getConnection();
$competenza_manager = new Competenza($db);
$competenza = $competenza_manager->findById($_GET['id']);

if (!$competenza) {
    header('Location: index.php');
    exit;
}

// Fetch all related data for display
$tipologia_manager = new TipologiaCompetenza($db);
$tipologia = $competenza->tipologia_id ? $tipologia_manager->findById($competenza->tipologia_id) : null;

$conoscenza_manager = new Conoscenza($db);
$all_conoscenze = $conoscenza_manager->findAll();
$conoscenze_map = [];
foreach ($all_conoscenze as $c) {
    $conoscenze_map[$c->id] = $c->nome;
}

$abilita_manager = new Abilita($db);
$all_abilita = $abilita_manager->findAll();
$abilita_map = [];
foreach ($all_abilita as $a) {
    $abilita_map[$a->id] = $a->nome;
}

?>
    <div class="container mt-5">
        <h2>Dettaglio: <?php echo htmlspecialchars($competenza->nome); ?></h2>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Descrizione</h5>
                <p class="card-text"><?php echo nl2br(htmlspecialchars($competenza->descrizione)); ?></p>

                <h5 class="card-title mt-4">Tipologia</h5>
                <p class="card-text"><?php echo $tipologia ? add_dependency_tooltip($tipologia->nome, 'competenze', 'tipologie_competenze') : 'N/A'; ?></p>

                <h5 class="card-title mt-4">Conoscenze</h5>
                <?php if (!empty($competenza->conoscenze)): ?>
                    <ul>
                        <?php foreach ($competenza->conoscenze as $id): ?>
                            <li><?php echo add_dependency_tooltip(
                                $conoscenze_map[$id] ?? 'ID Sconosciuto',
                                'competenze',
                                'conoscenze'
                            ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Nessuna conoscenza.</p>
                <?php endif; ?>

                <h5 class="card-title mt-4">Abilità</h5>
                <?php if (!empty($competenza->abilita)): ?>
                    <ul>
                        <?php foreach ($competenza->abilita as $id): ?>
                            <li><?php echo add_dependency_tooltip(
                                $abilita_map[$id] ?? 'ID Sconosciuto',
                                'competenze',
                                'abilita'
                            ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Nessuna abilità.</p>
                <?php endif; ?>

                <h5 class="card-title mt-4">Anni di Corso</h5>
                <?php if (!empty($competenza->anni_corso)): ?>
                    <p><?php echo implode(', ', array_map(function($y) { return $y . '° anno'; }, $competenza->anni_corso)); ?></p>
                <?php else: ?>
                    <p>Nessun anno di corso.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-3">
            <a href="edit.php?id=<?php echo $competenza->id; ?>" class="btn btn-primary">Modifica</a>
            <a href="index.php" class="btn btn-secondary">Torna all'elenco</a>
        </div>
    </div>
<?php include '../footer.php'; ?>
