<?php
require_once '../src/Database.php';
require_once '../src/TipologiaCompetenza.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../login.php');
    exit;
}

$tipologia = null;
if (isset($_GET['id'])) {
    $tipologia = TipologiaCompetenza::findById($_GET['id']);
}

$pageTitle = $tipologia ? 'Modifica Tipologia' : 'Crea Nuova Tipologia';
$formAction = 'save.php';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2><?php echo $pageTitle; ?></h2>
        <form action="<?php echo $formAction; ?>" method="post">
            <?php if ($tipologia && $tipologia->id): ?>
                <input type="hidden" name="id" value="<?php echo $tipologia->id; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($tipologia->nome ?? ''); ?>" required>
            </div>

            <button type="submit" class="btn btn-primary">Salva</button>
            <a href="index.php" class="btn btn-secondary">Annulla</a>
        </form>
    </div>
</body>
</html>
