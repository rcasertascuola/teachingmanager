<?php

require_once __DIR__ . '/Module.php';

class Verifica
{
    // Database connection
    private $conn;
    private $module = null;

    // Properties matching the 'verifiche' table
    public $id;
    public $titolo;
    public $descrizione;
    public $tipo;
    public $created_at;
    public $updated_at;
    public $module_id;

    // Related data
    public $griglia = null; // This will hold the Griglia object

    public function __construct($db, $data = [])
    {
        $this->conn = $db;
        $this->id = $data['id'] ?? null;
        $this->titolo = $data['titolo'] ?? '';
        $this->descrizione = $data['descrizione'] ?? '';
        $this->tipo = $data['tipo'] ?? 'scritto';
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
        $this->module_id = $data['module_id'] ?? null;
    }

    /**
     * Find all verifiche.
     * For now, no pagination.
     * @return Verifica[]
     */
    public function findAll()
    {
        $stmt = $this->conn->prepare('SELECT * FROM verifiche ORDER BY created_at DESC');
        $stmt->execute();

        $verifiche_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $verifiche = [];

        foreach ($verifiche_data as $data) {
            $verifiche[] = new self($this->conn, $data);
        }

        return $verifiche;
    }

    /**
     * Find a single verifica by its ID, including related data.
     *
     * @param int $id
     * @return Verifica|null
     */
    public function findById($id)
    {
        $stmt = $this->conn->prepare('SELECT * FROM verifiche WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $verifica = new self($this->conn, $data);
            $verifica->loadRelatedData();
            return $verifica;
        }
        return null;
    }

    /**
     * Helper function to load all data related to a verifica.
     */
    private function loadRelatedData()
    {
        // Load Griglia and Descrittori
        $stmt_griglia = $this->conn->prepare('SELECT * FROM griglie_valutazione WHERE verifica_id = :id LIMIT 1');
        $stmt_griglia->execute(['id' => $this->id]);
        $griglia_data = $stmt_griglia->fetch(PDO::FETCH_ASSOC);

        if ($griglia_data) {
            $this->griglia = (object) $griglia_data; // Simple object for now
            $stmt_descrittori = $this->conn->prepare('SELECT * FROM griglia_descrittori WHERE griglia_id = :griglia_id ORDER BY id ASC');
            $stmt_descrittori->execute(['griglia_id' => $this->griglia->id]);
            $this->griglia->descrittori = $stmt_descrittori->fetchAll(PDO::FETCH_OBJ);
        }
    }

    /**
     * Save or update the verifica and all its related data.
     * @return bool True on success, false on failure.
     */
    public function save() {
        $this->conn->beginTransaction();

        try {
            if ($this->id) {
                $stmt = $this->conn->prepare('UPDATE verifiche SET titolo = :titolo, descrizione = :descrizione, tipo = :tipo, module_id = :module_id WHERE id = :id');
                $stmt->execute([
                    'titolo' => $this->titolo,
                    'descrizione' => $this->descrizione,
                    'tipo' => $this->tipo,
                    'module_id' => $this->module_id,
                    'id' => $this->id
                ]);
            } else {
                $stmt = $this->conn->prepare('INSERT INTO verifiche (titolo, descrizione, tipo, module_id) VALUES (:titolo, :descrizione, :tipo, :module_id)');
                $stmt->execute([
                    'titolo' => $this->titolo,
                    'descrizione' => $this->descrizione,
                    'tipo' => $this->tipo,
                    'module_id' => $this->module_id
                ]);
                $this->id = $this->conn->lastInsertId();
            }

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
    public function delete($id) {
        $stmt = $this->conn->prepare('DELETE FROM verifiche WHERE id = :id');
        return $stmt->execute(['id' => $id]);
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

    public function getModule()
    {
        if ($this->module === null && $this->module_id) {
            $this->module = (new Module($this->conn))->findById($this->module_id);
        }
        return $this->module;
    }

    public function getConoscenze()
    {
        if (!$this->getModule()) {
            return [];
        }
        return $this->getModule()->getConoscenze();
    }

    public function getAbilita()
    {
        if (!$this->getModule()) {
            return [];
        }
        return $this->getModule()->getAbilita();
    }

    public function getCompetenze()
    {
        if (!$this->getModule()) {
            return [];
        }
        return $this->getModule()->getCompetenze();
    }
}
