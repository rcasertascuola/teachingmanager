<?php
require_once '../src/Database.php';
require_once '../src/Conoscenza.php';
include '../header.php';

// Teacher role check
if ($_SESSION['role'] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}

$conoscenze = Conoscenza::findAll();
?>
    <div class="container mt-5">
        <h2>Conoscenze</h2>
        <a href="edit.php" class="btn btn-success mb-3">Crea Nuova Conoscenza</a>
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php if ($_GET['success'] == 'create'): echo "Conoscenza creata con successo!"; endif; ?>
                <?php if ($_GET['success'] == 'update'): echo "Conoscenza aggiornata con successo!"; endif; ?>
                <?php if ($_GET['success'] == 'delete'): echo "Conoscenza eliminata con successo!"; endif; ?>
            </div>
        <?php endif; ?>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($conoscenze as $conoscenza): ?>
                <tr>
                    <td><?php echo htmlspecialchars($conoscenza->id); ?></td>
                    <td><?php echo htmlspecialchars($conoscenza->nome); ?></td>
                    <td>
                        <a href="view.php?id=<?php echo $conoscenza->id; ?>" class="btn btn-info btn-sm">Vedi</a>
                        <a href="edit.php?id=<?php echo $conoscenza->id; ?>" class="btn btn-primary btn-sm">Modifica</a>
                        <a href="delete.php?id=<?php echo $conoscenza->id; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Sei sicuro di voler eliminare questa conoscenza?');">Elimina</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php include '../footer.php'; ?>
