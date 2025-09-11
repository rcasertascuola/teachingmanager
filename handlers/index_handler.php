<?php
// Generic Index Page Handler

// Ensure required variables are set
if (!isset($page_title) || !isset($entity_name) || !isset($columns) || !isset($table_name)) {
    die("Configuration error: required variables are not set for the index handler.");
}

$is_teacher = isset($_SESSION['role']) && $_SESSION['role'] === 'teacher';
$json_columns = json_encode(array_merge(array_keys($columns), ['actions']));
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2"><?php echo htmlspecialchars($page_title); ?></h1>
        <?php if ($is_teacher): ?>
            <a href="edit.php" class="btn btn-primary"><i class="fas fa-plus"></i></a>
        <?php endif; ?>
    </div>

    <?php
    // Feedback message display
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
                <table class="table table-striped"
                       data-dynamic-table
                       data-table-name="<?php echo htmlspecialchars($table_name); ?>"
                       data-columns='<?php echo $json_columns; ?>'
                       <?php if (isset($selects)): ?>data-table-selects='<?php echo json_encode($selects); ?>'<?php endif; ?>
                       <?php if (isset($joins)): ?>data-table-joins='<?php echo json_encode($joins); ?>'<?php endif; ?>
                       <?php if (isset($custom_actions)): ?>data-table-custom-actions='<?php echo json_encode($custom_actions); ?>'<?php endif; ?>
                       <?php if (isset($renderers)): ?>data-table-renderers='<?php echo json_encode($renderers); ?>'<?php endif; ?>
                       >
                    <thead>
                        <tr>
                            <?php foreach ($columns as $column_label): ?>
                                <th scope="col"><?php echo htmlspecialchars($column_label); ?></th>
                            <?php endforeach; ?>
                            <th scope="col">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Content will be loaded dynamically by assets/js/dynamic-table.js -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
