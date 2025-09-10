<?php
session_start();
// Auth check
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'teacher') {
    header("location: ../login.php");
    exit;
}

require_once '../src/Database.php';
require_once '../src/Lesson.php';
require_once '../src/Module.php';
require_once '../src/Conoscenza.php';
require_once '../src/Abilita.php';

$lesson = null;
$modules = Module::findAll();
$all_conoscenze = Conoscenza::findAll();
$all_abilita = Abilita::findAll();
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
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
                        <label for="module_id" class="form-label">Modulo di appartenenza</label>
                        <select class="form-select" id="module_id" name="module_id">
                            <option value="">Nessun modulo</option>
                            <?php foreach ($modules as $module): ?>
                                <option value="<?php echo $module->id; ?>" <?php echo (isset($lesson) && $lesson->module_id == $module->id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($module->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="tags" class="form-label">Tags (separati da virgola)</label>
                        <input type="text" class="form-control" id="tags" name="tags" value="<?php echo htmlspecialchars($lesson->tags ?? ''); ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="conoscenze" class="form-label">Conoscenze Collegate</label>
                                <select multiple class="form-select" id="conoscenze" name="conoscenze[]" size="8">
                                    <?php foreach ($all_conoscenze as $conoscenza): ?>
                                        <option value="<?php echo $conoscenza->id; ?>" <?php echo ($lesson && in_array($conoscenza->id, $lesson->conoscenze)) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($conoscenza->nome); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="abilita" class="form-label">Abilità Collegate</label>
                                <select multiple class="form-select" id="abilita" name="abilita[]" size="8">
                                    <?php foreach ($all_abilita as $item): ?>
                                        <option value="<?php echo $item->id; ?>" <?php echo ($lesson && in_array($item->id, $lesson->abilita)) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($item->nome); ?> (<?php echo $item->tipo; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">Contenuto (Wikitext)</label>
                        <div id="wikitext-toolbar" class="btn-toolbar mb-2 flex-wrap" role="toolbar">
                            <div class="btn-group btn-group-sm me-2 mb-2">
                                <button type="button" class="btn btn-outline-secondary" onclick="wrapText('bold')" title="Grassetto"><b>B</b></button>
                                <button type="button" class="btn btn-outline-secondary" onclick="wrapText('italic')" title="Corsivo"><i>I</i></button>
                                <button type="button" class="btn btn-outline-secondary" onclick="wrapText('color')" title="Colore Testo"><i class="bi bi-palette-fill"></i></button>
                            </div>
                            <div class="btn-group btn-group-sm me-2 mb-2">
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Titoli
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="wrapText('h1')"><h1>Titolo 1</h1></a></li>
                                        <li><a class="dropdown-item" href="#" onclick="wrapText('h2')"><h2>Titolo 2</h2></a></li>
                                        <li><a class="dropdown-item" href="#" onclick="wrapText('h3')"><h3>Titolo 3</h3></a></li>
                                    </ul>
                                </div>
                            </div>
                             <div class="btn-group btn-group-sm me-2 mb-2">
                                <button type="button" class="btn btn-outline-secondary" onclick="wrapText('ul')" title="Elenco puntato"><i class="bi bi-list-ul"></i></button>
                                <button type="button" class="btn btn-outline-secondary" onclick="wrapText('ol')" title="Elenco numerato"><i class="bi bi-list-ol"></i></button>
                                <button type="button" class="btn btn-outline-secondary" onclick="wrapText('hr')" title="Linea orizzontale"><i class="bi bi-hr"></i></button>
                            </div>
                            <div class="btn-group btn-group-sm me-2 mb-2">
                                <button type="button" class="btn btn-outline-secondary" onclick="wrapText('internal-link')" title="Link Interno"><i class="bi bi-link-45deg"></i> Interno</button>
                                <button type="button" class="btn btn-outline-secondary" onclick="wrapText('external-link')" title="Link Esterno"><i class="bi bi-link-45deg"></i> Esterno</button>
                            </div>
                            <div class="btn-group btn-group-sm me-2 mb-2">
                                <button type="button" class="btn btn-outline-secondary" onclick="wrapText('image')" title="Immagine"><i class="bi bi-image"></i></button>
                                <button type="button" class="btn btn-outline-secondary" onclick="wrapText('video')" title="Video"><i class="bi bi-camera-video"></i></button>
                                <button type="button" class="btn btn-outline-secondary" onclick="wrapText('table')" title="Tabella"><i class="bi bi-table"></i></button>
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
        function wrapText(style, value = null) {
            const textarea = document.getElementById('content');
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selectedText = textarea.value.substring(start, end);
            let prefix = '';
            let suffix = '';
            let replacement = selectedText;

            switch (style) {
                case 'bold':
                    prefix = "'''";
                    suffix = "'''";
                    break;
                case 'italic':
                    prefix = "''";
                    suffix = "''";
                    break;
                case 'h1':
                    prefix = "\n= ";
                    suffix = " =\n";
                    break;
                case 'h2':
                    prefix = "\n== ";
                    suffix = " ==\n";
                    break;
                case 'h3':
                    prefix = "\n=== ";
                    suffix = " ===\n";
                    break;
                case 'hr':
                    replacement = "\n----\n";
                    break;
                case 'ul':
                    replacement = selectedText.split('\n').map(line => `* ${line}`).join('\n');
                     if(!selectedText) replacement = "* Elemento 1\n* Elemento 2";
                    break;
                case 'ol':
                    replacement = selectedText.split('\n').map(line => `# ${line}`).join('\n');
                    if(!selectedText) replacement = "# Elemento 1\n# Elemento 2";
                    break;
                case 'color':
                    const color = prompt("Inserisci un colore (es. red, #ff0000):", "red");
                    if (color) {
                        prefix = `{{color:${color}|`;
                        suffix = `}}`;
                    }
                    break;
                case 'internal-link':
                    prefix = "[[";
                    suffix = "]]";
                    if (!selectedText) replacement = "Titolo Pagina";
                    break;
                case 'external-link':
                    const urlExt = selectedText.startsWith('http') ? selectedText : prompt("Inserisci URL:", "https://");
                    if(urlExt) {
                         replacement = `[${urlExt} Testo del link]`;
                    }
                    break;
                case 'image':
                    const imgUrl = prompt("Inserisci l'URL dell'immagine:");
                    if (imgUrl) {
                        const caption = prompt("Inserisci una didascalia:", "didascalia");
                        replacement = `[[Image:${imgUrl}|${caption}]]`;
                    }
                    break;
                case 'video':
                    const videoUrl = prompt("Inserisci l'URL del video (YouTube):");
                    if (videoUrl) {
                        replacement = `[[Video:${videoUrl}]]`;
                    }
                    break;
                case 'table':
                    replacement = "\n| Header 1 | Header 2 |\n|----------|----------|\n| Cella 1  | Cella 2  |\n| Cella 3  | Cella 4  |\n";
                    break;
            }

            if (prefix || suffix) {
                replacement = prefix + selectedText + suffix;
            }

            textarea.setRangeText(replacement, start, end);
            textarea.focus();

            // Adjust cursor position
            textarea.selectionStart = start + replacement.length;
            textarea.selectionEnd = start + replacement.length;

             // A more intelligent cursor placement for some tags
            if (!selectedText) {
                if(style.startsWith('h')) {
                     textarea.selectionStart = start + prefix.length -1;
                     textarea.selectionEnd = start + prefix.length -1;
                } else if (style === 'internal-link') {
                    textarea.selectionStart = start + prefix.length;
                    textarea.selectionEnd = start + prefix.length + "Titolo Pagina".length;
                } else if (style === 'external-link') {
                    textarea.selectionStart = start + replacement.indexOf(']') + 1;
                } else if (style === 'color') {
                    textarea.selectionStart = start + prefix.length;
                    textarea.selectionEnd = end + prefix.length;
                }
            }
        }
    </script>
</body>
</html>
