<?php
require_once '../src/Database.php';
require_once '../src/Competenza.php';
require_once '../src/TipologiaCompetenza.php';
require_once '../src/Conoscenza.php';
require_once '../src/Abilita.php';
require_once '../src/Disciplina.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$competenza = Competenza::findById($_GET['id']);

if (!$competenza) {
    header('Location: index.php');
    exit;
}

// Fetch all related data for display
$tipologia = $competenza->tipologia_id ? TipologiaCompetenza::findById($competenza->tipologia_id) : null;

$all_conoscenze = Conoscenza::findAll();
$conoscenze_map = [];
foreach ($all_conoscenze as $c) {
    $conoscenze_map[$c->id] = $c->nome;
}

$all_abilita = Abilita::findAll();
$abilita_map = [];
foreach ($all_abilita as $a) {
    $abilita_map[$a->id] = $a->nome;
}

$all_discipline = Disciplina::findAll();
$discipline_map = [];
foreach ($all_discipline as $d) {
    $discipline_map[$d->id] = $d->nome;
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Dettaglio Competenza</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Dettaglio: <?php echo htmlspecialchars($competenza->nome); ?></h2>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Descrizione</h5>
                <p class="card-text"><?php echo nl2br(htmlspecialchars($competenza->descrizione)); ?></p>

                <h5 class="card-title mt-4">Tipologia</h5>
                <p class="card-text"><?php echo $tipologia ? htmlspecialchars($tipologia->nome) : 'N/A'; ?></p>

                <h5 class="card-title mt-4">Conoscenze</h5>
                <?php if (!empty($competenza->conoscenze)): ?>
                    <ul>
                        <?php foreach ($competenza->conoscenze as $id): ?>
                            <li><?php echo htmlspecialchars($conoscenze_map[$id] ?? 'ID Sconosciuto'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Nessuna conoscenza.</p>
                <?php endif; ?>

                <h5 class="card-title mt-4">Abilità</h5>
                <?php if (!empty($competenza->abilita)): ?>
                    <ul>
                        <?php foreach ($competenza->abilita as $id): ?>
                            <li><?php echo htmlspecialchars($abilita_map[$id] ?? 'ID Sconosciuto'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Nessuna abilità.</p>
                <?php endif; ?>

                <h5 class="card-title mt-4">Discipline</h5>
                <?php if (!empty($competenza->discipline)): ?>
                    <ul>
                        <?php foreach ($competenza->discipline as $id): ?>
                            <li><?php echo htmlspecialchars($discipline_map[$id] ?? 'ID Sconosciuto'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Nessuna disciplina.</p>
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
</body>
</html>
