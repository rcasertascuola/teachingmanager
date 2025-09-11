<?php
require_once '../src/Database.php';
require_once '../src/Exercise.php';
require_once '../src/exercise_parser.php';
include '../header.php';

// Auth check - only teachers can correct exercises
if ( $_SESSION["role"] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}


$exercise = null;
$answers = [];

// Get the database connection
$db = Database::getInstance()->getConnection();
$exercise_manager = new Exercise($db);

if (isset($_GET['id'])) {
    $exercise_id = (int)$_GET['id'];
    $exercise = $exercise_manager->findById($exercise_id);
    if ($exercise) {
        $answers = $exercise_manager->getStudentAnswers($exercise_id);
    }
}

?>

    <div class="container mt-4">
        <?php if ($exercise): ?>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2">Correzione: <?php echo htmlspecialchars($exercise->title); ?></h1>
                <a href="index.php" class="btn btn-secondary">Torna a Gestione Esercizi</a>
            </div>

            <!-- Optional: Display the original exercise content -->
            <div class="card mb-4">
                <div class="card-header">Contenuto Esercizio</div>
                <div class="card-body">
                    <pre><?php echo htmlspecialchars($exercise->content); ?></pre>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Risposte degli Studenti</div>
                <div class="card-body">
                    <?php if (empty($answers)): ?>
                        <p class="text-center">Nessuno studente ha ancora risposto a questo esercizio.</p>
                    <?php else: ?>
                        <form action="save_correction.php" method="post">
                             <input type="hidden" name="exercise_id" value="<?php echo $exercise->id; ?>">
                            <?php foreach ($answers as $answer): ?>
                                <div class="mb-4 border-bottom pb-3">
                                    <h5>Studente: <?php echo htmlspecialchars($answer['username']); ?></h5>
                                    <p><strong>Risposta data:</strong></p>
                                    <div class="p-3 bg-light border rounded">
                                        <?php echo parse_exercise_wikitext($exercise, $answer, true); ?>
                                    </div>

                                    <div class="row align-items-end mt-3">
                                        <div class="col-md-3">
                                            <label for="score_<?php echo $answer['id']; ?>" class="form-label">Punteggio</label>
                                            <input type="number" step="0.01" class="form-control"
                                                   id="score_<?php echo $answer['id']; ?>"
                                                   name="scores[<?php echo $answer['id']; ?>]"
                                                   value="<?php echo htmlspecialchars($answer['score'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <button type="submit" class="btn btn-primary">Salva Correzioni</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

        <?php else: ?>
            <div class="alert alert-danger">L'esercizio richiesto non è stato trovato.</div>
        <?php endif; ?>
    </div>

<?php include '../footer.php'; ?>
