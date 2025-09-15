<?php
if (!defined('TESTING')) header("Content-Type: application/json; charset=UTF-8");

require_once '../src/Database.php';
require_once '../src/Appuntamento.php';

session_start();

$database = Database::getInstance();
$db = $database->getConnection();

$appuntamento = new Appuntamento($db);

$appuntamento->user_id = $_SESSION['id'];
$today = date('Y-m-d');

$query = "SELECT id, titolo, tipo, data_inizio, data_fine FROM appuntamenti
          WHERE user_id = :user_id AND DATE(data_inizio) = :today
          ORDER BY data_inizio ASC";

$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $appuntamento->user_id);
$stmt->bindParam(':today', $today);
$stmt->execute();

$num = $stmt->rowCount();

if ($num > 0) {
    $appuntamenti_arr = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $appuntamento_item = array(
            "id" => $id,
            "title" => $titolo,
            "start" => $data_inizio,
            "end" => $data_fine,
            "tipo" => $tipo
        );
        array_push($appuntamenti_arr, $appuntamento_item);
    }
    echo json_encode($appuntamenti_arr);
} else {
    echo json_encode(array());
}
?>
