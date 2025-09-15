<?php
// import_module.php (Refactored for two-phase import)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Include dependencies
require_once 'config/config.php';
require_once 'src/Database.php';
// We don't need the model classes for this script anymore

// --- Helper Functions ---

function send_json_response($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// --- Main Controller ---

$db = new Database();
$pdo = $db->getConnection();

$action = $_POST['action'] ?? 'analyze';

// Get JSON data from uploaded file
if (!isset($_FILES['import_file'])) {
    send_json_response(['status' => 'error', 'message' => 'File di importazione non fornito.']);
}
$file_path = $_FILES['import_file']['tmp_name'];
$json_content = file_get_contents($file_path);
$data = json_decode($json_content, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    send_json_response(['status' => 'error', 'message' => 'Errore nel parsing del file JSON.']);
}


if ($action === 'analyze') {
    // =================================================================
    // --- ANALYSIS MODE ---
    // =================================================================
    try {
        $report = [
            'status' => 'analysis_complete',
            'creations' => [],
            'links' => [],
            'ambiguities' => []
        ];

        // Helper function for analysis
        function analyze_item(PDO $pdo, string $table, array $item_data, string $unique_field = 'nome') {
            $term = $item_data[$unique_field];
            $stmt = $pdo->prepare("SELECT id, nome, descrizione FROM `$table` WHERE `$unique_field` = ?");
            $stmt->execute([$term]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($results) === 0) {
                return ['status' => 'create', 'data' => $item_data];
            } elseif (count($results) === 1) {
                return ['status' => 'link', 'data' => $results[0]];
            } else {
                return ['status' => 'ambiguous', 'term' => $term, 'options' => $results];
            }
        }

        $entity_types = ['conoscenze', 'abilita', 'tipologie_competenze', 'discipline'];
        foreach ($entity_types as $entity_type) {
            if (empty($data[$entity_type])) continue;
            foreach ($data[$entity_type] as $item) {
                 $analysis = analyze_item($pdo, $entity_type, $item);
                 if ($analysis['status'] === 'ambiguous') {
                     $report['ambiguities'][$entity_type][] = $analysis;
                 } // Creations and links are implicitly handled by being in the JSON
            }
        }

        // For competencies, we just check the main competency, not the dependencies yet
        if (!empty($data['competenze'])) {
            foreach($data['competenze'] as $item) {
                $analysis = analyze_item($pdo, 'competenze', $item);
                if ($analysis['status'] === 'ambiguous') {
                    $report['ambiguities']['competenze'][] = $analysis;
                }
            }
        }

        // Add summary to the report
        $report['summary'] = [
            'conoscenze' => count($data['conoscenze'] ?? []),
            'abilita' => count($data['abilita'] ?? []),
            'competenze' => count($data['competenze'] ?? []),
            'module_name' => $data['modulo']['nome'] ?? 'N/A'
        ];

        send_json_response($report);

    } catch (Exception $e) {
        send_json_response(['status' => 'error', 'message' => 'Errore durante l\'analisi: ' . $e->getMessage()]);
    }

} elseif ($action === 'execute') {
    // =================================================================
    // --- EXECUTION MODE ---
    // =================================================================

    // This part uses the logic from the previous implementation

    $resolutions = isset($_POST['resolutions']) ? json_decode($_POST['resolutions'], true) : [];

    function search_or_create(PDO $pdo, string $table, array $data, string $unique_field = 'nome', array $resolutions = []): int {
        $term = $data[$unique_field];
        if (isset($resolutions[$table][$term])) {
            return (int)$resolutions[$table][$term];
        }
        $stmt = $pdo->prepare("SELECT id FROM `$table` WHERE `$unique_field` = ?");
        $stmt->execute([$term]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($results) === 1) {
            return (int)$results[0]['id'];
        }
        if (count($results) > 1) {
            // This should not happen in execute mode if UI works correctly
            throw new Exception("Ambiguità non risolta per $term in $table");
        }
        $columns = '`' . implode('`, `', array_keys($data)) . '`';
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $stmt = $pdo->prepare("INSERT INTO `$table` ($columns) VALUES ($placeholders)");
        $stmt->execute(array_values($data));
        return (int)$pdo->lastInsertId();
    }

    function create_relationship(PDO $pdo, string $table, string $col1, int $id1, string $col2, int $id2) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table WHERE $col1 = ? AND $col2 = ?");
        $stmt->execute([$id1, $id2]);
        if ($stmt->fetchColumn() == 0) {
            $stmt = $pdo->prepare("INSERT INTO $table ($col1, $col2) VALUES (?, ?)");
            $stmt->execute([$id1, $id2]);
        }
    }

    try {
        $pdo->beginTransaction();
        $id_map = []; // To hold IDs created/found during this transaction

        // Process atomic elements first
        $entities_to_process = ['conoscenze', 'abilita'];
        foreach ($entities_to_process as $entity) {
            if (!empty($data[$entity])) {
                foreach ($data[$entity] as $item) {
                    $id = search_or_create($pdo, $entity, $item, 'nome', $resolutions);
                    $id_map[$entity][$item['nome']] = $id;
                }
            }
        }

        // Process Competenze
        if (!empty($data['competenze'])) {
            foreach ($data['competenze'] as $item) {
                $tipologia_id = null;
                if (!empty($item['tipologia'])) {
                    $tipologia_id = search_or_create($pdo, 'tipologie_competenze', ['nome' => $item['tipologia']], 'nome', $resolutions);
                }
                $comp_data = ['nome' => $item['nome'], 'descrizione' => $item['descrizione'], 'tipologia_id' => $tipologia_id];
                $comp_id = search_or_create($pdo, 'competenze', $comp_data, 'nome', $resolutions);
                $id_map['competenze'][$item['nome']] = $comp_id;

                // Link relationships
                if (!empty($item['conoscenze_associate_per_nome'])) {
                    foreach($item['conoscenze_associate_per_nome'] as $nome) {
                        if(isset($id_map['conoscenze'][$nome])) create_relationship($pdo, 'competenza_conoscenze', 'competenza_id', $comp_id, 'conoscenza_id', $id_map['conoscenze'][$nome]);
                    }
                }
                if (!empty($item['abilita_associate_per_nome'])) {
                    foreach($item['abilita_associate_per_nome'] as $nome) {
                        if(isset($id_map['abilita'][$nome])) create_relationship($pdo, 'competenza_abilita', 'competenza_id', $comp_id, 'abilita_id', $id_map['abilita'][$nome]);
                    }
                }
            }
        }

        // Process Module -> UDA -> Lesson hierarchy
        if (!empty($data['modulo'])) {
            $mod_data = $data['modulo'];
            $disciplina_id = null;
            if(!empty($mod_data['disciplina'])) {
                $disciplina_id = search_or_create($pdo, 'discipline', ['nome' => $mod_data['disciplina']], 'nome', $resolutions);
            }
            $new_mod_data = ['name' => $mod_data['nome'], 'description' => $mod_data['descrizione'], 'disciplina_id' => $disciplina_id, 'anno_corso' => $mod_data['anno_corso']];
            $mod_id = search_or_create($pdo, 'modules', $new_mod_data, 'name', $resolutions);

            if(!empty($mod_data['udas'])) {
                foreach($mod_data['udas'] as $uda_data) {
                    $new_uda_data = ['module_id' => $mod_id, 'name' => $uda_data['nome'], 'description' => $uda_data['descrizione']];
                    $uda_id = search_or_create($pdo, 'udas', $new_uda_data, 'name', $resolutions);

                    if(!empty($uda_data['lezioni'])) {
                        foreach($uda_data['lezioni'] as $lezione_data) {
                            $new_lezione_data = ['uda_id' => $uda_id, 'title' => $lezione_data['titolo'], 'content' => $lezione_data['contenuto']];
                            $lezione_id = search_or_create($pdo, 'lessons', $new_lezione_data, 'title', $resolutions);

                            if(!empty($lezione_data['conoscenze_associate_per_nome'])) {
                                foreach($lezione_data['conoscenze_associate_per_nome'] as $nome) {
                                    if(isset($id_map['conoscenze'][$nome])) create_relationship($pdo, 'lezione_conoscenze', 'lezione_id', $lezione_id, 'conoscenza_id', $id_map['conoscenze'][$nome]);
                                }
                            }
                            if(!empty($lezione_data['abilita_associate_per_nome'])) {
                                foreach($lezione_data['abilita_associate_per_nome'] as $nome) {
                                    if(isset($id_map['abilita'][$nome])) create_relationship($pdo, 'lezione_abilita', 'lezione_id', $lezione_id, 'abilita_id', $id_map['abilita'][$nome]);
                                }
                            }
                        }
                    }
                }
            }
        }

        $pdo->commit();
        send_json_response(['status' => 'success', 'message' => 'Importazione completata con successo.']);

    } catch (Exception $e) {
        $pdo->rollBack();
        send_json_response(['status' => 'error', 'message' => 'Errore durante l'esecuzione: ' . $e->getMessage()]);
    }
}
?>
