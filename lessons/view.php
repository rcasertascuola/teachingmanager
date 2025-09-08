<?php
require_once '../src/init.php';
// Auth check
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
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

$student_data = [];
if ($lesson && isset($_SESSION['id']) && $_SESSION['role'] === 'student') {
    $student_data = Lesson::getStudentData($_SESSION['id'], $lesson->id);
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
        .highlight {
            background-color: yellow;
        }
        .annotation {
            border-bottom: 2px dotted blue;
            cursor: pointer;
        }
        .question {
            border-bottom: 2px dashed red;
            cursor: pointer;
        }
        .summary {
            background-color: #e9f7fd;
            display: block;
            padding: 10px;
            margin-top: 5px;
            border-left: 5px solid #2196F3;
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
                    <div id="lesson-content" class="wikitext-content">
                        <?php echo parse_wikitext($lesson->content); ?>
                    </div>
                </div>
                <div class="card-footer">
                    <?php if ($_SESSION['role'] === 'teacher'): ?>
                        <a href="edit.php?id=<?php echo $lesson->id; ?>" class="btn btn-primary">Modifica Lezione</a>
                    <?php else: ?>
                        <div id="student-tools" class="d-flex flex-wrap align-items-center gap-2">
                            <button id="highlight-btn" class="btn btn-secondary">Evidenzia</button>
                            <input type="color" id="highlight-color-picker" class="form-control form-control-color" value="#ffff00" title="Scegli un colore per evidenziare">
                            <button id="annotate-btn" class="btn btn-secondary">Annota</button>
                            <button id="question-btn" class="btn btn-secondary">Fai una domanda</button>
                            <button id="summary-btn" class="btn btn-secondary">Aggiungi riassunto</button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Annotation Modal -->
            <div class="modal fade" id="annotation-modal" tabindex="-1" aria-labelledby="annotation-modal-label" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="annotation-modal-label">Aggiungi Annotazione</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="annotation-form">
                                <div class="mb-3">
                                    <label for="annotation-text" class="form-label">Testo dell'annotazione</label>
                                    <textarea class="form-control" id="annotation-text" rows="3"></textarea>
                                </div>
                                <input type="hidden" id="selection-data-annotation">
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                            <button type="button" class="btn btn-primary" id="save-annotation-btn">Salva</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Question Modal -->
            <div class="modal fade" id="question-modal" tabindex="-1" aria-labelledby="question-modal-label" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="question-modal-label">Fai una Domanda</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="question-form">
                                <div class="mb-3">
                                    <label for="question-text" class="form-label">Testo della domanda</label>
                                    <textarea class="form-control" id="question-text" rows="3"></textarea>
                                </div>
                                <input type="hidden" id="selection-data-question">
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                            <button type="button" class="btn btn-primary" id="save-question-btn">Salva Domanda</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Modal -->
            <div class="modal fade" id="summary-modal" tabindex="-1" aria-labelledby="summary-modal-label" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="summary-modal-label">Aggiungi Riassunto</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="summary-form">
                                <div class="mb-3">
                                    <label for="summary-text" class="form-label">Testo del riassunto</label>
                                    <textarea class="form-control" id="summary-text" rows="5"></textarea>
                                </div>
                                <input type="hidden" id="selection-data-summary">
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                            <button type="button" class="btn btn-primary" id="save-summary-btn">Salva Riassunto</button>
                        </div>
                    </div>
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
    <?php if ($_SESSION['role'] === 'student'): ?>
    <script>
    const studentData = <?php echo json_encode($student_data); ?>;

    document.addEventListener('DOMContentLoaded', function () {
        const lessonContent = document.getElementById('lesson-content');
        const highlightBtn = document.getElementById('highlight-btn');
        const questionBtn = document.getElementById('question-btn');
        const summaryBtn = document.getElementById('summary-btn');
        const annotateBtn = document.getElementById('annotate-btn');

        const annotationModal = new bootstrap.Modal(document.getElementById('annotation-modal'));
        const questionModal = new bootstrap.Modal(document.getElementById('question-modal'));
        const summaryModal = new bootstrap.Modal(document.getElementById('summary-modal'));

        let currentSelection = null;

        loadStudentData();

        lessonContent.addEventListener('mouseup', () => {
            const selection = window.getSelection();
            if (selection.rangeCount > 0 && !selection.isCollapsed) {
                currentSelection = selection.getRangeAt(0);
            } else {
                currentSelection = null;
            }
        });

        function loadStudentData() {
            if (typeof studentData !== 'undefined' && studentData.length > 0) {
                // Sort data to apply inner highlights/annotations first
                studentData.sort((a, b) => {
                    if (!a.data.selection || !b.data.selection) return 0;
                    const pathA = a.data.selection.startContainerPath;
                    const pathB = b.data.selection.startContainerPath;
                    return pathB.length - pathA.length;
                });

                studentData.forEach(item => {
                    if (item.type === 'highlight') {
                        applyHighlight(item);
                    } else if (item.type === 'annotation') {
                        applyAnnotation(item);
                    } else if (item.type === 'question') {
                        applyQuestion(item);
                    } else if (item.type === 'summary') {
                        applySummary(item);
                    }
                });
            }
        }

        function saveTextualData(type, text, selectionData) {
            if (text.trim() === '') {
                alert('Il testo non può essere vuoto.');
                return;
            }

            const data = {
                lesson_id: <?php echo $lesson->id; ?>,
                type: type,
                data: {
                    selection: selectionData,
                    text: text
                }
            };

            fetch('save_student_data.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    // Reload to see the new data
                    location.reload();
                } else {
                    alert('Errore nel salvataggio: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Si è verificato un errore di rete.');
            });
        }

        function addDeleteClickHandler(element) {
            element.addEventListener('click', (e) => {
                e.preventDefault(); // Prevent any default action
                e.stopPropagation(); // Stop the event from bubbling up

                if (confirm('Vuoi cancellare questa selezione?')) {
                    const dataId = element.dataset.id;
                    deleteStudentDataItem(dataId, element);
                }
            });
        }

        function deleteStudentDataItem(id, element) {
            fetch('delete_student_data.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    // Reload the page to ensure a clean state
                    location.reload();
                } else {
                    alert('Errore nella cancellazione: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Si è verificato un errore di rete.');
            });
        }

        function deserializeRange(selectionData) {
            try {
                const startContainer = getNodeFromPath(selectionData.startContainerPath);
                const endContainer = getNodeFromPath(selectionData.endContainerPath);

                if (!startContainer || !endContainer) {
                    console.error("Couldn't find container nodes for range deserialization", selectionData);
                    return null;
                }

                const range = document.createRange();
                range.setStart(startContainer, selectionData.startOffset);
                range.setEnd(endContainer, selectionData.endOffset);
                return range;
            } catch (e) {
                console.error("Error deserializing range:", e);
                return null;
            }
        }

        function getNodeFromPath(path) {
            let node = lessonContent;
            for (let i = 0; i < path.length; i++) {
                if (node.childNodes.length > path[i]) {
                    node = node.childNodes[path[i]];
                } else {
                    console.error("Invalid path for node", path);
                    return null;
                }
            }
            return node;
        }

        function applyHighlight(item) {
            const range = deserializeRange(item.data.selection);
            if (range) {
                const span = document.createElement('span');
                span.className = 'highlight';
                span.style.backgroundColor = item.data.color || 'yellow';
                span.dataset.id = item.id;
                if (item.data.highlightId) {
                     span.id = item.data.highlightId;
                }
                try {
                    range.surroundContents(span);
                    addDeleteClickHandler(span);
                } catch (e) {
                    console.warn("Could not apply highlight, probably due to selection spanning multiple block elements.", item.data);
                }
            }
        }

        function applyAnnotation(item) {
            const range = deserializeRange(item.data.selection);
            if (range) {
                const span = document.createElement('span');
                span.className = 'annotation';
                span.title = item.data.text;
                span.dataset.annotationText = item.data.text;
                span.dataset.id = item.id;

                try {
                    range.surroundContents(span);
                    addDeleteClickHandler(span);
                } catch (e) {
                    console.warn("Could not apply annotation, probably due to selection spanning multiple block elements.", item.data);
                }
            }
        }

        function applyQuestion(item) {
            const range = deserializeRange(item.data.selection);
            if (range) {
                const span = document.createElement('span');
                span.className = 'question';
                span.title = item.data.text;
                span.dataset.questionText = item.data.text;
                span.dataset.id = item.id;

                try {
                    range.surroundContents(span);
                    addDeleteClickHandler(span);
                } catch (e) {
                    console.warn("Could not apply question, probably due to selection spanning multiple block elements.", item.data);
                }
            }
        }

        function applySummary(item) {
            const range = deserializeRange(item.data.selection);
            if (range) {
                const summaryDiv = document.createElement('div');
                summaryDiv.className = 'summary';
                summaryDiv.dataset.id = item.id;
                summaryDiv.innerHTML = `<p>${item.data.text}</p>`;

                const endContainer = range.endContainer;
                const endOffset = range.endOffset;
                const parent = endContainer.parentNode;

                // Find the block-level element to append the summary after
                let blockElement = parent;
                while(blockElement && window.getComputedStyle(blockElement).display !== 'block') {
                    blockElement = blockElement.parentNode;
                }

                if (blockElement && blockElement.parentNode) {
                    blockElement.parentNode.insertBefore(summaryDiv, blockElement.nextSibling);
                     addDeleteClickHandler(summaryDiv);
                } else {
                    range.endContainer.parentNode.appendChild(summaryDiv);
                     addDeleteClickHandler(summaryDiv);
                }
            }
        }

        document.getElementById('save-annotation-btn').addEventListener('click', () => {
            const text = document.getElementById('annotation-text').value;
            const selection = JSON.parse(document.getElementById('selection-data-annotation').value);
            saveTextualData('annotation', text, selection);
            annotationModal.hide();
        });

        document.getElementById('save-question-btn').addEventListener('click', () => {
            const text = document.getElementById('question-text').value;
            const selection = JSON.parse(document.getElementById('selection-data-question').value);
            saveTextualData('question', text, selection);
            questionModal.hide();
        });

        document.getElementById('save-summary-btn').addEventListener('click', () => {
            const text = document.getElementById('summary-text').value;
            const selection = JSON.parse(document.getElementById('selection-data-summary').value);
            saveTextualData('summary', text, selection);
            summaryModal.hide();
        });

        function setupModal(modal, button, inputId) {
             button.addEventListener('click', () => {
                if (currentSelection) {
                    document.getElementById(inputId).value = JSON.stringify(serializeRange(currentSelection));
                    modal.show();
                } else {
                    alert('Prima seleziona del testo.');
                }
            });
        }

        setupModal(annotationModal, annotateBtn, 'selection-data-annotation');
        setupModal(questionModal, questionBtn, 'selection-data-question');
        setupModal(summaryModal, summaryBtn, 'selection-data-summary');

        function serializeRange(range) {
            // Simplified serialization, for a real app a more robust solution is needed
            return {
                startContainerPath: getPath(range.startContainer),
                startOffset: range.startOffset,
                endContainerPath: getPath(range.endContainer),
                endOffset: range.endOffset
            };
        }

        highlightBtn.addEventListener('click', () => {
            if (currentSelection) {
                const color = document.getElementById('highlight-color-picker').value;
                const selectionData = serializeRange(currentSelection);
                saveHighlight(selectionData, color);
            } else {
                alert('Prima seleziona del testo da evidenziare.');
            }
        });

        function saveHighlight(selectionData, color) {
            const data = {
                lesson_id: <?php echo $lesson->id; ?>,
                type: 'highlight',
                data: {
                    selection: selectionData,
                    text: currentSelection.toString(),
                    color: color
                }
            };

            fetch('save_student_data.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    location.reload();
                } else {
                    alert('Errore nel salvataggio dell\'evidenziazione: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Si è verificato un errore di rete.');
            });
        }

        function getPath(node) {
            let path = [];
            while (node !== lessonContent) {
                let sibling = node;
                let index = 0;
                while ((sibling = sibling.previousSibling) != null) {
                    index++;
                }
                path.unshift(index);
                node = node.parentNode;
            }
            return path;
        }

    });
    </script>
    <?php endif; ?>
</body>
</html>
