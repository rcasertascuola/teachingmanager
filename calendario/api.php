<?php
if (!defined('TESTING')) header("Content-Type: application/json; charset=UTF-8");

require_once '../src/Database.php';
require_once '../src/Appuntamento.php';

$database = Database::getInstance();
$db = $database->getConnection();

$appuntamento = new Appuntamento($db);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Fetch events
        $stmt = $appuntamento->read();
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
                    "color" => getColorForTipo($tipo),
                    "tipo" => $tipo,
                    "description" => $descrizione,
                    "disciplina_id" => $disciplina_id
                );
                array_push($appuntamenti_arr, $appuntamento_item);
            }
            echo json_encode($appuntamenti_arr);
        } else {
            echo json_encode(array());
        }
        break;

    case 'POST':
        // Create event
        if (!empty($_POST)) {
            $data = (object)$_POST;
        } else {
            $data = json_decode(file_get_contents("php://input"));
        }

        if (!empty($data->titolo) && !empty($data->tipo) && !empty($data->data_inizio) && !empty($data->data_fine)) {
            session_start();
            $appuntamento->titolo = $data->titolo;
            $appuntamento->tipo = $data->tipo;
            $appuntamento->data_inizio = $data->data_inizio;
            $appuntamento->data_fine = $data->data_fine;
            $appuntamento->descrizione = isset($data->descrizione) ? $data->descrizione : null;
            $appuntamento->disciplina_id = isset($data->disciplina_id) ? $data->disciplina_id : null;
            $appuntamento->user_id = $_SESSION['id'];

            if ($appuntamento->create()) {
                http_response_code(201);
                echo json_encode(array("message" => "Appuntamento creato."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Impossibile creare l'appuntamento."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Dati incompleti."));
        }
        break;

    case 'PUT':
        // Update event
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->id) && !empty($data->titolo) && !empty($data->tipo) && !empty($data->data_inizio) && !empty($data->data_fine)) {
            $appuntamento->id = $data->id;
            $appuntamento->titolo = $data->titolo;
            $appuntamento->tipo = $data->tipo;
            $appuntamento->data_inizio = $data->data_inizio;
            $appuntamento->data_fine = $data->data_fine;
            $appuntamento->descrizione = isset($data->descrizione) ? $data->descrizione : null;
            $appuntamento->disciplina_id = isset($data->disciplina_id) ? $data->disciplina_id : null;

            if ($appuntamento->update()) {
                http_response_code(200);
                echo json_encode(array("message" => "Appuntamento aggiornato."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Impossibile aggiornare l'appuntamento."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Dati incompleti per l'aggiornamento."));
        }
        break;

    case 'DELETE':
        // Delete event
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->id)) {
            $appuntamento->id = $data->id;
            if ($appuntamento->delete()) {
                http_response_code(200);
                echo json_encode(array("message" => "Appuntamento eliminato."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Impossibile eliminare l'appuntamento."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "ID mancante."));
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(array("message" => "Metodo non consentito."));
        break;
}

function getColorForTipo($tipo) {
    switch ($tipo) {
        case 'lezione':
            return '#007bff'; // Blue
        case 'consiglio_di_classe':
            return '#ffc107'; // Yellow
        case 'dipartimento':
            return '#28a745'; // Green
        case 'collegio':
            return '#dc3545'; // Red
        default:
            return '#6c757d'; // Gray
    }
}
?>
