<?php
session_start();
require_once '../src/Database.php';
require_once '../src/Lesson.php';

header('Content-Type: application/json');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$dataId = $input['id'] ?? null;
$userId = $_SESSION['id'];

if (!$dataId) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Get the database connection
$db = Database::getInstance()->getConnection();
$lesson_manager = new Lesson($db);
$result = $lesson_manager->deleteStudentData($userId, $dataId);

if ($result === true) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $result]);
}
