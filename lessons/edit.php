<?php
session_start();
// Auth check
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'teacher') {
    header("location: ../login.php");
    exit;
}

require_once '../src/Database.php';
require_once '../src/Lesson.php';

$lesson = null;
$pageTitle = 'Aggiungi Nuova Lezione';
$formAction = 'save.php';

// Check if we are editing an existing lesson
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $lesson = Lesson::findById((int)$_GET['id']);
    if ($lesson) {
        $pageTitle = 'Modifica Lezione';
    } else {
        // Lesson not found, maybe show an error or redirect
        header("location: index.php");
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../dashboard.php">Gestionale Studio</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="h2 mb-4"><?php echo $pageTitle; ?></h1>

        <div class="card">
            <div class="card-body">
                <form action="<?php echo $formAction; ?>" method="post">
                    <?php if ($lesson && $lesson->id): ?>
                        <input type="hidden" name="id" value="<?php echo $lesson->id; ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="title" class="form-label">Titolo</label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($lesson->title ?? ''); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="tags" class="form-label">Tags (separati da virgola)</label>
                        <input type="text" class="form-control" id="tags" name="tags" value="<?php echo htmlspecialchars($lesson->tags ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">Contenuto (Wikitext)</label>
                        <div id="wikitext-toolbar" class="btn-toolbar mb-2" role="toolbar">
                            <div class="btn-group btn-group-sm me-2">
                                <button type="button" class="btn btn-outline-secondary" onclick="wrapText('bold')"><b>B</b></button>
                                <button type="button" class="btn btn-outline-secondary" onclick="wrapText('italic')"><i>I</i></button>
                            </div>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary" onclick="wrapText('internal-link')">Link Interno</button>
                                <button type="button" class="btn btn-outline-secondary" onclick="wrapText('external-link')">Link Esterno</button>
                            </div>
                        </div>
                        <textarea class="form-control" id="content" name="content" rows="15" required><?php echo htmlspecialchars($lesson->content ?? ''); ?></textarea>
                    </div>

                    <a href="index.php" class="btn btn-secondary">Annulla</a>
                    <button type="submit" class="btn btn-primary">Salva Lezione</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function wrapText(style) {
            const textarea = document.getElementById('content');
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selectedText = textarea.value.substring(start, end);
            let replacement = selectedText;

            switch (style) {
                case 'bold':
                    replacement = "'''" + selectedText + "'''";
                    break;
                case 'italic':
                    replacement = "''" + selectedText + "''";
                    break;
                case 'internal-link':
                    replacement = "[[" + (selectedText || 'Titolo Pagina') + "]]";
                    break;
                case 'external-link':
                    replacement = "[" + (selectedText || 'https://example.com') + " Testo del link]";
                    break;
            }

            textarea.setRangeText(replacement, start, end);
            textarea.focus();

            // Move cursor to a logical position after insertion
            if (selectedText) {
                 textarea.selectionStart = textarea.selectionEnd = start + replacement.length;
            } else {
                if(style === 'internal-link') {
                    textarea.selectionStart = start + 2;
                    textarea.selectionEnd = start + 2 + 'Titolo Pagina'.length;
                } else if (style === 'external-link') {
                     textarea.selectionStart = start + 1;
                     textarea.selectionEnd = start + 1 + 'https://example.com'.length;
                }
            }
        }
    </script>
</body>
</html>
