<?php
session_start();
// Auth check
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'teacher') {
    header("location: ../login.php");
    exit;
}

require_once '../src/Database.php';
require_once '../src/Lesson.php';
require_once '../src/wikitext_parser.php';

$lesson = null;
if (isset($_GET['id'])) {
    $lesson = Lesson::findById((int)$_GET['id']);
} elseif (isset($_GET['title'])) {
    $lesson = Lesson::findByTitle(urldecode($_GET['title']));
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lesson ? htmlspecialchars($lesson->title) : 'Lezione non trovata'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .wikitext-content h2 {
            border-bottom: 1px solid #dee2e6;
            padding-bottom: .5rem;
            margin-top: 1.5rem;
        }
        .wikitext-content h3 {
            margin-top: 1rem;
        }
        .wikitext-content p {
            line-height: 1.6;
        }
        .wikitext-content hr {
            margin: 2rem 0;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../dashboard.php">Gestionale Studio</a>
            <div class="collapse navbar-collapse">
                 <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Torna a Elenco Lezioni</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if ($lesson): ?>
            <div class="card">
                <div class="card-header">
                    <h1 class="h2 mb-0"><?php echo htmlspecialchars($lesson->title); ?></h1>
                    <?php if (!empty($lesson->tags)): ?>
                        <small class="text-muted">Tags: <?php echo htmlspecialchars($lesson->tags); ?></small>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="wikitext-content">
                        <?php echo parse_wikitext($lesson->content); ?>
                    </div>
                </div>
                <div class="card-footer">
                     <a href="edit.php?id=<?php echo $lesson->id; ?>" class="btn btn-primary">Modifica Lezione</a>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger" role="alert">
                <h4 class="alert-heading">Errore</h4>
                <p>La lezione richiesta non è stata trovata.</p>
                <hr>
                <a href="index.php" class="btn btn-secondary">Torna all'elenco</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
