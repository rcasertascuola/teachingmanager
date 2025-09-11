<?php
require_once '../src/Database.php';
require_once '../src/Lesson.php';
require_once '../src/Module.php';
require_once '../src/Uda.php';
include '../header.php';


// Get the database connection
$db = Database::getInstance()->getConnection();

$module_manager = new Module($db);
$modules = $module_manager->findAll();
$moduleNameMap = [];
$moduleUdaMap = [];
foreach ($modules as $module) {
    $moduleNameMap[$module->id] = $module->name;
    $moduleUdaMap[$module->id] = $module->uda_id;
}

$uda_manager = new Uda($db);
$udas = $uda_manager->findAll();
$udaNameMap = [];
foreach ($udas as $uda) {
    $udaNameMap[$uda->id] = $uda->name;
}
$lesson_manager = new Lesson($db);

// Pagination settings
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);
$limit = 10; // 10 lessons per page
$offset = ($page - 1) * $limit;

// Search terms
$search_content = trim($_GET['search_content'] ?? '');
$search_tags = trim($_GET['search_tags'] ?? '');
$is_search = !empty($search_content) || !empty($search_tags);
$module_id_filter = isset($_GET['module_id']) ? (int)$_GET['module_id'] : null;

if ($module_id_filter) {
    // For now, no pagination on this view
    $lessons = $lesson_manager->findByModuleId($module_id_filter);
    $total_lessons = count($lessons);
    $limit = $total_lessons; //
} elseif ($is_search) {
    $total_lessons = $lesson_manager->countSearch($search_content, $search_tags);
    $lessons = $lesson_manager->search($search_content, $search_tags, $limit, $offset);
} else {
    $total_lessons = $lesson_manager->countAll();
    $lessons = $lesson_manager->findAll($limit, $offset);
}

$total_pages = ceil($total_lessons / $limit);

$is_teacher = $_SESSION['role'] === 'teacher';

?>

    <div class="container mt-4">
        <?php
        if ($is_teacher && isset($_SESSION['import_feedback'])) {
            $feedback = $_SESSION['import_feedback'];
            echo '<div class="alert alert-' . htmlspecialchars($feedback['type']) . ' alert-dismissible fade show" role="alert">';
            echo htmlspecialchars($feedback['message']);
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo '</div>';
            unset($_SESSION['import_feedback']);
        }
        ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2"><?php echo $is_teacher ? 'Gestione Lezioni' : 'Lezioni Disponibili'; ?></h1>
            <?php if ($is_teacher): ?>
                <a href="edit.php" class="btn btn-primary"><i class="fas fa-plus"></i> Aggiungi Nuova Lezione</a>
            <?php endif; ?>
        </div>

        <div class="card mb-4">
            <div class="card-header">Cerca Lezioni<?php echo $is_teacher ? ' & Importa' : ''; ?></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-<?php echo $is_teacher ? '6' : '12'; ?>">
                        <h5>Cerca Lezioni</h5>
                        <form action="index.php" method="get" class="row g-3">
                            <div class="col-12">
                                <label for="search_content" class="form-label">Contenuto</label>
                                <input type="text" class="form-control" id="search_content" name="search_content" value="<?php echo htmlspecialchars($search_content); ?>">
                            </div>
                            <div class="col-12">
                                <label for="search_tags" class="form-label">Tags</label>
                                <input type="text" class="form-control" id="search_tags" name="search_tags" value="<?php echo htmlspecialchars($search_tags); ?>">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-info"><i class="fas fa-search"></i> Cerca</button>
                                <a href="index.php" class="btn btn-secondary"><i class="fas fa-undo"></i> Reset</a>
                            </div>
                        </form>
                    </div>
                    <?php if ($is_teacher): ?>
                    <div class="col-md-6">
                        <h5>Importa da JSON</h5>
                        <form action="import.php" method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="jsonFile" class="form-label">Seleziona file JSON</label>
                                <input class="form-control" type="file" id="jsonFile" name="jsonFile" accept="application/json" required>
                            </div>
                            <button type="submit" class="btn btn-success"><i class="fas fa-upload"></i> Carica File</button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                Elenco Lezioni (Pagina <?php echo $page; ?> di <?php echo $total_pages; ?>)
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">Titolo</th>
                                <th scope="col">UDA</th>
                                <th scope="col">Modulo</th>
                                <th scope="col">Tags</th>
                                <th scope="col">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($lessons)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">Nessuna lezione trovata.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($lessons as $lesson): ?>
                                    <?php
                                    $moduleId = $lesson->module_id;
                                    $moduleName = $moduleNameMap[$moduleId] ?? 'N/A';
                                    $udaId = $moduleUdaMap[$moduleId] ?? null;
                                    $udaName = $udaId ? ($udaNameMap[$udaId] ?? 'N/A') : 'N/A';
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($lesson->title); ?></td>
                                        <td><?php echo htmlspecialchars($udaName); ?></td>
                                        <td><?php echo htmlspecialchars($moduleName); ?></td>
                                        <td><?php echo htmlspecialchars($lesson->tags); ?></td>
                                        <td>
                                            <a href="view.php?id=<?php echo $lesson->id; ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                            <?php if ($is_teacher): ?>
                                            <a href="edit.php?id=<?php echo $lesson->id; ?>" class="btn btn-sm btn-warning"><i class="fas fa-pencil-alt"></i></a>
                                            <a href="delete.php?id=<?php echo $lesson->id; ?>" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if ($total_pages > 1): ?>
            <div class="card-footer">
                <nav aria-label="Navigazione pagine">
                    <ul class="pagination justify-content-center">
                        <?php
                        // Build query string for pagination links
                        $queryString = '';
                        if ($is_search) {
                            $queryString = http_build_query([
                                'search_content' => $search_content,
                                'search_tags' => $search_tags
                            ]);
                        }
                        ?>
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&<?php echo $queryString; ?>">Precedente</a>
                        </li>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&<?php echo $queryString; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>

                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&<?php echo $queryString; ?>">Successiva</a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>

<?php include '../footer.php'; ?>
