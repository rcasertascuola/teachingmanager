<?php
header('Content-Type: application/json');

require_once 'src/Database.php';
require_once 'src/Disciplina.php';

$db = Database::getInstance()->getConnection();
$disciplina_manager = new Disciplina($db);

$discipline = $disciplina_manager->findAll();

echo json_encode($discipline);
