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
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h4>Prossimo Appuntamento</h4>
                            </div>
                            <div class="card-body" id="next-appointment">
                                <!-- Next appointment will be loaded here -->
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h4>Appuntamento Precedente</h4>
                            </div>
                            <div class="card-body" id="previous-appointment">
                                <!-- Previous appointment will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Spazio per future implementazioni -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Agenda della Settimana</h4>
                            </div>
                            <div class="card-body" style="min-height: 400px;">
                                <div id="dashboard-calendar"></div>
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

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
    <script>
        function updateTime() {
            const now = new Date();
            const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('current-date').innerText = now.toLocaleDateString('it-IT', dateOptions);
            document.getElementById('current-time').innerText = now.toLocaleTimeString('it-IT');
        }
        setInterval(updateTime, 1000);
        updateTime(); // initial call

        function fetchDashboardData() {
            fetch('/calendario/api_dashboard.php')
                .then(response => response.json())
                .then(data => {
                    // Populate next appointment
                    const nextContainer = document.getElementById('next-appointment');
                    if (data.next) {
                        const startTime = new Date(data.next.start).toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' });
                        nextContainer.innerHTML = `<p><strong>${data.next.title}</strong> alle ${startTime}</p>`;
                    } else {
                        nextContainer.innerHTML = '<p class="text-muted">Nessun altro appuntamento per oggi.</p>';
                    }

                    // Populate previous appointment
                    const prevContainer = document.getElementById('previous-appointment');
                    if (data.previous) {
                        const startTime = new Date(data.previous.start).toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' });
                        prevContainer.innerHTML = `<p><strong>${data.previous.title}</strong> alle ${startTime}</p>`;
                    } else {
                        prevContainer.innerHTML = '<p class="text-muted">Nessun appuntamento precedente oggi.</p>';
                    }

                    // Initialize weekly calendar
                    var calendarEl = document.getElementById('dashboard-calendar');
                    var calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'timeGridWeek',
                        headerToolbar: false,
                        events: data.all_today,
                        editable: false,
                        selectable: false,
                        height: 'auto',
                        allDaySlot: false
                    });
                    calendar.render();
                })
                .catch(error => console.error('Error fetching dashboard data:', error));
        }

        fetchDashboardData();
    </script>
<?php require_once 'footer.php'; ?>
