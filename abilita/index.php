<?php
require_once '../src/Database.php';
require_once '../src/Abilita.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../login.php');
    exit;
}

$abilita_items = Abilita::findAll();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione Abilità</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
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
        <a href="../dashboard.php" class="btn btn-secondary">Torna alla Dashboard</a>
    </div>
</body>
</html>
