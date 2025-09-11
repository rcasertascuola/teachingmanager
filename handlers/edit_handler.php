<?php
// Generic Edit/Create Form Handler

// Ensure required variables are set
if (!isset($page_title) || !isset($form_action) || !isset($entity) || !isset($form_fields)) {
    die("Configuration error in edit handler.");
}

?>
<div class="container mt-4">
    <h1 class="h2 mb-4"><?php echo htmlspecialchars($page_title); ?></h1>

    <div class="card">
        <div class="card-body">
            <form action="<?php echo htmlspecialchars($form_action); ?>" method="post">
                <?php if ($entity && $entity->id): ?>
                    <input type="hidden" name="id" value="<?php echo $entity->id; ?>">
                <?php endif; ?>

                <?php foreach ($form_fields as $field_name => $field_config): ?>
                    <?php if ($field_name === 'custom_html'): ?>
                        <?php echo $field_config; ?>
                    <?php else: ?>
                    <div class="mb-3">
                        <label for="<?php echo $field_name; ?>" class="form-label"><?php echo htmlspecialchars($field_config['label']); ?></label>

                        <?php
                        $field_type = $field_config['type'] ?? 'text';
                        $field_value = htmlspecialchars($entity->$field_name ?? '');
                        $required = isset($field_config['required']) && $field_config['required'] ? 'required' : '';

                        switch ($field_type) {
                            case 'textarea':
                                echo "<textarea class='form-control' id='$field_name' name='$field_name' rows='5' $required>$field_value</textarea>";
                                break;

                            case 'checkbox_group':
                                $options = $field_config['options'] ?? [];
                                $selected_values = $entity->$field_name ?? [];
                                echo "<div class='border rounded p-2' style='max-height: 150px; overflow-y: auto;'>";
                                foreach ($options as $option_value => $option_label) {
                                    $checked = in_array($option_value, $selected_values) ? 'checked' : '';
                                    echo "<div class='form-check'>";
                                    echo "<input class='form-check-input' type='checkbox' id='{$field_name}_{$option_value}' name='{$field_name}[]' value='$option_value' $checked>";
                                    echo "<label class='form-check-label' for='{$field_name}_{$option_value}'>" . htmlspecialchars($option_label) . "</label>";
                                    echo "</div>";
                                }
                                echo "</div>";
                                break;

                            case 'select':
                                $options = $field_config['options'] ?? [];
                                echo "<select class='form-select' id='$field_name' name='$field_name' $required>";
                                if (isset($field_config['default_option'])) {
                                    echo "<option value=''>{$field_config['default_option']}</option>";
                                }
                                foreach ($options as $option_value => $option_label) {
                                    $selected = ($field_value == $option_value) ? 'selected' : '';
                                    echo "<option value='$option_value' $selected>" . htmlspecialchars($option_label) . "</option>";
                                }
                                echo "</select>";
                                break;

                            case 'text':
                            default:
                                echo "<input type='text' class='form-control' id='$field_name' name='$field_name' value='$field_value' $required>";
                                break;
                        }

                        if (isset($field_config['help_text'])): ?>
                            <div class="form-text"><?php echo htmlspecialchars($field_config['help_text']); ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <a href="index.php" class="btn btn-secondary">Annulla</a>
                <button type="submit" class="btn btn-primary">Salva</button>
            </form>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
