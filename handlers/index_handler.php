<?php
// Generic Index Page Handler

// Ensure required variables are set
if (!isset($page_title) || !isset($entity_name) || !isset($columns) || !isset($items)) {
    die("Configuration error in index handler.");
}

$is_teacher = isset($_SESSION['role']) && $_SESSION['role'] === 'teacher';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2"><?php echo htmlspecialchars($page_title); ?></h1>
        <?php if ($is_teacher): ?>
            <a href="edit.php" class="btn btn-primary"><i class="fas fa-plus"></i> Aggiungi Nuovo <?php echo htmlspecialchars($entity_name); ?></a>
        <?php endif; ?>
    </div>

    <?php
    if (isset($_SESSION['feedback'])) {
        $feedback = $_SESSION['feedback'];
        echo '<div class="alert alert-' . htmlspecialchars($feedback['type']) . ' alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($feedback['message']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION['feedback']);
    }
    ?>

    <div class="card">
        <div class="card-header">
            Elenco <?php echo htmlspecialchars($entity_name); ?>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <?php foreach ($columns as $column): ?>
                                <th scope="col"><?php echo htmlspecialchars($column); ?></th>
                            <?php endforeach; ?>
                            <th scope="col">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="<?php echo count($columns) + 1; ?>" class="text-center">Nessun elemento trovato.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <?php foreach ($columns as $key => $column): ?>
                                        <td>
                                            <?php
                                            if (is_callable($column)) {
                                                echo $column($item);
                                            } else {
                                                echo htmlspecialchars($item->$key ?? '');
                                            }
                                            ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td>
                                        <?php if (isset($actions) && is_callable($actions)): ?>
                                            <?php echo $actions($item); ?>
                                        <?php else: ?>
                                            <a href="view.php?id=<?php echo $item->id; ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                            <?php if ($is_teacher): ?>
                                            <a href="edit.php?id=<?php echo $item->id; ?>" class="btn btn-sm btn-warning"><i class="fas fa-pencil-alt"></i></a>
                                            <a href="delete.php?id=<?php echo $item->id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Sei sicuro di voler cancellare questo elemento?');"><i class="fas fa-trash"></i></a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
