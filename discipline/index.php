<?php
require_once '../src/Database.php';
require_once '../src/Disciplina.php';

session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] !== 'teacher') {
    header('Location: ../login.php');
    exit;
}

$discipline = Disciplina::findAll();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione Discipline</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Discipline</h2>
        <a href="edit.php" class="btn btn-success mb-3">Crea Nuova Disciplina</a>
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
                        <a href="edit.php?id=<?php echo $disciplina->id; ?>" class="btn btn-primary btn-sm">Modifica</a>
                        <a href="delete.php?id=<?php echo $disciplina->id; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Sei sicuro di voler eliminare questa disciplina?');">Elimina</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="../dashboard.php" class="btn btn-secondary">Torna alla Dashboard</a>
    </div>
</body>
</html>
