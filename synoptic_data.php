<?php
header('Content-Type: application/json');

require_once 'src/Database.php';
require_once 'src/Uda.php';
require_once 'src/Module.php';
require_once 'src/Lesson.php';
require_once 'src/Conoscenza.php';
require_once 'src/Abilita.php';
require_once 'src/Competenza.php';
require_once 'src/Disciplina.php';
require_once 'src/Exercise.php';

$db = Database::getInstance()->getConnection();
$pdo = $db; // for compatibility with existing functions

$lesson_manager = new Lesson($db);
$module_manager = new Module($db);
$uda_manager = new Uda($db);
$exercise_manager = new Exercise($db);

$anno_corso = isset($_GET['anno_corso']) && !empty($_GET['anno_corso']) ? (int)$_GET['anno_corso'] : null;

function getCompetenzeByAbilita($pdo, $abilitaId) {
    $stmt = $pdo->prepare('
        SELECT c.* FROM competenze c
        JOIN competenza_abilita ca ON c.id = ca.competenza_id
        WHERE ca.abilita_id = :abilita_id
    ');
    $stmt->execute(['abilita_id' => $abilitaId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCompetenzeByConoscenza($pdo, $conoscenzaId) {
    $stmt = $pdo->prepare('
        SELECT c.* FROM competenze c
        JOIN competenza_conoscenze cc ON c.id = cc.competenza_id
        WHERE cc.conoscenza_id = :conoscenza_id
    ');
    $stmt->execute(['conoscenza_id' => $conoscenzaId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getDisciplineByCompetenza($pdo, $competenzaId) {
    $stmt = $pdo->prepare('
        SELECT d.* FROM discipline d
        JOIN competenza_discipline cd ON d.id = cd.disciplina_id
        WHERE cd.competenza_id = :competenza_id
    ');
    $stmt->execute(['competenza_id' => $competenzaId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$udas = $uda_manager->findAll();
$result = [];

foreach ($udas as $uda) {
    $modules = $module_manager->findByUdaId($uda->id);
    $moduleData = [];

    foreach ($modules as $module) {
        $stmt = $pdo->prepare('SELECT lesson_id FROM module_lessons WHERE module_id = :module_id');
        $stmt->execute(['module_id' => $module->id]);
        $lessonIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $lessonData = [];

        if (!empty($lessonIds)) {
            foreach ($lessonIds as $lessonId) {
                $lesson = $lesson_manager->findById($lessonId);
                if ($lesson) {
                    // Fetch conoscenze
                    $sqlConoscenze = '
                        SELECT c.* FROM conoscenze c
                        JOIN lezione_conoscenze lc ON c.id = lc.conoscenza_id
                        LEFT JOIN conoscenza_anni_corso cac ON c.id = cac.conoscenza_id
                        WHERE lc.lezione_id = :lezione_id';
                    if ($anno_corso) {
                        $sqlConoscenze .= ' AND cac.anno_corso = :anno_corso';
                    }
                    $stmt = $pdo->prepare($sqlConoscenze);
                    $params = ['lezione_id' => $lesson->id];
                    if ($anno_corso) {
                        $params['anno_corso'] = $anno_corso;
                    }
                    $stmt->execute($params);
                    $conoscenze = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($conoscenze as &$conoscenza) {
                        $conoscenza['competenze'] = getCompetenzeByConoscenza($pdo, $conoscenza['id']);
                        foreach ($conoscenza['competenze'] as &$competenza) {
                            $competenza['discipline'] = getDisciplineByCompetenza($pdo, $competenza['id']);
                        }
                    }


                    // Fetch abilità
                    $sqlAbilita = '
                        SELECT a.* FROM abilita a
                        JOIN lezione_abilita la ON a.id = la.abilita_id
                        LEFT JOIN abilita_anni_corso aac ON a.id = aac.abilita_id
                        WHERE la.lezione_id = :lezione_id';
                    if ($anno_corso) {
                        $sqlAbilita .= ' AND aac.anno_corso = :anno_corso';
                    }
                    $stmt = $pdo->prepare($sqlAbilita);
                    $params = ['lezione_id' => $lesson->id];
                    if ($anno_corso) {
                        $params['anno_corso'] = $anno_corso;
                    }
                    $stmt->execute($params);
                    $abilita = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($abilita as &$skill) {
                        $skill['competenze'] = getCompetenzeByAbilita($pdo, $skill['id']);
                        foreach ($skill['competenze'] as &$competenza) {
                            $competenza['discipline'] = getDisciplineByCompetenza($pdo, $competenza['id']);
                        }
                    }

                    // Fetch exercises
                    $exercises = $exercise_manager->findForLesson($lesson->id);

                    $lessonData[] = [
                        'id' => $lesson->id,
                        'title' => $lesson->title,
                        'content' => $lesson->content,
                        'conoscenze' => $conoscenze,
                        'abilita' => $abilita,
                        'exercises' => $exercises,
                    ];
                }
            }
        }

        $moduleData[] = [
            'id' => $module->id,
            'name' => $module->name,
            'description' => $module->description,
            'lessons' => $lessonData,
        ];
    }

    $result[] = [
        'id' => $uda->id,
        'name' => $uda->name,
        'description' => $uda->description,
        'modules' => $moduleData,
    ];
}

echo json_encode($result, JSON_PRETTY_PRINT);
