<?php
require_once '../src/Database.php';
require_once '../src/Verifica.php';
require_once '../src/Module.php';
require_once '../src/Abilita.php';
require_once '../src/Conoscenza.php';
require_once '../src/Competenza.php';
include '../header.php';

$verifica = null;
$module = null;
$conoscenze = [];
$abilita = [];
$competenze = [];

// Get the database connection
$db = Database::getInstance()->getConnection();
$verifica_manager = new Verifica($db);

if (isset($_GET['id'])) {
    $verifica = $verifica_manager->findById((int)$_GET['id']);
}

if ($verifica) {
    $module = $verifica->getModule();
    if ($module) {
        $conoscenza_manager = new Conoscenza($db);
        $conoscenze = $conoscenza_manager->findByIds($module->getConoscenze());

        $abilita_manager = new Abilita($db);
        $abilita = $abilita_manager->findByIds($module->getAbilita());

        $competenza_manager = new Competenza($db);
        $competenze = $competenza_manager->findByIds($module->getCompetenze());
    }
}

$is_teacher = isset($_SESSION['role']) && $_SESSION['role'] === 'teacher';
?>

<div class="container mt-4">
    <?php if ($verifica): ?>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h1 class="h2 mb-0"><?php echo htmlspecialchars($verifica->titolo); ?></h1>
                <?php if ($is_teacher): ?>
                    <a href="edit.php?id=<?php echo $verifica->id; ?>" class="btn btn-primary">Modifica Verifica</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <p><strong>Tipo:</strong> <?php echo htmlspecialchars(ucfirst($verifica->tipo)); ?></p>
                <?php if (!empty($verifica->descrizione)): ?>
                    <p><strong>Descrizione:</strong> <?php echo nl2br(htmlspecialchars($verifica->descrizione)); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h3 class="h4 mb-0">Dettagli Ereditati dal Modulo</h3>
            </div>
            <div class="card-body">
                <?php if ($module): ?>
                    <p><strong>Modulo di Riferimento:</strong> <a href="../modules/view.php?id=<?php echo $module->id; ?>"><?php echo htmlspecialchars($module->name); ?></a></p>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <h5>Conoscenze</h5>
                            <?php if (!empty($conoscenze)): ?>
                                <ul class="list-group">
                                    <?php foreach ($conoscenze as $item): ?>
                                        <li class="list-group-item"><?php echo htmlspecialchars($item->nome); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p>Nessuna conoscenza ereditata.</p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <h5>Abilità</h5>
                            <?php if (!empty($abilita)): ?>
                                <ul class="list-group">
                                    <?php foreach ($abilita as $item): ?>
                                        <li class="list-group-item"><?php echo htmlspecialchars($item->nome); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p>Nessuna abilità ereditata.</p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <h5>Competenze</h5>
                            <?php if (!empty($competenze)): ?>
                                <ul class="list-group">
                                    <?php foreach ($competenze as $item): ?>
                                        <li class="list-group-item"><?php echo htmlspecialchars($item->nome); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p>Nessuna competenza ereditata.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <p>Questa verifica non è collegata a nessun modulo.</p>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($verifica->griglia): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h3 class="h4 mb-0">Griglia di Valutazione: <?php echo htmlspecialchars($verifica->griglia->nome); ?></h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Descrittore</th>
                            <th>Punteggio Massimo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $punteggio_totale = 0;
                        if (!empty($verifica->griglia->descrittori)):
                            foreach ($verifica->griglia->descrittori as $descrittore):
                                $punteggio_totale += $descrittore->punteggio_max;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($descrittore->descrittore); ?></td>
                            <td><?php echo htmlspecialchars(number_format($descrittore->punteggio_max, 2)); ?></td>
                        </tr>
                        <?php
                            endforeach;
                        endif;
                        ?>
                    </tbody>
                    <tfoot class="table-group-divider">
                        <tr>
                            <th class="text-end">Punteggio Totale</th>
                            <th><?php echo htmlspecialchars(number_format($punteggio_totale, 2)); ?> / 20.00</th>
                        </tr>
                    </tfoot>
                </table>
                 <small class="text-muted">Nota: La somma dei punteggi massimi dei descrittori costituisce il punteggio totale della verifica, che è sempre in ventesimi.</small>
            </div>
        </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="alert alert-danger" role="alert">
            <h4 class="alert-heading">Errore</h4>
            <p>La verifica richiesta non è stata trovata.</p>
            <hr>
            <a href="index.php" class="btn btn-secondary">Torna all'elenco</a>
        </div>
    <?php endif; ?>
</div>

<?php include '../footer.php'; ?>
