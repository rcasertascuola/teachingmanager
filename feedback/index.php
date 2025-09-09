<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login.php");
    exit;
}

require_once '../src/Database.php';
require_once '../src/Lesson.php';

$is_teacher = $_SESSION["role"] === 'teacher';
$lessons = [];

if ($is_teacher) {
    // For teachers, fetch all lessons for the dropdown
    $lessons = Lesson::findAll(9999, 0);
} else {
    // For students, fetch only the lessons they have interacted with
    $lessons = Lesson::findForStudent($_SESSION['id']);
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riscontro Lezioni</title>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><?php echo $is_teacher ? 'Selezione Lezione per Riscontro' : 'I Tuoi Riscontri per Lezione'; ?></h1>
            <a href="../dashboard.php" class="btn btn-secondary">Torna alla Dashboard</a>
        </div>

        <?php if ($is_teacher): ?>
            <div class="card">
                <div class="card-header">Scegli una lezione</div>
                <div class="card-body">
                    <p>Scegli una lezione dal menu a tendina per visualizzare i dati di riscontro degli alunni.</p>
                    <form action="../lessons/feedback.php" method="get">
                        <div class="mb-3">
                            <label for="lesson_id" class="form-label">Lezione</label>
                            <select class="form-select" id="lesson_id" name="id" required>
                                <option value="" disabled selected>-- Scegli una lezione --</option>
                                <?php if (empty($lessons)): ?>
                                    <option value="" disabled>Nessuna lezione disponibile</option>
                                <?php else: ?>
                                    <?php foreach ($lessons as $lesson): ?>
                                        <option value="<?php echo $lesson->id; ?>"><?php echo htmlspecialchars($lesson->title); ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Visualizza Riscontro</button>
                    </form>
                </div>
            </div>
        <?php else: // Student view ?>
            <div class="card">
                <div class="card-header">Lezioni con i tuoi riscontri</div>
                <div class="card-body">
                    <p>Qui puoi vedere un elenco delle lezioni in cui hai lasciato annotazioni, sottolineature o altri riscontri. Clicca su una lezione per rivedere i tuoi contributi.</p>
                    <?php if (empty($lessons)): ?>
                        <div class="alert alert-info">Non hai ancora fornito riscontri per nessuna lezione. Inizia a interagire con una lezione per vederla qui.</div>
                    <?php else: ?>
                        <ul class="list-group">
                            <?php foreach ($lessons as $lesson): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo htmlspecialchars($lesson->title); ?>
                                    <a href="../lessons/feedback.php?id=<?php echo $lesson->id; ?>" class="btn btn-sm btn-outline-primary">Vedi i tuoi riscontri</a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
