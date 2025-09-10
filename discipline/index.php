<?php
require_once '../src/Database.php';
require_once '../src/Disciplina.php';
include '../header.php';

// Teacher role check
if ($_SESSION['role'] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}

$discipline = Disciplina::findAll();
?>
    <div class="container mt-5">
        <h2>Discipline</h2>
        <a href="edit.php" class="btn btn-success mb-3"><i class="fas fa-plus"></i> Crea Nuova Disciplina</a>
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php if ($_GET['success'] == 'create'): echo "Disciplina creata con successo!"; endif; ?>
                <?php if ($_GET['success'] == 'update'): echo "Disciplina aggiornata con successo!"; endif; ?>
                <?php if ($_GET['success'] == 'delete'): echo "Disciplina eliminata con successo!"; endif; ?>
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
                <?php foreach ($discipline as $disciplina): ?>
                <tr>
                    <td><?php echo htmlspecialchars($disciplina->id); ?></td>
                    <td><?php echo htmlspecialchars($disciplina->nome); ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo $disciplina->id; ?>" class="btn btn-primary btn-sm"><i class="fas fa-pencil-alt"></i></a>
                        <a href="delete.php?id=<?php echo $disciplina->id; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Sei sicuro di voler eliminare questa disciplina?');"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php include '../footer.php'; ?>
