<?php
// dashboard.php
require_once 'header.php';
?>

    <div class="container mt-4">
        <div class="p-3 mb-4 bg-light rounded-3">
            <div class="container-fluid py-3">
                <h1 class="display-5 fw-bold">Ciao, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h1>
                <p class="col-md-8 fs-4">Benvenuto nella tua dashboard. Il tuo ruolo è: <strong><?php echo htmlspecialchars($_SESSION["role"]); ?></strong>.</p>
            </div>
        </div>

        <div class="row">
            <!-- Main Content -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h4>Visione Giornaliera Appuntamenti</h4>
                    </div>
                    <div class="card-body feature-slot">
                        <p class="text-muted">_placeholder per gli appuntamenti di oggi_</p>
                    </div>
                </div>

                <div class="row">
                    <?php if ($_SESSION['role'] === 'teacher'): ?>
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">Gestisci Lezioni</div>
                            <div class="card-body feature-slot d-flex flex-column justify-content-center align-items-center">
                                <p>Crea, modifica e visualizza lezioni.</p>
                                <a href="lessons/index.php" class="btn btn-primary">Vai a Lezioni</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-sitemap"></i> Synoptic View</h5>
                                <p class="card-text">View a synoptic overview of the educational structure.</p>
                                <a href="synoptic_view.php" class="btn btn-info">Go to Synoptic View</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">Gestisci UDA</div>
                            <div class="card-body feature-slot d-flex flex-column justify-content-center align-items-center">
                                <p>Crea e organizza le Unità di Apprendimento.</p>
                                <a href="udas/index.php" class="btn btn-secondary">Gestisci UDA</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">Gestisci Moduli</div>
                            <div class="card-body feature-slot d-flex flex-column justify-content-center align-items-center">
                                <p>Crea e organizza i moduli didattici.</p>
                                <a href="modules/index.php" class="btn btn-secondary">Gestisci Moduli</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="card mb-4">
                            <div class="card-header">Gestione Dati Strutturali</div>
                            <div class="card-body feature-slot d-flex flex-wrap justify-content-center align-items-center">
                                <a href="tipologie/index.php" class="btn btn-outline-dark m-2">Gestisci Tipologie</a>
                                <a href="discipline/index.php" class="btn btn-outline-dark m-2">Gestisci Discipline</a>
                                <a href="conoscenze/index.php" class="btn btn-outline-primary m-2">Gestisci Conoscenze</a>
                                <a href="abilita/index.php" class="btn btn-outline-primary m-2">Gestisci Abilità</a>
                                <a href="competenze/index.php" class="btn btn-outline-primary m-2">Gestisci Competenze</a>
                            </div>
                        </div>
                    </div>
                    <?php else: // Student view ?>
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">Lezioni</div>
                            <div class="card-body feature-slot d-flex flex-column justify-content-center align-items-center">
                                <p>Visualizza le lezioni e i tuoi materiali di studio.</p>
                                <a href="udas/view.php" class="btn btn-primary">Sfoglia Lezioni per UDA</a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($_SESSION['role'] === 'teacher'): ?>
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">Gestisci Esercizi</div>
                            <div class="card-body feature-slot d-flex flex-column justify-content-center align-items-center">
                                <p>Crea, assegna e correggi esercizi.</p>
                                <a href="exercises/index.php" class="btn btn-info">Vai a Esercizi</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">Riscontro Alunni</div>
                            <div class="card-body feature-slot d-flex flex-column justify-content-center align-items-center">
                                <p>Visualizza il lavoro degli alunni sulle lezioni.</p>
                                <a href="feedback/index.php" class="btn btn-success">Vai a Riscontri</a>
                            </div>
                        </div>
                    </div>
                    <?php else: // Student view ?>
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">Esercizi</div>
                            <div class="card-body feature-slot d-flex flex-column justify-content-center align-items-center">
                                <p>Svolgi gli esercizi assegnati dal docente.</p>
                                <a href="exercises/index.php" class="btn btn-info">Vai a Esercizi</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">Riscontro Personale</div>
                            <div class="card-body feature-slot d-flex flex-column justify-content-center align-items-center">
                                <p>Rivedi i tuoi riscontri e le annotazioni sulle lezioni.</p>
                                <a href="feedback/index.php" class="btn btn-success">Rivedi i tuoi Riscontri</a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h4>Data e Ora</h4>
                    </div>
                    <div class="card-body text-center">
                        <h5 id="current-date"></h5>
                        <h3 id="current-time"></h3>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-header">
                        <h4>Calendario Mensile</h4>
                    </div>
                    <div class="card-body feature-slot">
                        <p class="text-muted">_placeholder per il calendario_</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateTime() {
            const now = new Date();
            const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('current-date').innerText = now.toLocaleDateString('it-IT', dateOptions);
            document.getElementById('current-time').innerText = now.toLocaleTimeString('it-IT');
        }
        setInterval(updateTime, 1000);
        updateTime(); // initial call
    </script>
<?php require_once 'footer.php'; ?>
