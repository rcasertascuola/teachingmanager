<?php
require_once '../src/Database.php';
require_once '../src/Competenza.php';
require_once '../src/TipologiaCompetenza.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../login.php');
    exit;
}

$competenze = Competenza::findAll();
$tipologie = TipologiaCompetenza::findAll();
$tipologia_map = [];
foreach ($tipologie as $t) {
    $tipologia_map[$t->id] = $t->nome;
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione Competenze</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
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
        <a href="../dashboard.php" class="btn btn-secondary">Torna alla Dashboard</a>
    </div>
</body>
</html>
