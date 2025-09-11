<?php
require_once '../src/Database.php';
require_once '../src/Lesson.php';
require_once '../src/User.php';
include '../header.php';


$lessonId = $_GET['id'] ?? null;
if (!$lessonId) {
    // Redirect to the feedback selection page if no lesson ID is provided
    header("location: ../feedback/index.php");
    exit;
}

// Get the database connection
$db = Database::getInstance()->getConnection();
$lesson_manager = new Lesson($db);

$lesson = $lesson_manager->findById($lessonId);
if (!$lesson) {
    echo "Lezione non trovata.";
    exit;
}

$is_teacher = $_SESSION['role'] === 'teacher';

if ($is_teacher) {
    // Teacher-specific data fetching
    $allStudentData = $lesson_manager->getAllStudentDataForLesson($lessonId);
    $studentsOnLesson = $lesson_manager->getStudentsForLesson($lessonId);

    $aggregatedData = ['highlight' => [], 'annotation' => [], 'question' => [], 'summary' => []];
    $individualData = [];

    foreach ($allStudentData as $data) {
        $studentId = $data['user_id'];
        $username = $data['username'];
        $type = $data['type'];

        if (!isset($individualData[$studentId])) {
            $individualData[$studentId] = ['username' => $username, 'data' => []];
        }
        $individualData[$studentId]['data'][] = $data;

        if (isset($aggregatedData[$type])) {
            $data['data']['student_username'] = $username;
            $aggregatedData[$type][] = $data['data'];
        }
    }
} else {
    // Student-specific data fetching
    $student_id = $_SESSION['id'];
    $studentData = $lesson_manager->getStudentData($student_id, $lessonId);

    // Also, ensure student has access to this lesson's feedback
    // This is implicitly handled by findForStudent on the previous page,
    // but a direct access attempt should be secure.
    if (empty($studentData)) {
        // We can check if they have any interaction at all.
        // A student who has no data for a lesson shouldn't be here.
        // Maybe redirect them back if they try to access it directly.
        // For now, we just show a message.
    }
}

?>
    <style>
        .data-block { margin-bottom: 1rem; }
        .data-block h5 { font-size: 1rem; font-weight: bold; }
        .data-block .content { background-color: #f8f9fa; border-left: 3px solid #0d6efd; padding: 10px; margin-top: 5px; }
        .student-badge { font-size: 0.8rem; font-weight: normal; }
    </style>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center">
            <h1>Riscontro per: <strong><?php echo htmlspecialchars($lesson->title); ?></strong></h1>
            <a href="../feedback/index.php" class="btn btn-secondary">
                <?php echo $is_teacher ? 'Torna a Selezione Lezione' : 'Torna ai tuoi Riscontri'; ?>
            </a>
        </div>
        <hr>

        <?php if ($is_teacher): ?>
            <!-- Teacher View -->
            <ul class="nav nav-tabs" id="feedbackTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="aggregated-tab" data-bs-toggle="tab" data-bs-target="#aggregated" type="button" role="tab" aria-controls="aggregated" aria-selected="true">Visione Aggregata</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="individual-tab" data-bs-toggle="tab" data-bs-target="#individual" type="button" role="tab" aria-controls="individual" aria-selected="false">Dettaglio per Alunno</button>
                </li>
            </ul>
            <div class="tab-content pt-3" id="feedbackTabContent">
                <div class="tab-pane fade show active" id="aggregated" role="tabpanel" aria-labelledby="aggregated-tab">
                    <h3>Dati aggregati da <?php echo count($studentsOnLesson); ?> alunni</h3>
                    <div class="card mb-3"><div class="card-header"><h4>Sottolineature</h4></div><div class="card-body">
                        <?php if (empty($aggregatedData['highlight'])): ?><p class="text-muted">Nessuna sottolineatura.</p><?php else: ?>
                        <?php foreach ($aggregatedData['highlight'] as $item): ?><div class="data-block"><h5 class="mb-1">Da: <?php echo htmlspecialchars($item['student_username']); ?> <span class="badge bg-primary student-badge">Sottolineatura</span></h5><div class="content">"<?php echo htmlspecialchars($item['text'] ?? '[Testo non disponibile]'); ?>"</div></div><?php endforeach; ?><?php endif; ?></div></div>
                    <div class="card mb-3"><div class="card-header"><h4>Annotazioni</h4></div><div class="card-body">
                        <?php if (empty($aggregatedData['annotation'])): ?><p class="text-muted">Nessuna annotazione.</p><?php else: ?>
                        <?php foreach ($aggregatedData['annotation'] as $item): ?><div class="data-block"><h5 class="mb-1">Da: <?php echo htmlspecialchars($item['student_username']); ?> <span class="badge bg-success student-badge">Annotazione</span></h5><div class="content"><?php echo htmlspecialchars($item['text']); ?></div></div><?php endforeach; ?><?php endif; ?></div></div>
                    <div class="card mb-3"><div class="card-header"><h4>Domande</h4></div><div class="card-body">
                        <?php if (empty($aggregatedData['question'])): ?><p class="text-muted">Nessuna domanda.</p><?php else: ?>
                        <?php foreach ($aggregatedData['question'] as $item): ?><div class="data-block"><h5 class="mb-1">Da: <?php echo htmlspecialchars($item['student_username']); ?> <span class="badge bg-warning text-dark student-badge">Domanda</span></h5><div class="content"><?php echo htmlspecialchars($item['text']); ?></div></div><?php endforeach; ?><?php endif; ?></div></div>
                    <div class="card mb-3"><div class="card-header"><h4>Riassunti</h4></div><div class="card-body">
                        <?php if (empty($aggregatedData['summary'])): ?><p class="text-muted">Nessun riassunto.</p><?php else: ?>
                        <?php foreach ($aggregatedData['summary'] as $item): ?><div class="data-block"><h5 class="mb-1">Da: <?php echo htmlspecialchars($item['student_username']); ?> <span class="badge bg-info text-dark student-badge">Riassunto</span></h5><div class="content"><?php echo nl2br(htmlspecialchars($item['text'])); ?></div></div><?php endforeach; ?><?php endif; ?></div></div>
                </div>
                <div class="tab-pane fade" id="individual" role="tabpanel" aria-labelledby="individual-tab">
                    <h3>Dati per singolo alunno</h3>
                    <div class="mb-3"><label for="studentSelector" class="form-label">Seleziona un alunno:</label><select class="form-select" id="studentSelector"><option value="">-- Seleziona --</option><?php foreach ($studentsOnLesson as $student): ?><option value="<?php echo $student['id']; ?>"><?php echo htmlspecialchars($student['username']); if (!empty($student['classe']) || !empty($student['corso']) || !empty($student['anno_scolastico'])) { echo " (" . htmlspecialchars($student['classe'] ?? '') . " " . htmlspecialchars($student['corso'] ?? '') . " - " . htmlspecialchars($student['anno_scolastico'] ?? '') . ")"; } ?></option><?php endforeach; ?></select></div>
                    <div id="student-data-container"><p class="text-muted">Seleziona un alunno per vedere i suoi dati.</p></div>
                </div>
            </div>

        <?php else: ?>
            <!-- Student View -->
            <div class="card">
                <div class="card-header">
                    <h3>I tuoi Riscontri</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($studentData)): ?>
                        <div class="alert alert-info">Non hai ancora fornito riscontri per questa lezione.</div>
                    <?php else: ?>
                        <?php
                        $categorizedData = ['highlight' => [], 'annotation' => [], 'question' => [], 'summary' => []];
                        foreach ($studentData as $item) {
                            if (isset($categorizedData[$item['type']])) {
                                $categorizedData[$item['type']][] = $item['data'];
                            }
                        }
                        ?>

                        <!-- Highlights -->
                        <h4>Sottolineature</h4>
                        <?php if (empty($categorizedData['highlight'])): ?><p class="text-muted">Nessuna.</p><?php else: ?>
                            <?php foreach ($categorizedData['highlight'] as $item): ?>
                                <div class="data-block"><div class="content" style="border-color: <?php echo htmlspecialchars($item['color'] ?? '#0d6efd'); ?>;">"<?php echo htmlspecialchars($item['text'] ?? '[Testo non disponibile]'); ?>"</div></div>
                            <?php endforeach; ?>
                        <?php endif; ?><hr>

                        <!-- Annotations -->
                        <h4>Annotazioni</h4>
                        <?php if (empty($categorizedData['annotation'])): ?><p class="text-muted">Nessuna.</p><?php else: ?>
                            <?php foreach ($categorizedData['annotation'] as $item): ?>
                                <div class="data-block"><div class="content" style="border-color: #198754;"><?php echo htmlspecialchars($item['text']); ?></div></div>
                            <?php endforeach; ?>
                        <?php endif; ?><hr>

                        <!-- Questions -->
                        <h4>Domande</h4>
                        <?php if (empty($categorizedData['question'])): ?><p class="text-muted">Nessuna.</p><?php else: ?>
                            <?php foreach ($categorizedData['question'] as $item): ?>
                                <div class="data-block"><div class="content" style="border-color: #ffc107;"><?php echo htmlspecialchars($item['text']); ?></div></div>
                            <?php endforeach; ?>
                        <?php endif; ?><hr>

                        <!-- Summaries -->
                        <h4>Riassunti</h4>
                        <?php if (empty($categorizedData['summary'])): ?><p class="text-muted">Nessuno.</p><?php else: ?>
                            <?php foreach ($categorizedData['summary'] as $item): ?>
                                <div class="data-block"><div class="content" style="border-color: #0dcaf0;"><?php echo nl2br(htmlspecialchars($item['text'])); ?></div></div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($is_teacher): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const studentSelector = document.getElementById('studentSelector');
        const container = document.getElementById('student-data-container');
        const individualData = <?php echo json_encode($individualData); ?>;

        studentSelector.addEventListener('change', function() {
            const studentId = this.value;
            if (!studentId) {
                container.innerHTML = '<p class="text-muted">Seleziona un alunno per vedere i suoi dati.</p>';
                return;
            }

            const studentData = individualData[studentId]?.data || [];

            if (studentData.length === 0) {
                container.innerHTML = '<p>Nessun dato trovato per questo alunno.</p>';
                return;
            }

            let html = '';
            studentData.forEach(item => {
                let badgeClass = '';
                let typeText = '';
                switch(item.type) {
                    case 'highlight': badgeClass = 'bg-primary'; typeText = 'Sottolineatura'; break;
                    case 'annotation': badgeClass = 'bg-success'; typeText = 'Annotazione'; break;
                    case 'question': badgeClass = 'bg-warning text-dark'; typeText = 'Domanda'; break;
                    case 'summary': badgeClass = 'bg-info text-dark'; typeText = 'Riassunto'; break;
                }
                const textContent = item.data.text ?? (item.type === 'highlight' ? '[Testo non disponibile]' : '');
                html += `<div class="data-block"><h5><span class="badge ${badgeClass}">${typeText}</span></h5><div class="content">${textContent.replace(/\n/g, '<br>')}</div></div>`;
            });
            container.innerHTML = html;
        });
    });
    </script>
    <?php endif; ?>
<?php include '../footer.php'; ?>
