<?php
session_start();
require_once '../src/Database.php';
require_once '../src/Orario.php';
require_once '../src/Appuntamento.php';

$database = Database::getInstance();
$db = $database->getConnection();

$orario = new Orario($db);
$appuntamento = new Appuntamento($db);

$action = isset($_GET['action']) ? $_GET['action'] : 'save';

if ($action == 'save' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $orario->disciplina_id = $_POST['disciplina_id'];
    $orario->giorno_settimana = $_POST['giorno_settimana'];
    $orario->ora_inizio = $_POST['ora_inizio'];
    $orario->ora_fine = $_POST['ora_fine'];
    $orario->validita_inizio = $_POST['validita_inizio'];
    $orario->validita_fine = $_POST['validita_fine'];
    $orario->user_id = $_SESSION['id'];

    if ($orario->create()) {
        // Now, generate the appointments
        $begin = new DateTime($orario->validita_inizio);
        $end = new DateTime($orario->validita_fine);
        $end = $end->modify('+1 day');

        $interval = new DateInterval('P1D');
        $dateRange = new DatePeriod($begin, $interval, $end);

        foreach ($dateRange as $date) {
            if ($date->format('N') == $orario->giorno_settimana) {
                $appuntamento->titolo = "Lezione"; // Or get from discipline name
                $appuntamento->tipo = 'lezione';
                $appuntamento->data_inizio = $date->format('Y-m-d') . ' ' . $orario->ora_inizio;
                $appuntamento->data_fine = $date->format('Y-m-d') . ' ' . $orario->ora_fine;
                $appuntamento->descrizione = '';
                $appuntamento->disciplina_id = $orario->disciplina_id;
                $appuntamento->user_id = $_SESSION['id'];
                $appuntamento->create();
            }
        }
        if (!defined('TESTING')) header("Location: orario.php?status=success");
    } else {
        if (!defined('TESTING')) header("Location: orario.php?status=error");
    }
} elseif ($action == 'delete' && isset($_GET['id'])) {
    $orario->id = $_GET['id'];
    $orario->user_id = $_SESSION['id'];
    if ($orario->delete()) {
        // This is a simplified approach. A more robust solution would be to also delete the generated appointments.
        // For now, we will just delete the schedule rule.
        if (!defined('TESTING')) header("Location: orario.php?status=deleted");
    } else {
        if (!defined('TESTING')) header("Location: orario.php?status=delete_error");
    }
} else {
    if (!defined('TESTING')) header("Location: orario.php");
}
if (!defined('TESTING')) exit();
?>
