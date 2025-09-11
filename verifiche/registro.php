<?php
require_once '../src/Database.php';
require_once '../src/Verifica.php';
require_once '../src/User.php';
include '../header.php';

// Auth check
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}

$verifica = null;
$students = [];
$records = []; // Placeholder for existing records

if (isset($_GET['id'])) {
    $verifica_id = (int)$_GET['id'];
    // Get database connection
    $db = Database::getInstance()->getConnection();
    $verifica_manager = new Verifica($db);
    $verifica = $verifica_manager->findById($verifica_id);

    // Get all students
    $user = new User($db);
    $students = $user->findAllStudents();

    if ($verifica) {
        $records = $verifica->getRegistroRecords();
    }

} else {
    echo "<div class='alert alert-warning'>Nessuna verifica specificata.</div>";
    include '../footer.php';
    exit;
}

if (!$verifica) {
    echo "<div class='alert alert-danger'>Verifica non trovata.</div>";
    include '../footer.php';
    exit;
}

?>

<div class="container mt-4">
    <h1 class="h2 mb-2">Registro Valutazioni: <?php echo htmlspecialchars($verifica->titolo); ?></h1>
    <a href="view.php?id=<?php echo $verifica->id; ?>" class="btn btn-sm btn-outline-secondary mb-4">Torna alla Verifica</a>

    <div class="row">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="h5 mb-0">Aggiungi Nuova Valutazione</h3>
                </div>
                <div class="card-body">
                    <form action="save_registro.php" method="post">
                        <input type="hidden" name="verifica_id" value="<?php echo $verifica->id; ?>">
                        <div class="mb-3">
                            <label for="user_id" class="form-label">Studente</label>
                            <select class="form-select" id="user_id" name="user_id" required>
                                <option value="">Seleziona uno studente</option>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?php echo $student['id']; ?>"><?php echo htmlspecialchars($student['username']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="data_svolgimento" class="form-label">Data Svolgimento</label>
                            <input type="date" class="form-control" id="data_svolgimento" name="data_svolgimento" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="punteggio_totale" class="form-label">Punteggio (su 20)</label>
                            <input type="number" class="form-control" id="punteggio_totale" name="punteggio_totale" step="0.01" min="0" max="20" required>
                        </div>
                        <div class="mb-3">
                            <label for="note" class="form-label">Note</label>
                            <textarea class="form-control" id="note" name="note" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Salva Valutazione</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="h5 mb-0">Valutazioni Inserite</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Studente</th>
                                    <th>Data</th>
                                    <th>Punteggio</th>
                                    <th>Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($records)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">Nessuna valutazione inserita.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($records as $record): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($record['username']); ?></td>
                                            <td><?php echo htmlspecialchars($record['data_svolgimento']); ?></td>
                                            <td><?php echo htmlspecialchars(number_format($record['punteggio_totale'], 2)); ?></td>
                                            <td><?php echo nl2br(htmlspecialchars($record['note'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
