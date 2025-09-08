<?php
require_once '../src/init.php';
// Ensure the user is a logged-in teacher
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'teacher') {
    header("location: ../login.php");
    exit;
}

require_once '../src/Database.php';
require_once '../src/Lesson.php';

// Fetch all lessons to populate the dropdown. Using a high limit to get all lessons.
$lessons = Lesson::findAll(9999, 0);

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selezione Lezione per Riscontro</title>
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
            <h1>Selezione Lezione per Riscontro</h1>
            <a href="../dashboard.php" class="btn btn-secondary">Torna alla Dashboard</a>
        </div>

        <div class="card">
            <div class="card-header">
                Scegli una lezione
            </div>
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
