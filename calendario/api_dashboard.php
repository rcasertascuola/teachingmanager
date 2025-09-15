<?php
header("Content-Type: application/json; charset=UTF-8");

require_once '../src/Database.php';
require_once '../src/Appuntamento.php';

session_start();

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit;
}

$database = Database::getInstance();
$db = $database->getConnection();

$appuntamento = new Appuntamento($db);

$user_id = $_SESSION['id'];
$today = date('Y-m-d');
$now = new DateTime();

$query = "SELECT id, titolo, tipo, data_inizio, data_fine FROM appuntamenti
          WHERE user_id = :user_id AND DATE(data_inizio) = :today
          ORDER BY data_inizio ASC";

$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->bindParam(':today', $today);
$stmt->execute();

$num = $stmt->rowCount();

$all_appointments = [];
$previous_appointment = null;
$next_appointment = null;

if ($num > 0) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $start_time = new DateTime($row['data_inizio']);
        $end_time = new DateTime($row['data_fine']);

        $appointment_item = [
            "id" => $row['id'],
            "title" => $row['titolo'],
            "start" => $row['data_inizio'],
            "end" => $row['data_fine'],
            "tipo" => $row['tipo']
        ];
        $all_appointments[] = $appointment_item;

        if ($start_time < $now) {
            $previous_appointment = $appointment_item;
        }

        if ($start_time > $now && $next_appointment === null) {
            $next_appointment = $appointment_item;
        }
    }
}

echo json_encode([
    "previous" => $previous_appointment,
    "next" => $next_appointment,
    "all_today" => $all_appointments
]);

?>
