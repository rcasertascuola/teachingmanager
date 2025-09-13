<?php
require_once '../src/Database.php';
require_once '../src/Lesson.php';
require_once '../src/Module.php';
require_once '../src/Uda.php';
require_once '../src/wikitext_parser.php';
require_once '../src/Exercise.php';
require_once '../src/Conoscenza.php';
require_once '../src/Abilita.php';
include '../header.php';


// Get the database connection
$db = Database::getInstance()->getConnection();
$lesson_manager = new Lesson($db);

$lesson = null;
if (isset($_GET['id'])) {
    $lesson = $lesson_manager->findById((int)$_GET['id']);
} elseif (isset($_GET['title'])) {
    $lesson = $lesson_manager->findByTitle(urldecode($_GET['title']));
}

$uda = null;
$module = null;
if ($lesson && $lesson->uda_id) {
    $uda_manager = new Uda($db);
    $uda = $uda_manager->findById($lesson->uda_id);
    if ($uda && $uda->module_id) {
        $module_manager = new Module($db);
        $module = $module_manager->findById($uda->module_id);
    }
}

$student_data = [];
if ($lesson && isset($_SESSION['id']) && $_SESSION['role'] === 'student') {
    $student_data = $lesson_manager->getStudentData($_SESSION['id'], $lesson->id);
}

$linked_exercises = [];
$conoscenze_map = [];
$abilita_map = [];

if ($lesson) {
    $exercise_manager = new Exercise($db);
    $linked_exercises = $exercise_manager->findForLesson($lesson->id);

    // Fetch maps for displaying names
    $conoscenza_manager = new Conoscenza($db);
    $all_conoscenze = $conoscenza_manager->findAll();
    foreach ($all_conoscenze as $c) {
        $conoscenze_map[$c->id] = $c->nome;
    }

    $abilita_manager = new Abilita($db);
    $all_abilita = $abilita_manager->findAll();
    foreach ($all_abilita as $a) {
        $abilita_map[$a->id] = $a->nome;
    }
}
?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.13.1/font/bootstrap-icons.min.css" integrity="sha512-t7Few9xlddEmgd3oKZQahkNI4dS6l80+eGEzFQiqtyVYdvcSG2D3Iub77R20BdotfRPA9caaRkg1tyaJiPmO0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
        #student-tools {
            position: absolute;
            display: none;
            z-index: 1050;
            background-color: #343a40;
            padding: 8px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            gap: 5px;
        }
        #student-tools .btn {
            color: white;
            border-color: #6c757d;
            width: 38px;
            font-size: 1.1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        #student-tools .form-control-color {
            width: 40px;
            height: 38px;
            padding: .1rem;
            border: none;
        }
        .figure-thumbnail {
            border: 1px solid #dee2e6;
            background-color: #f8f9fa;
            padding: 0.5rem;
            margin-bottom: 1rem;
        }
        .figure-wrapper.mx-auto {
            clear: both;
        }
    </style>

    <div class="container mt-4">
        <?php if ($lesson): ?>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <?php if ($module): ?>
                        <li class="breadcrumb-item"><a href="../modules/view.php?id=<?php echo $module->id; ?>"><?php echo add_dependency_tooltip($module->name, 'lessons', 'modules'); ?></a></li>
                    <?php endif; ?>
                    <?php if ($uda): ?>
                        <li class="breadcrumb-item"><a href="../udas/view.php?id=<?php echo $uda->id; ?>"><?php echo add_dependency_tooltip($uda->name, 'lessons', 'udas'); ?></a></li>
                    <?php endif; ?>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($lesson->title); ?></li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <h1 class="h2 mb-0"><?php echo htmlspecialchars($lesson->title); ?></h1>
                    <div>
                        <?php if ($lesson->disciplina_nome): ?>
                             <span class="badge bg-primary me-2"><?php echo htmlspecialchars($lesson->disciplina_nome); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($lesson->tags)): ?>
                            <small class="text-muted">Tags: <?php echo htmlspecialchars($lesson->tags); ?></small>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <div id="lesson-content" class="wikitext-content">
                        <?php echo parse_wikitext($lesson->content); ?>
                    </div>
                </div>
                <div class="card-footer">
                    <?php if ($_SESSION['role'] === 'teacher'): ?>
                        <a href="edit.php?id=<?php echo $lesson->id; ?>" class="btn btn-primary">Modifica Lezione</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="h4 mb-0">Dettagli Strutturali</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Conoscenze Collegate</h5>
                            <?php if (!empty($lesson->conoscenze)): ?>
                                <ul class="list-group">
                                    <?php foreach ($lesson->conoscenze as $id): ?>
                                        <li class="list-group-item"><?php echo add_dependency_tooltip($conoscenze_map[$id] ?? 'ID Sconosciuto', 'lessons', 'conoscenze'); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p>Nessuna conoscenza collegata.</p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <h5>Abilità Collegate</h5>
                            <?php if (!empty($lesson->abilita)): ?>
                                <ul class="list-group">
                                    <?php foreach ($lesson->abilita as $id): ?>
                                        <li class="list-group-item"><?php echo add_dependency_tooltip($abilita_map[$id] ?? 'ID Sconosciuto', 'lessons', 'abilita'); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p>Nessuna abilità collegata.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($linked_exercises)): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="h4 mb-0">Esercizi Collegati</h3>
                </div>
                <div class="list-group list-group-flush">
                    <?php foreach ($linked_exercises as $exercise): ?>
                        <a href="../exercises/view.php?id=<?php echo $exercise->id; ?>" class="list-group-item list-group-item-action">
                            <?php echo htmlspecialchars($exercise->title); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($_SESSION['role'] === 'student'): ?>
            <div id="student-tools">
                <button id="highlight-btn" class="btn btn-sm btn-secondary" title="Evidenzia"><i class="bi bi-highlighter"></i></button>
                <input type="color" id="highlight-color-picker" class="form-control form-control-color" value="#ffff00" title="Scegli un colore per evidenziare">
                <button id="annotate-btn" class="btn btn-sm btn-secondary" title="Annota"><i class="bi bi-chat-square-text"></i></button>
                <button id="question-btn" class="btn btn-sm btn-secondary" title="Fai una domanda"><i class="bi bi-question-circle"></i></button>
                <button id="summary-btn" class="btn btn-sm btn-secondary" title="Aggiungi riassunto"><i class="bi bi-card-text"></i></button>
            </div>
            <?php endif; ?>

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

    <?php if ($_SESSION['role'] === 'student'): ?>
    <script>
    const studentData = <?php echo json_encode($student_data); ?>;

    document.addEventListener('DOMContentLoaded', function () {
        const lessonContent = document.getElementById('lesson-content');
        const studentTools = document.getElementById('student-tools');
        const highlightBtn = document.getElementById('highlight-btn');
        const questionBtn = document.getElementById('question-btn');
        const summaryBtn = document.getElementById('summary-btn');
        const annotateBtn = document.getElementById('annotate-btn');

        const annotationModal = new bootstrap.Modal(document.getElementById('annotation-modal'));
        const questionModal = new bootstrap.Modal(document.getElementById('question-modal'));
        const summaryModal = new bootstrap.Modal(document.getElementById('summary-modal'));

        let currentSelection = null;

        loadStudentData();

        function handleSelection(event) {
            setTimeout(() => {
                if (!studentTools) return;
                const selection = window.getSelection();
                if (selection && selection.rangeCount > 0 && !selection.isCollapsed) {
                    const range = selection.getRangeAt(0);
                    const rect = range.getBoundingClientRect();

                    if (rect.width > 1 || rect.height > 1) {
                        currentSelection = range;
                        studentTools.style.display = 'flex';

                        let top = rect.top + window.scrollY - studentTools.offsetHeight - 10;
                        if (top < window.scrollY) {
                            top = rect.bottom + window.scrollY + 10;
                        }

                        let left = rect.left + window.scrollX + rect.width / 2 - studentTools.offsetWidth / 2;
                        if (left < 0) left = 5;
                        if (left + studentTools.offsetWidth > window.innerWidth) {
                            left = window.innerWidth - studentTools.offsetWidth - 5;
                        }

                        studentTools.style.top = `${top}px`;
                        studentTools.style.left = `${left}px`;
                    }
                } else {
                    if (studentTools && event && event.target && !studentTools.contains(event.target)) {
                       studentTools.style.display = 'none';
                    }
                }
            }, 10);
        }

        if (studentTools) {
            lessonContent.addEventListener('mouseup', handleSelection);
            lessonContent.addEventListener('touchend', handleSelection);

            lessonContent.addEventListener('contextmenu', e => e.preventDefault());

            document.addEventListener('mousedown', function(e) {
                if (studentTools.style.display === 'flex') {
                    const isClickInsideLesson = lessonContent.contains(e.target);
                    const isClickInsideToolbar = studentTools.contains(e.target);

                    if (!isClickInsideLesson && !isClickInsideToolbar) {
                        studentTools.style.display = 'none';
                        currentSelection = null;
                        if (window.getSelection) {
                            if (window.getSelection().empty) {  // Chrome
                                window.getSelection().empty();
                            } else if (window.getSelection().removeAllRanges) {  // Firefox
                                window.getSelection().removeAllRanges();
                            }
                        }
                    }
                }
            });
        }

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

                const startNodeLength = (startContainer.nodeType === Node.TEXT_NODE) ? startContainer.length : startContainer.childNodes.length;
                if (selectionData.startOffset > startNodeLength) {
                    console.warn("Start offset out of bounds for node. Skipping this item.", { selection: selectionData, node: startContainer });
                    return null;
                }

                const endNodeLength = (endContainer.nodeType === Node.TEXT_NODE) ? endContainer.length : endContainer.childNodes.length;
                if (selectionData.endOffset > endNodeLength) {
                    console.warn("End offset out of bounds for node. Skipping this item.", { selection: selectionData, node: endContainer });
                    return null;
                }

                const range = document.createRange();
                range.setStart(startContainer, selectionData.startOffset);
                range.setEnd(endContainer, selectionData.endOffset);
                return range;
            } catch (e) {
                console.error("Error deserializing range:", e, { selection: selectionData });
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
<?php include '../footer.php'; ?>
