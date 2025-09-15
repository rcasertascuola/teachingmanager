<?php

class Appuntamento {
    private $conn;
    private $table_name = "appuntamenti";

    public $id;
    public $titolo;
    public $tipo;
    public $data_inizio;
    public $data_fine;
    public $descrizione;
    public $disciplina_id;
    public $user_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    function read() {
        $query = "SELECT a.id, a.titolo, a.tipo, a.data_inizio, a.data_fine, a.descrizione, a.disciplina_id, a.user_id
                  FROM " . $this->table_name . " a";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET titolo=:titolo, tipo=:tipo, data_inizio=:data_inizio, data_fine=:data_fine, descrizione=:descrizione, disciplina_id=:disciplina_id, user_id=:user_id";
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->titolo=htmlspecialchars(strip_tags($this->titolo));
        $this->tipo=htmlspecialchars(strip_tags($this->tipo));
        $this->descrizione=htmlspecialchars(strip_tags($this->descrizione));

        // bind values
        $stmt->bindParam(":titolo", $this->titolo);
        $stmt->bindParam(":tipo", $this->tipo);
        $stmt->bindParam(":data_inizio", $this->data_inizio);
        $stmt->bindParam(":data_fine", $this->data_fine);
        $stmt->bindParam(":descrizione", $this->descrizione);
        $stmt->bindParam(":disciplina_id", $this->disciplina_id);
        $stmt->bindParam(":user_id", $this->user_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET titolo = :titolo, tipo = :tipo, data_inizio = :data_inizio, data_fine = :data_fine, descrizione = :descrizione, disciplina_id = :disciplina_id
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->titolo=htmlspecialchars(strip_tags($this->titolo));
        $this->tipo=htmlspecialchars(strip_tags($this->tipo));
        $this->descrizione=htmlspecialchars(strip_tags($this->descrizione));

        // bind values
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":titolo", $this->titolo);
        $stmt->bindParam(":tipo", $this->tipo);
        $stmt->bindParam(":data_inizio", $this->data_inizio);
        $stmt->bindParam(":data_fine", $this->data_fine);
        $stmt->bindParam(":descrizione", $this->descrizione);
        $stmt->bindParam(":disciplina_id", $this->disciplina_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
