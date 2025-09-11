<?php
require_once '../src/Database.php';
require_once '../src/User.php';
include '../header.php';

// Check if user is a teacher
if ($_SESSION['role'] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$users = $user->findAll();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">Gestione Utenti</h1>
        <a href="edit.php" class="btn btn-primary"><i class="fas fa-plus"></i></a>
    </div>

    <div class="card">
        <div class="card-header">
            Elenco Utenti
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">Username</th>
                            <th scope="col">Ruolo</th>
                            <th scope="col">Classe</th>
                            <th scope="col">Corso</th>
                            <th scope="col">Anno Scolastico</th>
                            <th scope="col">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($users->rowCount() === 0): ?>
                            <tr>
                                <td colspan="6" class="text-center">Nessun utente trovato.</td>
                            </tr>
                        <?php else: ?>
                            <?php while ($row = $users->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><?php echo htmlspecialchars($row['role']); ?></td>
                                    <td><?php echo htmlspecialchars($row['classe'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($row['corso'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($row['anno_scolastico'] ?? 'N/A'); ?></td>
                                    <td>
                                        <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-pencil-alt"></i></a>
                                        <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Sei sicuro di voler eliminare questo utente?');"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
