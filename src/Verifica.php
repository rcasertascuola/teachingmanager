<?php

class Verifica
{
    // Database connection
    private $conn;

    // Properties matching the 'verifiche' table
    public $id;
    public $titolo;
    public $descrizione;
    public $tipo;
    public $created_at;
    public $updated_at;

    // Related data
    public $abilita_ids = [];
    public $competenza_ids = [];
    public $griglia = null; // This will hold the Griglia object

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Find all verifiche.
     * For now, no pagination.
     * @return Verifica[]
     */
    public static function findAll()
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare('SELECT * FROM verifiche ORDER BY created_at DESC');
        $stmt->execute();

        $verifiche_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $verifiche = [];

        foreach ($verifiche_data as $data) {
            $verifica = new self($pdo);
            $verifica->id = $data['id'];
            $verifica->titolo = $data['titolo'];
            $verifica->descrizione = $data['descrizione'];
            $verifica->tipo = $data['tipo'];
            $verifica->created_at = $data['created_at'];
            $verifica->updated_at = $data['updated_at'];
            $verifiche[] = $verifica;
        }

        return $verifiche;
    }

    /**
     * Find a single verifica by its ID, including related data.
     *
     * @param int $id
     * @return Verifica|null
     */
    public static function findById($id)
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare('SELECT * FROM verifiche WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $verifica = new self($pdo);
            $verifica->id = $data['id'];
            $verifica->titolo = $data['titolo'];
            $verifica->descrizione = $data['descrizione'];
            $verifica->tipo = $data['tipo'];
            $verifica->created_at = $data['created_at'];
            $verifica->updated_at = $data['updated_at'];

            // Load related data
            $verifica->loadRelatedData($pdo);
            return $verifica;
        }
        return null;
    }

    /**
     * Helper function to load all data related to a verifica.
     */
    private function loadRelatedData($pdo)
    {
        // Load Abilita IDs
        $stmt_abilita = $pdo->prepare('SELECT abilita_id FROM verifica_abilita WHERE verifica_id = :id');
        $stmt_abilita->execute(['id' => $this->id]);
        $this->abilita_ids = $stmt_abilita->fetchAll(PDO::FETCH_COLUMN, 0);

        // Load Competenza IDs
        $stmt_competenze = $pdo->prepare('SELECT competenza_id FROM verifica_competenze WHERE verifica_id = :id');
        $stmt_competenze->execute(['id' => $this->id]);
        $this->competenza_ids = $stmt_competenze->fetchAll(PDO::FETCH_COLUMN, 0);

        // Load Griglia and Descrittori
        $stmt_griglia = $pdo->prepare('SELECT * FROM griglie_valutazione WHERE verifica_id = :id LIMIT 1');
        $stmt_griglia->execute(['id' => $this->id]);
        $griglia_data = $stmt_griglia->fetch(PDO::FETCH_ASSOC);

        if ($griglia_data) {
            $this->griglia = (object) $griglia_data; // Simple object for now
            $stmt_descrittori = $pdo->prepare('SELECT * FROM griglia_descrittori WHERE griglia_id = :griglia_id ORDER BY id ASC');
            $stmt_descrittori->execute(['griglia_id' => $this->griglia->id]);
            $this->griglia->descrittori = $stmt_descrittori->fetchAll(PDO::FETCH_OBJ);
        }
    }

    // save and delete methods will be added here later.

    /**
     * Save or update the verifica and all its related data.
     * @return bool True on success, false on failure.
     */
    public function save() {
        $this->conn->beginTransaction();

        try {
            if ($this->id) {
                $stmt = $this->conn->prepare('UPDATE verifiche SET titolo = :titolo, descrizione = :descrizione, tipo = :tipo WHERE id = :id');
                $stmt->execute([
                    'titolo' => $this->titolo,
                    'descrizione' => $this->descrizione,
                    'tipo' => $this->tipo,
                    'id' => $this->id
                ]);
            } else {
                $stmt = $this->conn->prepare('INSERT INTO verifiche (titolo, descrizione, tipo) VALUES (:titolo, :descrizione, :tipo)');
                $stmt->execute([
                    'titolo' => $this->titolo,
                    'descrizione' => $this->descrizione,
                    'tipo' => $this->tipo
                ]);
                $this->id = $this->conn->lastInsertId();
            }

            // Sync abilities and competencies
            $this->syncRelatedData('verifica_abilita', 'abilita_id', $this->abilita_ids);
            $this->syncRelatedData('verifica_competenze', 'competenza_id', $this->competenza_ids);

            // Sync griglia
            if ($this->griglia) {
                $this->saveGriglia();
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            // In a real app, you would log the error message ($e->getMessage())
            return false;
        }
    }

    /**
     * Delete a verifica by its ID.
     * @param int $id
     * @return bool
     */
    public static function delete($id) {
        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare('DELETE FROM verifiche WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    private function syncRelatedData($tableName, $relatedIdColumn, $relatedIds) {
        $stmt = $this->conn->prepare("DELETE FROM {$tableName} WHERE verifica_id = :id");
        $stmt->execute(['id' => $this->id]);

        if (!empty($relatedIds)) {
            $sql = "INSERT INTO {$tableName} (verifica_id, {$relatedIdColumn}) VALUES ";
            $placeholders = [];
            $values = [];
            foreach ($relatedIds as $relatedId) {
                $placeholders[] = '(?, ?)';
                $values[] = $this->id;
                $values[] = $relatedId;
            }
            $sql .= implode(', ', $placeholders);
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($values);
        }
    }

    private function saveGriglia() {
        // Check if griglia exists, if not, insert it
        $stmt = $this->conn->prepare('SELECT id FROM griglie_valutazione WHERE verifica_id = :verifica_id');
        $stmt->execute(['verifica_id' => $this->id]);
        $griglia_id = $stmt->fetchColumn();

        if ($griglia_id) {
            $stmt = $this->conn->prepare('UPDATE griglie_valutazione SET nome = :nome WHERE id = :id');
            $stmt->execute(['nome' => $this->griglia['nome'], 'id' => $griglia_id]);
        } else {
            $stmt = $this->conn->prepare('INSERT INTO griglie_valutazione (verifica_id, nome) VALUES (:verifica_id, :nome)');
            $stmt->execute(['verifica_id' => $this->id, 'nome' => $this->griglia['nome']]);
            $griglia_id = $this->conn->lastInsertId();
        }

        // Sync descrittori
        // First, delete all existing descrittori for this griglia
        $stmt_delete = $this->conn->prepare('DELETE FROM griglia_descrittori WHERE griglia_id = :griglia_id');
        $stmt_delete->execute(['griglia_id' => $griglia_id]);

        // Then, insert the new ones
        if (!empty($this->griglia['descrittori'])) {
            $sql = 'INSERT INTO griglia_descrittori (griglia_id, descrittore, punteggio_max) VALUES ';
            $placeholders = [];
            $values = [];
            foreach ($this->griglia['descrittori'] as $d) {
                $placeholders[] = '(?, ?, ?)';
                $values[] = $griglia_id;
                $values[] = $d['descrittore'];
                $values[] = $d['punteggio_max'];
            }
            $sql .= implode(', ', $placeholders);
            $stmt_insert = $this->conn->prepare($sql);
            $stmt_insert->execute($values);
        }
    }

    /**
     * Get all evaluation records for this verifica.
     * @return array
     */
    public function getRegistroRecords() {
        $sql = "SELECT r.data_svolgimento, r.punteggio_totale, r.note, u.username
                FROM registri_verifiche r
                JOIN users u ON r.user_id = u.id
                WHERE r.verifica_id = :verifica_id
                ORDER BY r.data_svolgimento DESC, u.username ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':verifica_id' => $this->id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
