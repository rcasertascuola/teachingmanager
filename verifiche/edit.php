<?php
require_once '../src/Database.php';
require_once '../src/Database.php';
require_once '../src/Verifica.php';
require_once '../src/Abilita.php';
require_once '../src/Competenza.php';
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

// Fetch all available abilities and competencies for the form
$abilita_manager = new Abilita($db);
$all_abilita = $abilita_manager->findAll();
$competenza_manager = new Competenza($db);
$all_competenze = $competenza_manager->findAll();

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

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Abilità Collegate</label>
                            <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                                <?php foreach ($all_abilita as $item): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="abilita[]" value="<?php echo $item->id; ?>" id="abilita_<?php echo $item->id; ?>" <?php echo ($verifica && in_array($item->id, $verifica->abilita_ids)) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="abilita_<?php echo $item->id; ?>"><?php echo htmlspecialchars($item->nome); ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Competenze Collegate</label>
                            <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                                <?php foreach ($all_competenze as $item): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="competenze[]" value="<?php echo $item->id; ?>" id="competenza_<?php echo $item->id; ?>" <?php echo ($verifica && in_array($item->id, $verifica->competenza_ids)) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="competenza_<?php echo $item->id; ?>"><?php echo htmlspecialchars($item->nome); ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
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
                <button type="button" id="add-row" class="btn btn-success btn-sm">Aggiungi Descrittore</button>
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
