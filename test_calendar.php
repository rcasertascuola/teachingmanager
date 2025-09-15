<?php
define('TESTING', true);
session_start();
// Mock session
$_SESSION['id'] = 1; // Assuming user with ID 1 exists

require_once 'src/Database.php';
require_once 'src/Orario.php';
require_once 'src/Appuntamento.php';
require_once 'src/Disciplina.php';

$database = Database::getInstance();
$db = $database->getConnection();

// 1. Cleanup existing test discipline
$stmt = $db->prepare("DELETE FROM discipline WHERE nome = 'Test Discipline'");
$stmt->execute();
echo "Cleaned up any existing test discipline.\n";

// 2. Create a discipline for testing
$disciplina = new Disciplina($db);
$disciplina->nome = 'Test Discipline';
$disciplina->save();
$disciplina_id = $disciplina->id;
echo "Created discipline with ID: $disciplina_id\n";

// 2. Create a new lesson schedule
$_POST = [
    'disciplina_id' => $disciplina_id,
    'giorno_settimana' => 1, // Monday
    'ora_inizio' => '09:00:00',
    'ora_fine' => '10:00:00',
    'validita_inizio' => date('Y-m-d', strtotime('monday this week')),
    'validita_fine' => date('Y-m-d', strtotime('monday this week + 2 weeks')),
];
$_SERVER['REQUEST_METHOD'] = 'POST';
chdir('calendario');
include 'save_orario.php';
chdir('..');
echo "Ran save_orario.php to create schedule.\n";

// 3. Verify appointments are created
$appuntamento = new Appuntamento($db);
$stmt = $appuntamento->read();
$count = $stmt->rowCount();
echo "Found $count appointments after creating schedule.\n";
if ($count < 2) {
    echo "Error: Expected at least 2 appointments, found $count.\n";
}

// 4. Create a single appointment
$_POST = [
    'titolo' => 'Test Appointment',
    'tipo' => 'riunione',
    'data_inizio' => date('Y-m-d') . ' 15:00:00',
    'data_fine' => date('Y-m-d') . ' 16:00:00',
];
$_SERVER['REQUEST_METHOD'] = 'POST';
chdir('calendario');
include 'api.php';
chdir('..');
echo "Ran api.php to create a single appointment.\n";

// 5. Get today's appointments
$_SERVER['REQUEST_METHOD'] = 'GET';
chdir('calendario');
include 'api_today.php';
chdir('..');
echo "Ran api_today.php to get today's appointments.\n";

// Cleanup
$disciplina->delete($disciplina_id);
echo "Cleaned up test discipline.\n";

// Also need to clean up appointments and schedule
// For now, this is a basic test
?>
