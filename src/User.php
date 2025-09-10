<?php

class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $password;
    public $role;
    public $classe;
    public $corso;
    public $anno_scolastico;

    public function __construct($db) {
        $this->conn = $db;
    }

    function register() {
        // Check if username already exists
        $query = "SELECT id FROM " . $this->table_name . " WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $this->username);
        $stmt->execute();

        if($stmt->rowCount() > 0){
            return false; // Username already exists
        }

        $query = "INSERT INTO " . $this->table_name . "
                    SET
                        username = :username,
                        password = :password,
                        role = :role";

        if ($this->role === 'student') {
            $query .= ", classe = :classe, corso = :corso, anno_scolastico = :anno_scolastico";
        }

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->role = htmlspecialchars(strip_tags($this->role));

        // Hash the password
        $password_hash = password_hash($this->password, PASSWORD_BCRYPT);

        // Bind the values
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':password', $password_hash);
        $stmt->bindParam(':role', $this->role);

        if ($this->role === 'student') {
            $this->classe = htmlspecialchars(strip_tags($this->classe));
            $this->corso = htmlspecialchars(strip_tags($this->corso));
            $this->anno_scolastico = htmlspecialchars(strip_tags($this->anno_scolastico));
            $stmt->bindParam(':classe', $this->classe);
            $stmt->bindParam(':corso', $this->corso);
            $stmt->bindParam(':anno_scolastico', $this->anno_scolastico);
        }

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    function login() {
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

    /**
     * Find all users with the 'student' role.
     *
     * @return array An array of student data (id, username).
     */
    public static function findAllStudents()
    {
        $database = new Database();
        $pdo = $database->getConnection();

        $stmt = $pdo->prepare("SELECT id, username, classe, corso, anno_scolastico FROM users WHERE role = 'student' ORDER BY username ASC");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
