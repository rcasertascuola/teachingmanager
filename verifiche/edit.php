<?php
require_once '../src/Database.php';
require_once '../src/Verifica.php';
require_once '../src/Module.php';
include '../header.php';

// Auth check
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}

$verifica = null;
$pageTitle = 'Aggiungi Nuova Verifica';
$formAction = 'save.php';

// Get the database connection
$db = Database::getInstance()->getConnection();

// Fetch all modules for the dropdown
$module_manager = new Module($db);
$all_modules = $module_manager->findAll();

$griglia_nome = "Griglia di Valutazione";
$descrittori = [];

$verifica_manager = new Verifica($db);

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    $verifica = $verifica_manager->findById($id);

    if ($verifica) {
        $pageTitle = 'Modifica Verifica';
        if ($verifica->griglia) {
            $griglia_nome = $verifica->griglia->nome;
            $descrittori = $verifica->griglia->descrittori;
        }
    } else {
        // Redirect if verifica not found
        header("Location: index.php");
        exit;
    }
}
?>

<div class="container mt-4">
    <h1 class="h2 mb-4"><?php echo $pageTitle; ?></h1>

    <div class="card">
        <div class="card-body">
            <form action="<?php echo $formAction; ?>" method="post">
                <?php if ($verifica && $verifica->id): ?>
                    <input type="hidden" name="id" value="<?php echo $verifica->id; ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="titolo" class="form-label">Titolo</label>
                    <input type="text" class="form-control" id="titolo" name="titolo" value="<?php echo htmlspecialchars($verifica->titolo ?? ''); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="tipo" class="form-label">Tipo di Verifica</label>
                    <select class="form-select" id="tipo" name="tipo" required>
                        <option value="scritto" <?php echo (isset($verifica) && $verifica->tipo == 'scritto') ? 'selected' : ''; ?>>Scritto</option>
                        <option value="orale" <?php echo (isset($verifica) && $verifica->tipo == 'orale') ? 'selected' : ''; ?>>Orale</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="descrizione" class="form-label">Descrizione</label>
                    <textarea class="form-control" id="descrizione" name="descrizione" rows="3"><?php echo htmlspecialchars($verifica->descrizione ?? ''); ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="module_id" class="form-label">Modulo Collegato</label>
                    <select class="form-select" id="module_id" name="module_id">
                        <option value="">Seleziona un modulo</option>
                        <?php foreach ($all_modules as $module): ?>
                            <option value="<?php echo $module->id; ?>" <?php echo (isset($verifica) && $verifica->module_id == $module->id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($module->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">
                        La verifica erediterà le conoscenze, abilità e competenze del modulo selezionato.
                    </div>
                </div>

                <hr class="my-4">

                <h3 class="h4">Griglia di Valutazione</h3>
                <div class="mb-3">
                    <label for="griglia_nome" class="form-label">Nome Griglia</label>
                    <input type="text" class="form-control" id="griglia_nome" name="griglia_nome" value="<?php echo htmlspecialchars($griglia_nome); ?>" required>
                </div>

                <table id="griglia-table" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Descrittore</th>
                            <th style="width: 150px;">Punteggio Max</th>
                            <th style="width: 100px;">Azione</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($descrittori)): foreach ($descrittori as $index => $d): ?>
                        <input type="hidden" name="descrittori[<?php echo $index; ?>][id]" value="<?php echo $d->id; ?>">
                        <tr>
                            <td><input type="text" class="form-control" name="descrittori[<?php echo $index; ?>][descrittore]" value="<?php echo htmlspecialchars($d->descrittore); ?>" required></td>
                            <td><input type="number" class="form-control punteggio" name="descrittori[<?php echo $index; ?>][punteggio_max]" value="<?php echo htmlspecialchars($d->punteggio_max); ?>" step="0.01" min="0" max="20" required></td>
                            <td><button type="button" class="btn btn-danger btn-sm remove-row">Rimuovi</button></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="text-end"><strong>Totale</strong></td>
                            <td><strong id="total-punteggio">0.00</strong> / 20.00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
                <button type="button" id="add-row" class="btn btn-success btn-sm">+</button>
                <div id="punteggio-error" class="text-danger mt-2" style="display: none;">Il punteggio totale deve essere 20.00</div>


                <hr class="my-4">

                <a href="index.php" class="btn btn-secondary">Annulla</a>
                <button type="submit" class="btn btn-primary">Salva Verifica</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.querySelector('#griglia-table tbody');
    const addRowBtn = document.getElementById('add-row');
    const totalPunteggioEl = document.getElementById('total-punteggio');
    const punteggioErrorEl = document.getElementById('punteggio-error');
    let rowIndex = <?php echo count($descrittori); ?>;

    function updateTotal() {
        let total = 0;
        document.querySelectorAll('.punteggio').forEach(input => {
            const value = parseFloat(input.value);
            if (!isNaN(value)) {
                total += value;
            }
        });
        totalPunteggioEl.textContent = total.toFixed(2);

        if (total.toFixed(2) == 20.00) {
            punteggioErrorEl.style.display = 'none';
        } else {
            punteggioErrorEl.style.display = 'block';
        }
    }

    addRowBtn.addEventListener('click', function() {
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td><input type="text" class="form-control" name="descrittori[${rowIndex}][descrittore]" required></td>
            <td><input type="number" class="form-control punteggio" name="descrittori[${rowIndex}][punteggio_max]" value="0.00" step="0.01" min="0" max="20" required></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row">Rimuovi</button></td>
        `;
        tableBody.appendChild(newRow);
        rowIndex++;
        attachEventListeners(newRow);
        updateTotal();
    });

    function attachEventListeners(element) {
        element.querySelector('.remove-row').addEventListener('click', function() {
            this.closest('tr').remove();
            updateTotal();
        });
        element.querySelector('.punteggio').addEventListener('input', updateTotal);
    }

    document.querySelectorAll('#griglia-table tbody tr').forEach(row => {
        attachEventListeners(row);
    });

    updateTotal();
});
</script>

<?php include '../footer.php'; ?>
