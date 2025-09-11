<?php
require_once '../src/Database.php';
require_once '../src/Verifica.php';
include '../header.php';

// Auth check
if ($_SESSION['role'] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}

// Configuration for the generic index handler
$db = Database::getInstance()->getConnection();
$manager = new Verifica($db);

$page_title = 'Gestione Verifiche';
$entity_name = 'Verifica';
$columns = [
    'titolo' => 'Titolo',
    'tipo' => 'Tipo',
    'created_at' => 'Data Creazione'
];
$items = $manager->findAll();

$actions = function($item) {
    $is_teacher = isset($_SESSION['role']) && $_SESSION['role'] === 'teacher';
    $view_btn = '<a href="view.php?id='.$item->id.'" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>';
    $teacher_btns = '';
    if ($is_teacher) {
        $teacher_btns = '
            <a href="registro.php?id='.$item->id.'" class="btn btn-sm btn-success"><i class="fas fa-book"></i></a>
            <a href="edit.php?id='.$item->id.'" class="btn btn-sm btn-warning"><i class="fas fa-pencil-alt"></i></a>
            <a href="delete.php?id='.$item->id.'" class="btn btn-sm btn-danger" onclick="return confirm(\'Sei sicuro di voler cancellare questo elemento?\');"><i class="fas fa-trash"></i></a>
        ';
    }
    return $view_btn . $teacher_btns;
};

// Include the generic handler
require_once '../handlers/index_handler.php';
?>
