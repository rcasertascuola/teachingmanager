<?php
require_once '../src/Database.php';
require_once '../src/TipologiaCompetenza.php';
include '../header.php';

if ($_SESSION['role'] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}

$tipologie = TipologiaCompetenza::findAll();
?>
    <div class="container mt-5">
        <h2>Tipologie di Competenze</h2>
        <a href="edit.php" class="btn btn-success mb-3">Crea Nuova Tipologia</a>
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php if ($_GET['success'] == 'create'): echo "Tipologia creata con successo!"; endif; ?>
                <?php if ($_GET['success'] == 'update'): echo "Tipologia aggiornata con successo!"; endif; ?>
                <?php if ($_GET['success'] == 'delete'): echo "Tipologia eliminata con successo!"; endif; ?>
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
                <?php foreach ($tipologie as $tipologia): ?>
                <tr>
                    <td><?php echo htmlspecialchars($tipologia->id); ?></td>
                    <td><?php echo htmlspecialchars($tipologia->nome); ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo $tipologia->id; ?>" class="btn btn-primary btn-sm">Modifica</a>
                        <a href="delete.php?id=<?php echo $tipologia->id; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Sei sicuro di voler eliminare questa tipologia?');">Elimina</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php include '../footer.php'; ?>
