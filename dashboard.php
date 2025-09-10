<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Gestionale Studio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .feature-slot {
            min-height: 150px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Gestionale Studio</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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
</body>
</html>
