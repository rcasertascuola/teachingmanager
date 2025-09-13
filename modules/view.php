<?php
require_once '../src/Database.php';
require_once '../src/Module.php';
require_once '../src/Disciplina.php';
require_once '../src/TooltipHelper.php';
include '../header.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$db = Database::getInstance()->getConnection();
$module_manager = new Module($db);
$module = $module_manager->findById($_GET['id']);

if (!$module) {
    header('Location: index.php');
    exit;
}

// Fetch related discipline for display
$disciplina_manager = new Disciplina($db);
$disciplina = $module->disciplina_id ? $disciplina_manager->findById($module->disciplina_id) : null;

require_once '../src/Conoscenza.php';
require_once '../src/Abilita.php';
require_once '../src/Competenza.php';

// Fetch related KSA objects for display
$conoscenza_manager = new Conoscenza($db);
$conoscenze = $conoscenza_manager->findByIds($module->getConoscenze());

$abilita_manager = new Abilita($db);
$abilita = $abilita_manager->findByIds($module->getAbilita());

$competenza_manager = new Competenza($db);
$competenze = $competenza_manager->findByIds($module->getCompetenze());

?>
<div class="container mt-5">
    <h2>Dettaglio Modulo: <?php echo htmlspecialchars($module->name); ?></h2>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Descrizione</h5>
            <p class="card-text"><?php echo nl2br(htmlspecialchars($module->description)); ?></p>

            <h5 class="card-title mt-4">Disciplina</h5>
            <p class="card-text"><?php echo $disciplina ? add_dependency_tooltip($disciplina->nome, 'modules', 'discipline') : 'Nessuna disciplina associata'; ?></p>

            <h5 class="card-title mt-4">Anno di Corso</h5>
            <p class="card-text"><?php echo htmlspecialchars($module->anno_corso); ?>° anno</p>

            <hr>

            <h5 class="card-title mt-4">Conoscenze Associate</h5>
            <?php if (!empty($conoscenze)): ?>
                <ul>
                    <?php foreach ($conoscenze as $item): ?>
                        <li><?php echo htmlspecialchars($item->nome); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="card-text">Nessuna conoscenza associata.</p>
            <?php endif; ?>

            <h5 class="card-title mt-4">Abilità Associate</h5>
            <?php if (!empty($abilita)): ?>
                <ul>
                    <?php foreach ($abilita as $item): ?>
                        <li><?php echo htmlspecialchars($item->nome); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="card-text">Nessuna abilità associata.</p>
            <?php endif; ?>

            <h5 class="card-title mt-4">Competenze Associate</h5>
            <?php if (!empty($competenze)): ?>
                <ul>
                    <?php foreach ($competenze as $item): ?>
                        <li><?php echo htmlspecialchars($item->nome); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="card-text">Nessuna competenza associata.</p>
            <?php endif; ?>
        </div>
    </div>
    <div class="mt-3">
        <a href="edit.php?id=<?php echo $module->id; ?>" class="btn btn-primary">Modifica</a>
        <a href="index.php" class="btn btn-secondary">Torna all'elenco</a>
    </div>
</div>
<?php include '../footer.php'; ?>
