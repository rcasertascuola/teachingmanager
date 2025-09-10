<?php
require_once '../src/Database.php';
require_once '../src/User.php';
include '../header.php';

if ($_SESSION['role'] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$pageTitle = 'Aggiungi Nuovo Utente';
$formAction = 'save.php';
$userData = [
    'id' => null,
    'username' => '',
    'role' => 'student',
    'classe' => '',
    'corso' => '',
    'anno_scolastico' => ''
];

if (isset($_GET['id'])) {
    $pageTitle = 'Modifica Utente';
    $retrievedUser = $user->findById($_GET['id']);
    if ($retrievedUser) {
        $userData = $retrievedUser;
    }
}
?>

<div class="container mt-4">
    <h1 class="h2 mb-4"><?php echo $pageTitle; ?></h1>

    <div class="card">
        <div class="card-body">
            <form action="<?php echo $formAction; ?>" method="post">
                <?php if ($userData['id']): ?>
                    <input type="hidden" name="id" value="<?php echo $userData['id']; ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($userData['username']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" <?php if (!$userData['id']) echo 'required'; ?>>
                    <?php if ($userData['id']): ?>
                        <small class="form-text text-muted">Lasciare vuoto per non modificare la password.</small>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label">Ruolo</label>
                    <select class="form-select" id="role" name="role">
                        <option value="student" <?php echo ($userData['role'] === 'student') ? 'selected' : ''; ?>>Alunno</option>
                        <option value="teacher" <?php echo ($userData['role'] === 'teacher') ? 'selected' : ''; ?>>Insegnante</option>
                    </select>
                </div>

                <div id="student-fields" style="display: <?php echo ($userData['role'] === 'student') ? 'block' : 'none'; ?>;">
                    <div class="mb-3">
                        <label for="classe" class="form-label">Classe</label>
                        <input type="text" class="form-control" id="classe" name="classe" value="<?php echo htmlspecialchars($userData['classe'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="corso" class="form-label">Corso</label>
                        <input type="text" class="form-control" id="corso" name="corso" value="<?php echo htmlspecialchars($userData['corso'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="anno_scolastico" class="form-label">Anno Scolastico</label>
                        <input type="text" class="form-control" id="anno_scolastico" name="anno_scolastico" placeholder="Es. 2023/2024" value="<?php echo htmlspecialchars($userData['anno_scolastico'] ?? ''); ?>">
                    </div>
                </div>

                <a href="index.php" class="btn btn-secondary">Annulla</a>
                <button type="submit" class="btn btn-primary">Salva Utente</button>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('role').addEventListener('change', function () {
        var studentFields = document.getElementById('student-fields');
        if (this.value === 'student') {
            studentFields.style.display = 'block';
        } else {
            studentFields.style.display = 'none';
        }
    });
</script>

<?php include '../footer.php'; ?>
