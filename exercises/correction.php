<?php
session_start();
// Auth check - only teachers can correct exercises
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'teacher') {
    header("location: ../login.php");
    exit;
}

require_once '../src/Database.php';
require_once '../src/Exercise.php';
require_once '../src/exercise_parser.php';

$exercise = null;
$answers = [];

if (isset($_GET['id'])) {
    $exercise_id = (int)$_GET['id'];
    $exercise = Exercise::findById($exercise_id);
    if ($exercise) {
        $answers = Exercise::getStudentAnswers($exercise_id);
    }
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correzione Esercizio: <?php echo $exercise ? htmlspecialchars($exercise->title) : 'Esercizio non trovato'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../dashboard.php">Gestionale Studio</a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
