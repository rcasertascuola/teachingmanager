<?php

class User
{
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $password;
    public $role;
    public $classe;
    public $corso;
    public $anno_scolastico;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    function register()
    {
        $query = "INSERT INTO " . $this->table_name . "
            SET
                username = :username,
                password = :password,
                role = :role,
                classe = :classe,
                corso = :corso,
                anno_scolastico = :anno_scolastico";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->classe = htmlspecialchars(strip_tags($this->classe));
        $this->corso = htmlspecialchars(strip_tags($this->corso));
        $this->anno_scolastico = htmlspecialchars(strip_tags($this->anno_scolastico));

        // Hash the password
        $password_hash = password_hash($this->password, PASSWORD_BCRYPT);

        // Bind the values
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':password', $password_hash);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':classe', $this->classe);
        $stmt->bindParam(':corso', $this->corso);
        $stmt->bindParam(':anno_scolastico', $this->anno_scolastico);

        try {
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            // Check if it's a duplicate entry error
            if ($e->getCode() == 23000) {
                return false; // Username already exists
            }
            // You might want to log other errors
            // error_log($e->getMessage());
            return false;
        }

        return false;
    }

    function login()
    {
        $query = "SELECT id, username, password, role, classe, corso, anno_scolastico FROM " . $this->table_name . " WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $this->username);
        $stmt->execute();

        $num = $stmt->rowCount();

        if ($num > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->role = $row['role'];
            $this->classe = $row['classe'];
            $this->corso = $row['corso'];
            $this->anno_scolastico = $row['anno_scolastico'];
            $password_from_db = $row['password'];

            if (password_verify($this->password, $password_from_db)) {
                return true;
            }
        }

        return false;
    }

    public function findAll()
    {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function findById($id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update()
    {
        $query = "UPDATE " . $this->table_name . " SET username = :username, role = :role, classe = :classe, corso = :corso, anno_scolastico = :anno_scolastico";

        if (!empty($this->password)) {
            $query .= ", password = :password";
        }

        $query .= " WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->classe = htmlspecialchars(strip_tags($this->classe));
        $this->corso = htmlspecialchars(strip_tags($this->corso));
        $this->anno_scolastico = htmlspecialchars(strip_tags($this->anno_scolastico));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind the values
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':classe', $this->classe);
        $stmt->bindParam(':corso', $this->corso);
        $stmt->bindParam(':anno_scolastico', $this->anno_scolastico);
        $stmt->bindParam(':id', $this->id);

        if (!empty($this->password)) {
            $password_hash = password_hash($this->password, PASSWORD_BCRYPT);
            $stmt->bindParam(':password', $password_hash);
        }

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    function delete($id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $id = htmlspecialchars(strip_tags($id));

        // Bind the value
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function count()
    {
        $query = "SELECT COUNT(*) as total_rows FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_rows'];
    }

    /**
     * Find all users with the 'student' role.
     *
     * @return array An array of student data (id, username).
     */
    public function findAllStudents()
    {
        $stmt = $this->conn->prepare("SELECT id, username, classe, corso, anno_scolastico FROM users WHERE role = 'student' ORDER BY username ASC");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
