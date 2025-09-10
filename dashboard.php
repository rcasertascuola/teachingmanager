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

                <!-- Spazio per future implementazioni -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body feature-slot" style="min-height: 400px;">
                                <p class="text-muted">_placeholder per contenuti futuri_</p>
                            </div>
                        </div>
                    </div>
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
