<?php
require_once '../src/Database.php';
require_once '../src/Exercise.php';
require_once '../src/Lesson.php';
include '../header.php';

// Auth check
if ($_SESSION["role"] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}


$exercise = null;
$pageTitle = 'Aggiungi Nuovo Esercizio';
$formAction = 'save.php';
$linked_lesson_ids = [];

// Check if we are editing an existing exercise
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $exercise = Exercise::findById((int)$_GET['id']);
    if ($exercise) {
        $pageTitle = 'Modifica Esercizio';
        $linked_lessons = $exercise->getLinkedLessons();
        $linked_lesson_ids = array_column($linked_lessons, 'id');
    } else {
        $_SESSION['feedback'] = ['type' => 'danger', 'message' => 'Esercizio non trovato.'];
        header("location: index.php");
        exit;
    }
}

// Fetch all lessons for the multi-select field
$all_lessons = Lesson::findAll(1000, 0); // Assuming there are less than 1000 lessons

$exercise_types = ['multiple_choice' => 'Risposta Multipla', 'open_answer' => 'Risposta Aperta', 'fill_in_the_blanks' => 'Completa gli Spazi'];

?>

    <div class="container mt-4">
        <h1 class="h2 mb-4"><?php echo $pageTitle; ?></h1>

        <div class="card">
            <div class="card-body">
                <form action="<?php echo $formAction; ?>" method="post">
                    <?php if ($exercise && $exercise->id): ?>
                        <input type="hidden" name="id" value="<?php echo $exercise->id; ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="title" class="form-label">Titolo</label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($exercise->title ?? ''); ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">Tipo di Esercizio</label>
                            <select class="form-select" id="type" name="type" required>
                                <?php foreach ($exercise_types as $key => $value): ?>
                                    <option value="<?php echo $key; ?>" <?php echo (isset($exercise) && $exercise->type == $key) ? 'selected' : ''; ?>>
                                        <?php echo $value; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                             <label for="lessons" class="form-label">Lezioni Collegate</label>
                            <select class="form-select" id="lessons" name="lessons[]" multiple size="5">
                                <?php foreach ($all_lessons as $lesson): ?>
                                    <option value="<?php echo $lesson->id; ?>" <?php echo in_array($lesson->id, $linked_lesson_ids) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($lesson->title); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Tieni premuto Ctrl (o Cmd su Mac) per selezionare più lezioni.</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">Contenuto Esercizio (Sintassi Speciale)</label>
                        <textarea class="form-control" id="content" name="content" rows="15" required><?php echo htmlspecialchars($exercise->content ?? ''); ?></textarea>
                        <div class="form-text">
                            <p class="mb-1"><strong>Sintassi da usare:</strong></p>
                            <ul class="list-unstyled">
                                <li><strong>Risposta Multipla:</strong> <code>[question]Testo domanda...[/question][options]( ) opzione 1\n(x) opzione 2 (corretta)\n( ) opzione 3[/options]</code></li>
                                <li><strong>Risposta Aperta:</strong> <code>[question]Testo domanda...[/question]</code></li>
                                <li><strong>Completa Spazi:</strong> <code>Testo con __1__ e altri __2__ spazi.[blanks]1:risposta1\n2:risposta2[/blanks]</code></li>
                            </ul>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="options" class="form-label">Opzioni (JSON)</label>
                        <textarea class="form-control" id="options" name="options" rows="3"><?php echo htmlspecialchars($exercise->options ?? '{"correction_type": "teacher", "score": 1}'); ?></textarea>
                        <div class="form-text">
                            Es: <code>{"correction_type": "teacher", "score_per_item": 1, "shuffle_answers": true}</code>.
                            <code>correction_type</code> può essere "teacher" o "student".
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="enabled" name="enabled" value="1" <?php echo (isset($exercise) && $exercise->enabled) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="enabled">
                            Abilitato per gli studenti
                        </label>
                    </div>

                    <a href="index.php" class="btn btn-secondary">Annulla</a>
                    <button type="submit" class="btn btn-primary">Salva Esercizio</button>
                </form>
            </div>
        </div>
    </div>

<?php include '../footer.php'; ?>
