<?php
require_once '../src/Database.php';
require_once '../src/Abilita.php';
require_once '../src/Conoscenza.php';
require_once '../src/Disciplina.php';

session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$abilita = Abilita::findById($_GET['id']);

if (!$abilita) {
    header('Location: index.php');
    exit;
}

// Fetch related data for display
$all_conoscenze = Conoscenza::findAll();
$conoscenze_map = [];
foreach ($all_conoscenze as $c) {
    $conoscenze_map[$c->id] = $c->nome;
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
    <title>Dettaglio Abilità</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
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
                            <li><?php echo htmlspecialchars($conoscenze_map[$conoscenza_id] ?? 'ID Sconosciuto'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Nessuna conoscenza collegata.</p>
                <?php endif; ?>

                <h5 class="card-title mt-4">Discipline Correlate</h5>
                <?php if (!empty($abilita->discipline)): ?>
                    <ul>
                        <?php foreach ($abilita->discipline as $disciplina_id): ?>
                            <li><?php echo htmlspecialchars($discipline_map[$disciplina_id] ?? 'ID Sconosciuto'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Nessuna disciplina correlata.</p>
                <?php endif; ?>

                <h5 class="card-title mt-4">Anni di Corso</h5>
                <?php if (!empty($abilita->anni_corso)): ?>
                    <p><?php echo implode(', ', array_map(function($y) { return $y . '° anno'; }, $abilita->anni_corso)); ?></p>
                <?php else: ?>
                    <p>Nessun anno di corso specificato.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-3">
            <a href="edit.php?id=<?php echo $abilita->id; ?>" class="btn btn-primary">Modifica</a>
            <a href="index.php" class="btn btn-secondary">Torna all'elenco</a>
        </div>
    </div>
</body>
</html>
