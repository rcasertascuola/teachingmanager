<?php
require_once '../src/Database.php';
require_once '../src/Exercise.php';
require_once '../src/exercise_parser.php';
include '../header.php';


$exercise = null;
if (isset($_GET['id'])) {
    $exercise = Exercise::findById((int)$_GET['id']);
}

// Students can only view enabled exercises
if ($_SESSION['role'] === 'student' && $exercise && !$exercise->enabled) {
    $exercise = null; // Pretend it doesn't exist
}

// Fetch student's previous answer if it exists
$previous_answer = null;
if ($exercise && $_SESSION['role'] === 'student') {
    // Need a new method to get a single answer for a specific user and exercise
    // Let's add getStudentAnswer($userId, $exerciseId) to Exercise class
    $answers = Exercise::getStudentAnswers($exercise->id);
    foreach ($answers as $answer) {
        if ($answer['user_id'] == $_SESSION['id']) {
            $previous_answer = $answer;
            break;
        }
    }
}

?>

    <div class="container mt-4">
        <?php if ($exercise): ?>
            <div class="card">
                <div class="card-header">
                    <h1 class="h2 mb-0"><?php echo htmlspecialchars($exercise->title); ?></h1>
                    <small class="text-muted">Tipo: <?php echo htmlspecialchars($exercise->type); ?></small>
                </div>
                <div class="card-body">
                    <?php if ($previous_answer): ?>
                        <div class="alert alert-info">
                            Hai già completato questo esercizio. Visualizza la tua risposta qui sotto.
                            <!-- TODO: Display the answer and correction -->
                        </div>
                    <?php else: ?>
                        <form action="save_answer.php" method="post">
                            <input type="hidden" name="exercise_id" value="<?php echo $exercise->id; ?>">

                            <div id="exercise-content">
                                <?php echo parse_exercise_wikitext($exercise, $previous_answer); ?>
                            </div>

                            <hr>
                            <?php if (!$previous_answer): ?>
                                <button type="submit" class="btn btn-primary">Invia Risposta</button>
                            <?php else: ?>
                                <p><strong>Punteggio:</strong>
                                <?php
                                if ($previous_answer['score'] !== null) {
                                    echo htmlspecialchars($previous_answer['score']);
                                } else {
                                    echo '<span class="text-muted">In attesa di correzione</span>';
                                }
                                ?>
                                </p>
                                <a href="view.php?id=<?php echo $exercise->id; ?>" class="btn btn-secondary">Ricarica per aggiornamenti</a>
                            <?php endif; ?>
                        </form>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <?php if ($_SESSION['role'] === 'teacher'): ?>
                        <a href="edit.php?id=<?php echo $exercise->id; ?>" class="btn btn-warning">Modifica Esercizio</a>
                        <a href="correction.php?id=<?php echo $exercise->id; ?>" class="btn btn-success">Correggi Risposte</a>
                    <?php endif; ?>
                     <a href="<?php echo $_SESSION['role'] === 'teacher' ? 'index.php' : '../dashboard.php'; ?>" class="btn btn-secondary">Indietro</a>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">
                L'esercizio richiesto non è stato trovato o non è attualmente abilitato.
            </div>
        <?php endif; ?>
    </div>

<?php include '../footer.php'; ?>
