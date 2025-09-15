<?php

class Documento
{
    private $conn;
    private $table_name = "files";

    public $id;
    public $filename;
    public $file_type;
    public $size;
    public $topic;
    public $description;
    public $upload_date;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " SET filename=:filename, file_type=:file_type, size=:size, topic=:topic, description=:description";
        $stmt = $this->conn->prepare($query);

        $this->filename = htmlspecialchars(strip_tags($this->filename));
        $this->file_type = htmlspecialchars(strip_tags($this->file_type));
        $this->size = htmlspecialchars(strip_tags($this->size));
        $this->topic = htmlspecialchars(strip_tags($this->topic));
        $this->description = htmlspecialchars(strip_tags($this->description));

        $stmt->bindParam(":filename", $this->filename);
        $stmt->bindParam(":file_type", $this->file_type);
        $stmt->bindParam(":size", $this->size);
        $stmt->bindParam(":topic", $this->topic);
        $stmt->bindParam(":description", $this->description);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function readAll()
    {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY upload_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->filename = $row['filename'];
        $this->file_type = $row['file_type'];
        $this->size = $row['size'];
        $this->topic = $row['topic'];
        $this->description = $row['description'];
        $this->upload_date = $row['upload_date'];
    }

    public function delete()
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function update()
    {
        $query = "UPDATE " . $this->table_name . " SET topic = :topic, description = :description WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $this->topic = htmlspecialchars(strip_tags($this->topic));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':topic', $this->topic);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }
}
?>
