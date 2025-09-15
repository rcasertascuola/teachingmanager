<?php
require_once '../header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-9">
            <div id="calendar"></div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    Legenda
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item border-0">
                            <span class="badge bg-primary">&nbsp;</span> Lezione
                        </li>
                        <li class="list-group-item border-0">
                            <span class="badge bg-warning">&nbsp;</span> Consiglio di Classe
                        </li>
                        <li class="list-group-item border-0">
                            <span class="badge bg-success">&nbsp;</span> Dipartimento
                        </li>
                        <li class="list-group-item border-0">
                            <span class="badge bg-danger">&nbsp;</span> Collegio
                        </li>
                        <li class="list-group-item border-0">
                            <span class="badge bg-secondary">&nbsp;</span> Altro
                        </li>
                    </ul>
                    <hr>
                    <div class="d-grid gap-2">
                        <button class="btn btn-success" id="addAppointmentBtn">Aggiungi Appuntamento</button>
                        <a href="orario.php" class="btn btn-primary">Gestisci Orario Lezioni</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Event Modal -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventModalLabel">Aggiungi/Modifica Appuntamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="eventForm">
                    <input type="hidden" id="eventId" name="id">
                    <div class="mb-3">
                        <label for="eventTitle" class="form-label">Titolo</label>
                        <input type="text" class="form-control" id="eventTitle" required>
                    </div>
                    <div class="mb-3">
                        <label for="eventType" class="form-label">Tipo</label>
                        <select class="form-select" id="eventType" required>
                            <option value="lezione">Lezione</option>
                            <option value="consiglio_di_classe">Consiglio di Classe</option>
                            <option value="dipartimento">Dipartimento</option>
                            <option value="collegio">Collegio</option>
                            <option value="altro">Altro</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="eventStart" class="form-label">Inizio</label>
                        <input type="datetime-local" class="form-control" id="eventStart" required>
                    </div>
                    <div class="mb-3">
                        <label for="eventEnd" class="form-label">Fine</label>
                        <input type="datetime-local" class="form-control" id="eventEnd" required>
                    </div>
                    <div class="mb-3">
                        <label for="eventDescription" class="form-label">Descrizione</label>
                        <textarea class="form-control" id="eventDescription"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                <button type="button" class="btn btn-primary" id="saveEvent">Salva</button>
                <button type="button" class="btn btn-danger" id="deleteEvent" style="display:none;">Elimina</button>
            </div>
        </div>
    </div>
</div>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var eventModal = new bootstrap.Modal(document.getElementById('eventModal'));
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        events: 'api.php',
        editable: true,
        selectable: true,
        dateClick: function(info) {
            document.getElementById('eventForm').reset();
            document.getElementById('eventId').value = '';
            document.getElementById('deleteEvent').style.display = 'none';
            document.getElementById('eventStart').value = info.dateStr + 'T00:00';
            document.getElementById('eventEnd').value = info.dateStr + 'T01:00';
            eventModal.show();
        },
        eventClick: function(info) {
            document.getElementById('eventForm').reset();
            document.getElementById('eventId').value = info.event.id;
            document.getElementById('eventTitle').value = info.event.title;
            document.getElementById('eventType').value = info.event.extendedProps.tipo;
            document.getElementById('eventStart').value = new Date(info.event.start).toISOString().slice(0,16);
            document.getElementById('eventEnd').value = new Date(info.event.end).toISOString().slice(0,16);
            document.getElementById('eventDescription').value = info.event.extendedProps.description;
            document.getElementById('deleteEvent').style.display = 'inline-block';
            eventModal.show();
        }
    });
    calendar.render();

    document.getElementById('addAppointmentBtn').addEventListener('click', function() {
        document.getElementById('eventForm').reset();
        document.getElementById('eventId').value = '';
        document.getElementById('deleteEvent').style.display = 'none';

        // Set default start and end times for new appointments
        const now = new Date();
        const year = now.getFullYear();
        const month = (now.getMonth() + 1).toString().padStart(2, '0');
        const day = now.getDate().toString().padStart(2, '0');
        const hours = now.getHours().toString().padStart(2, '0');

        document.getElementById('eventStart').value = `${year}-${month}-${day}T${hours}:00`;
        document.getElementById('eventEnd').value = `${year}-${month}-${day}T${(parseInt(hours) + 1).toString().padStart(2, '0')}:00`;

        eventModal.show();
    });

    document.getElementById('saveEvent').addEventListener('click', function() {
        var id = document.getElementById('eventId').value;
        var data = {
            titolo: document.getElementById('eventTitle').value,
            tipo: document.getElementById('eventType').value,
            data_inizio: document.getElementById('eventStart').value,
            data_fine: document.getElementById('eventEnd').value,
            descrizione: document.getElementById('eventDescription').value
        };

        var url = id ? 'api.php' : 'api.php';
        var method = id ? 'PUT' : 'POST';
        if(id) data.id = id;

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data),
        })
        .then(response => response.json())
        .then(data => {
            console.log('Success:', data);
            calendar.refetchEvents();
            eventModal.hide();
        })
        .catch((error) => {
            console.error('Error:', error);
        });
    });

    document.getElementById('deleteEvent').addEventListener('click', function() {
        var id = document.getElementById('eventId').value;
        if (confirm('Sei sicuro di voler eliminare questo appuntamento?')) {
            fetch('api.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({id: id}),
            })
            .then(response => response.json())
            .then(data => {
                console.log('Success:', data);
                calendar.refetchEvents();
                eventModal.hide();
            })
            .catch((error) => {
                console.error('Error:', error);
            });
        }
    });
});
</script>

<?php require_once '../footer.php'; ?>
