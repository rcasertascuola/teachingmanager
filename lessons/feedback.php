<?php
require_once '../src/init.php';
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'teacher') {
    header("location: ../login.php");
    exit;
}

require_once '../src/Database.php';
require_once '../src/Lesson.php';
require_once '../src/User.php';

$lessonId = $_GET['id'] ?? null;
if (!$lessonId) {
    header("location: index.php");
    exit;
}

$lesson = Lesson::findById($lessonId);
if (!$lesson) {
    echo "Lezione non trovata.";
    exit;
}

// Fetch all data for this lesson
$allStudentData = Lesson::getAllStudentDataForLesson($lessonId);
$studentsOnLesson = Lesson::getStudentsForLesson($lessonId);

// Process data for aggregated view and individual view
$aggregatedData = [
    'highlight' => [],
    'annotation' => [],
    'question' => [],
    'summary' => []
];
$individualData = [];

foreach ($allStudentData as $data) {
    $studentId = $data['user_id'];
    $username = $data['username'];
    $type = $data['type'];

    if (!isset($individualData[$studentId])) {
        $individualData[$studentId] = [
            'username' => $username,
            'data' => []
        ];
    }
    $individualData[$studentId]['data'][] = $data;

    // For aggregated view, we collect all data points
    if (isset($aggregatedData[$type])) {
        // Add student info to the data for context
        $data['data']['student_username'] = $username;
        $aggregatedData[$type][] = $data['data'];
    }
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riscontro Alunni per: <?php echo htmlspecialchars($lesson->title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .data-block { margin-bottom: 1rem; }
        .data-block h5 { font-size: 1rem; font-weight: bold; }
        .data-block .content { background-color: #f8f9fa; border-left: 3px solid #0d6efd; padding: 10px; margin-top: 5px; }
        .student-badge { font-size: 0.8rem; font-weight: normal; }
    </style>
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
        <div class="d-flex justify-content-between align-items-center">
            <h1>Riscontro per: <strong><?php echo htmlspecialchars($lesson->title); ?></strong></h1>
            <a href="index.php" class="btn btn-secondary">Torna a Lezioni</a>
        </div>
        <hr>

        <ul class="nav nav-tabs" id="feedbackTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="aggregated-tab" data-bs-toggle="tab" data-bs-target="#aggregated" type="button" role="tab" aria-controls="aggregated" aria-selected="true">Visione Aggregata</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="individual-tab" data-bs-toggle="tab" data-bs-target="#individual" type="button" role="tab" aria-controls="individual" aria-selected="false">Dettaglio per Alunno</button>
            </li>
        </ul>

        <div class="tab-content pt-3" id="feedbackTabContent">
            <!-- Aggregated View -->
            <div class="tab-pane fade show active" id="aggregated" role="tabpanel" aria-labelledby="aggregated-tab">
                <h3>Dati aggregati da <?php echo count($studentsOnLesson); ?> alunni</h3>
                <p>Questa sezione mostra tutti i contributi degli studenti, raggruppati per tipo.</p>

                <!-- Highlights -->
                <div class="card mb-3">
                    <div class="card-header"><h4>Sottolineature</h4></div>
                    <div class="card-body">
                        <?php if (empty($aggregatedData['highlight'])): ?>
                            <p class="text-muted">Nessuna sottolineatura dagli alunni.</p>
                        <?php else: ?>
                            <?php foreach ($aggregatedData['highlight'] as $item): ?>
                                <div class="data-block">
                                    <h5 class="mb-1">
                                        Da: <?php echo htmlspecialchars($item['student_username']); ?>
                                        <span class="badge bg-primary student-badge">Sottolineatura</span>
                                    </h5>
                                    <div class="content">"<?php echo htmlspecialchars($item['text'] ?? '[Testo non disponibile]'); ?>"</div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Notes -->
                <div class="card mb-3">
                    <div class="card-header"><h4>Annotazioni</h4></div>
                    <div class="card-body">
                         <?php if (empty($aggregatedData['annotation'])): ?>
                            <p class="text-muted">Nessuna annotazione dagli alunni.</p>
                        <?php else: ?>
                            <?php foreach ($aggregatedData['annotation'] as $item): ?>
                                <div class="data-block">
                                    <h5 class="mb-1">
                                        Da: <?php echo htmlspecialchars($item['student_username']); ?>
                                        <span class="badge bg-success student-badge">Annotazione</span>
                                    </h5>
                                    <div class="content"><?php echo htmlspecialchars($item['text']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Questions -->
                <div class="card mb-3">
                    <div class="card-header"><h4>Domande</h4></div>
                    <div class="card-body">
                        <?php if (empty($aggregatedData['question'])): ?>
                            <p class="text-muted">Nessuna domanda dagli alunni.</p>
                        <?php else: ?>
                            <?php foreach ($aggregatedData['question'] as $item): ?>
                                <div class="data-block">
                                    <h5 class="mb-1">
                                        Da: <?php echo htmlspecialchars($item['student_username']); ?>
                                        <span class="badge bg-warning text-dark student-badge">Domanda</span>
                                    </h5>
                                    <div class="content"><?php echo htmlspecialchars($item['text']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Summaries -->
                <div class="card mb-3">
                    <div class="card-header"><h4>Riassunti</h4></div>
                    <div class="card-body">
                        <?php if (empty($aggregatedData['summary'])): ?>
                            <p class="text-muted">Nessun riassunto dagli alunni.</p>
                        <?php else: ?>
                            <?php foreach ($aggregatedData['summary'] as $item): ?>
                                <div class="data-block">
                                    <h5 class="mb-1">
                                        Da: <?php echo htmlspecialchars($item['student_username']); ?>
                                        <span class="badge bg-info text-dark student-badge">Riassunto</span>
                                    </h5>
                                    <div class="content"><?php echo nl2br(htmlspecialchars($item['text'])); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Individual View -->
            <div class="tab-pane fade" id="individual" role="tabpanel" aria-labelledby="individual-tab">
                <h3>Dati per singolo alunno</h3>
                <div class="mb-3">
                    <label for="studentSelector" class="form-label">Seleziona un alunno:</label>
                    <select class="form-select" id="studentSelector">
                        <option value="">-- Seleziona --</option>
                        <?php foreach ($studentsOnLesson as $student): ?>
                            <option value="<?php echo $student['id']; ?>"><?php echo htmlspecialchars($student['username']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="student-data-container">
                    <p class="text-muted">Seleziona un alunno per vedere i suoi dati.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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

                // Safely access text, providing a fallback for older highlights
                const textContent = item.data.text ?? (item.type === 'highlight' ? '[Testo non disponibile]' : '');

                html += `
                    <div class="data-block">
                        <h5><span class="badge ${badgeClass}">${typeText}</span></h5>
                        <div class="content">${textContent.replace(/\n/g, '<br>')}</div>
                    </div>
                `;
            });
            container.innerHTML = html;
        });
    });
    </script>
</body>
</html>
