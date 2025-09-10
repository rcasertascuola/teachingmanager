<?php
require_once '../src/Database.php';

session_start();

// Auth check
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== 'teacher') {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    if (empty($_POST['verifica_id']) || empty($_POST['user_id']) || empty($_POST['data_svolgimento']) || !isset($_POST['punteggio_totale'])) {
        $_SESSION['feedback'] = [
            'type' => 'danger',
            'message' => 'Tutti i campi obbligatori devono essere compilati.'
        ];
        // Redirect back to the last known verifica_id if possible
        $redirect_id = !empty($_POST['verifica_id']) ? '?id=' . $_POST['verifica_id'] : '';
        header('Location: registro.php' . $redirect_id);
        exit;
    }

    $db = new Database();
    $conn = $db->getConnection();

    $verifica_id = (int)$_POST['verifica_id'];
    $user_id = (int)$_POST['user_id'];
    $data_svolgimento = $_POST['data_svolgimento'];
    $punteggio_totale = (float)$_POST['punteggio_totale'];
    $note = trim($_POST['note']);
    $corretto_da = $_SESSION['id']; // Teacher's ID from session

    $sql = "INSERT INTO registri_verifiche (verifica_id, user_id, data_svolgimento, punteggio_totale, note, corretto_da)
            VALUES (:verifica_id, :user_id, :data_svolgimento, :punteggio_totale, :note, :corretto_da)";

    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':verifica_id' => $verifica_id,
            ':user_id' => $user_id,
            ':data_svolgimento' => $data_svolgimento,
            ':punteggio_totale' => $punteggio_totale,
            ':note' => $note,
            ':corretto_da' => $corretto_da
        ]);

        $_SESSION['feedback'] = [
            'type' => 'success',
            'message' => 'Valutazione salvata con successo.'
        ];

    } catch (PDOException $e) {
        // Handle potential duplicate entry error (verifica_id, user_id, data_svolgimento)
        if ($e->getCode() == 23000) {
            $message = 'Errore: Esiste già una valutazione per questo studente in questa data.';
        } else {
            $message = 'Errore nel salvataggio della valutazione: ' . $e->getMessage();
        }
        $_SESSION['feedback'] = [
            'type' => 'danger',
            'message' => $message
        ];
    }

    header('Location: registro.php?id=' . $verifica_id);
    exit;

} else {
    // Not a POST request
    header('Location: index.php');
    exit;
}
?>
