<?php
require_once '../src/Database.php';
require_once '../src/Exercise.php';
include '../header.php';

// Auth check - only teachers can manage exercises
if ($_SESSION["role"] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}


// Pagination settings
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);
$limit = 10; // 10 exercises per page
$offset = ($page - 1) * $limit;

// Get the database connection
$db = Database::getInstance()->getConnection();
$exercise_manager = new Exercise($db);

// For now, we don't have search.
$total_exercises = $exercise_manager->countAll();
$exercises = $exercise_manager->findAll($limit, $offset);

$total_pages = ceil($total_exercises / $limit);

?>

    <div class="container mt-4">
        <?php
        // Feedback messages from other pages
        if (isset($_SESSION['feedback'])) {
            $feedback = $_SESSION['feedback'];
            echo '<div class="alert alert-' . htmlspecialchars($feedback['type']) . ' alert-dismissible fade show" role="alert">';
            echo htmlspecialchars($feedback['message']);
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo '</div>';
            unset($_SESSION['feedback']);
        }
        ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2">Gestione Esercizi</h1>
            <a href="edit.php" class="btn btn-primary"><i class="fas fa-plus"></i> Aggiungi Nuovo Esercizio</a>
        </div>

        <div class="card mb-4">
            <div class="card-header">Importa da JSON</div>
            <div class="card-body">
                <form action="import.php" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="jsonFile" class="form-label">Seleziona file JSON</label>
                        <input class="form-control" type="file" id="jsonFile" name="jsonFile" accept="application/json" required>
                    </div>
                    <button type="submit" class="btn btn-success"><i class="fas fa-upload"></i> Carica File</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                Elenco Esercizi (Pagina <?php echo $page; ?> di <?php echo $total_pages; ?>)
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">Titolo</th>
                                <th scope="col">Tipo</th>
                                <th scope="col">Stato</th>
                                <th scope="col">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($exercises)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">Nessun esercizio trovato.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($exercises as $exercise): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($exercise->title); ?></td>
                                        <td><?php echo htmlspecialchars($exercise->type); ?></td>
                                        <td>
                                            <?php if ($exercise->enabled): ?>
                                                <span class="badge bg-success">Abilitato</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Disabilitato</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="view.php?id=<?php echo $exercise->id; ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                            <a href="edit.php?id=<?php echo $exercise->id; ?>" class="btn btn-sm btn-warning"><i class="fas fa-pencil-alt"></i></a>
                                            <a href="delete.php?id=<?php echo $exercise->id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Sei sicuro di voler cancellare questo esercizio?');"><i class="fas fa-trash"></i></a>
                                            <!-- TODO: Add enable/disable toggle -->
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if ($total_pages > 1): ?>
            <div class="card-footer">
                <nav aria-label="Navigazione pagine">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>">Precedente</a>
                        </li>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>

                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>">Successiva</a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>

<?php include '../footer.php'; ?>
