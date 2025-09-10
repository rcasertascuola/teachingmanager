<?php
require_once '../src/Database.php';
require_once '../src/Abilita.php';
include '../header.php';

// Additional check for teacher role
if ($_SESSION['role'] !== 'teacher') {
    // Redirect or show an error message
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}

$abilita_items = Abilita::findAll();
?>
    <div class="container mt-5">
        <h2>Abilità</h2>
        <a href="edit.php" class="btn btn-success mb-3">Crea Nuova Abilità</a>
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php if ($_GET['success'] == 'create'): echo "Abilità creata con successo!"; endif; ?>
                <?php if ($_GET['success'] == 'update'): echo "Abilità aggiornata con successo!"; endif; ?>
                <?php if ($_GET['success'] == 'delete'): echo "Abilità eliminata con successo!"; endif; ?>
            </div>
        <?php endif; ?>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Tipo</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($abilita_items as $abilita): ?>
                <tr>
                    <td><?php echo htmlspecialchars($abilita->id); ?></td>
                    <td><?php echo htmlspecialchars($abilita->nome); ?></td>
                    <td><?php echo htmlspecialchars(ucfirst($abilita->tipo)); ?></td>
                    <td>
                        <a href="view.php?id=<?php echo $abilita->id; ?>" class="btn btn-info btn-sm">Vedi</a>
                        <a href="edit.php?id=<?php echo $abilita->id; ?>" class="btn btn-primary btn-sm">Modifica</a>
                        <a href="delete.php?id=<?php echo $abilita->id; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Sei sicuro di voler eliminare questa abilità?');">Elimina</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php include '../footer.php'; ?>
