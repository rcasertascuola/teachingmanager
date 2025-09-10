<?php
require_once '../src/Database.php';
require_once '../src/Competenza.php';
require_once '../src/TipologiaCompetenza.php';
include '../header.php';

// Additional check for teacher role
if ($_SESSION['role'] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}

$competenze = Competenza::findAll();
$tipologie = TipologiaCompetenza::findAll();
$tipologia_map = [];
foreach ($tipologie as $t) {
    $tipologia_map[$t->id] = $t->nome;
}

?>
    <div class="container mt-5">
        <h2>Competenze</h2>
        <a href="edit.php" class="btn btn-success mb-3">Crea Nuova Competenza</a>
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php if ($_GET['success'] == 'create'): echo "Competenza creata con successo!"; endif; ?>
                <?php if ($_GET['success'] == 'update'): echo "Competenza aggiornata con successo!"; endif; ?>
                <?php if ($_GET['success'] == 'delete'): echo "Competenza eliminata con successo!"; endif; ?>
            </div>
        <?php endif; ?>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Tipologia</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($competenze as $competenza): ?>
                <tr>
                    <td><?php echo htmlspecialchars($competenza->id); ?></td>
                    <td><?php echo htmlspecialchars($competenza->nome); ?></td>
                    <td><?php echo htmlspecialchars($tipologia_map[$competenza->tipologia_id] ?? 'N/A'); ?></td>
                    <td>
                        <a href="view.php?id=<?php echo $competenza->id; ?>" class="btn btn-info btn-sm">Vedi</a>
                        <a href="edit.php?id=<?php echo $competenza->id; ?>" class="btn btn-primary btn-sm">Modifica</a>
                        <a href="delete.php?id=<?php echo $competenza->id; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Sei sicuro di voler eliminare questa competenza?');">Elimina</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php include '../footer.php'; ?>
