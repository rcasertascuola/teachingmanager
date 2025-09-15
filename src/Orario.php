<?php

class Orario {
    private $conn;
    private $table_name = "orari_lezioni";

    public $id;
    public $disciplina_id;
    public $giorno_settimana;
    public $ora_inizio;
    public $ora_fine;
    public $validita_inizio;
    public $validita_fine;
    public $user_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    function read() {
        $query = "SELECT o.id, o.disciplina_id, d.nome as disciplina_nome, o.giorno_settimana, o.ora_inizio, o.ora_fine, o.validita_inizio, o.validita_fine
                  FROM " . $this->table_name . " o
                  LEFT JOIN discipline d ON o.disciplina_id = d.id
                  WHERE o.user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->user_id);
        $stmt->execute();
        return $stmt;
    }

    function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET disciplina_id=:disciplina_id, giorno_settimana=:giorno_settimana, ora_inizio=:ora_inizio, ora_fine=:ora_fine,
                      validita_inizio=:validita_inizio, validita_fine=:validita_fine, user_id=:user_id";
        $stmt = $this->conn->prepare($query);

        // bind values
        $stmt->bindParam(":disciplina_id", $this->disciplina_id);
        $stmt->bindParam(":giorno_settimana", $this->giorno_settimana);
        $stmt->bindParam(":ora_inizio", $this->ora_inizio);
        $stmt->bindParam(":ora_fine", $this->ora_fine);
        $stmt->bindParam(":validita_inizio", $this->validita_inizio);
        $stmt->bindParam(":validita_fine", $this->validita_fine);
        $stmt->bindParam(":user_id", $this->user_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->bindParam(2, $this->user_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
