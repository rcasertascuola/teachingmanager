<?php
// import_page.php
require_once 'header.php'; // Assumendo che header.php gestisca sessione e layout
?>

<div class="container mt-4">
    <h2>Importa Modulo da JSON</h2>
    <p>Seleziona un file JSON formattato secondo lo schema per importare un intero modulo, con le relative competenze, abilità, conoscenze, UDA e lezioni.</p>

    <div class="card">
        <div class="card-body">
            <form id="import-form" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="import_file">File JSON</label>
                    <input type="file" class="form-control-file" id="import_file" name="import_file" accept=".json" required>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Importa Modulo</button>
            </form>
        </div>
    </div>

    <div id="status-message" class="mt-4"></div>

    <div id="analysis-results" class="mt-4"></div>

</div>

<script>
const importForm = document.getElementById('import-form');
const fileInput = document.getElementById('import_file');
const statusDiv = document.getElementById('status-message');
const resultsDiv = document.getElementById('analysis-results');

importForm.addEventListener('submit', handleAnalysis);

function handleAnalysis(e) {
    e.preventDefault();
    if (fileInput.files.length === 0) {
        showStatus('Per favore, seleziona un file.', 'danger');
        return;
    }

    const formData = new FormData();
    formData.append('import_file', fileInput.files[0]);

    showStatus('Analisi del file in corso...', 'info');
    resultsDiv.innerHTML = '';

    fetch('import_module.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'error') {
            showStatus(`Errore: ${data.message}`, 'danger');
            return;
        }
        if (data.status === 'analysis_complete') {
            showStatus('Analisi completata. Controlla i risultati e conferma per importare.', 'primary');
            renderAnalysis(data);
        }
    })
    .catch(error => {
        showStatus('Errore di rete durante l\'analisi.', 'danger');
        console.error('Analysis Error:', error);
    });
}

function renderAnalysis(report) {
    let html = '<h4>Anteprima Importazione</h4>';

    // Render ambiguities
    if (report.ambiguities && Object.keys(report.ambiguities).length > 0) {
        html += '<div class="card mb-3"><div class="card-header bg-warning">Azioni Richieste: Risolvi Ambiguità</div><div class="card-body">';
        for (const [entity, ambiguities] of Object.entries(report.ambiguities)) {
            ambiguities.forEach(ambiguity => {
                html += `<p><strong>Termine ambiguo: "${ambiguity.term}"</strong> (in ${entity})</p>`;
                html += `<div class="form-group" data-entity="${entity}" data-term="${ambiguity.term}">`;
                ambiguity.options.forEach(option => {
                    const desc = option.descrizione ? ` - <em>${option.descrizione}</em>` : '';
                    html += `<div class="form-check">
                               <input class="form-check-input" type="radio" name="res_${entity}_${ambiguity.term}" value="${option.id}" required>
                               <label class="form-check-label">${option.nome}${desc}</label>
                             </div>`;
                });
                html += `</div><hr>`;
            });
        }
        html += '</div></div>';
    } else {
        html += '<p class="text-success">Nessuna ambiguità trovata.</p>';
    }

    // Render summary from the report
    const summary = report.summary;
    html += '<div class="card"><div class="card-header">Riepilogo Contenuto</div><div class="card-body">';
    if (summary.conoscenze > 0) html += `<p>${summary.conoscenze} Conoscenze definite nel file.</p>`;
    if (summary.abilita > 0) html += `<p>${summary.abilita} Abilità definite nel file.</p>`;
    if (summary.competenze > 0) html += `<p>${summary.competenze} Competenze definite nel file.</p>`;
    if (summary.module_name !== 'N/A') html += `<p>1 Modulo: <strong>${summary.module_name}</strong></p>`;
    html += '</div></div>';

    html += '<button id="execute-btn" class="btn btn-success mt-3">Conferma e Salva nel Database</button>';
    resultsDiv.innerHTML = html;

    document.getElementById('execute-btn').addEventListener('click', handleExecution);
}

function handleExecution() {
    const resolutions = {};
    const ambiguityGroups = resultsDiv.querySelectorAll('[data-entity]');

    for(const group of ambiguityGroups) {
        const selected = group.querySelector('input[type="radio"]:checked');
        if (!selected) {
            alert(`Per favore, risolvi l'ambiguità per "${group.dataset.term}"`);
            return;
        }
        const entity = group.dataset.entity;
        const term = group.dataset.term;
        if (!resolutions[entity]) {
            resolutions[entity] = {};
        }
        resolutions[entity][term] = selected.value;
    }

    const formData = new FormData();
    formData.append('import_file', fileInput.files[0]);
    formData.append('action', 'execute');
    formData.append('resolutions', JSON.stringify(resolutions));

    showStatus('Salvataggio nel database in corso...', 'info');

    fetch('import_module.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showStatus(data.message, 'success');
            resultsDiv.innerHTML = '';
            importForm.reset();
        } else {
            showStatus(`Errore durante il salvataggio: ${data.message}`, 'danger');
        }
    })
    .catch(error => {
        showStatus('Errore di rete durante il salvataggio.', 'danger');
        console.error('Execution Error:', error);
    });
}

function showStatus(message, type) {
    statusDiv.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
}
</script>

<?php
require_once 'footer.php';
?>
