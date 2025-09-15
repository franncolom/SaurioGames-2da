<?php
class Usuario {
    private $conn;
    private $table = "Usuario";

    public $id_usuario;
    public $correo;
    public $nombre_usuario;
    public $contraseña;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT id_usuario, correo, nombre_usuario FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getById($id) {
        $query = "SELECT id_usuario, correo, nombre_usuario FROM " . $this->table . " WHERE id_usuario = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " (correo, nombre_usuario, `contraseña`) VALUES (:correo, :nombre_usuario, :contrasena)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":correo", $this->correo);
        $stmt->bindParam(":nombre_usuario", $this->nombre_usuario);
        $stmt->bindParam(":contrasena", $this->contraseña);
        return $stmt->execute();
    }

    public function update() {
        $query = "UPDATE " . $this->table . " SET correo = :correo, nombre_usuario = :nombre_usuario, `contraseña` = :contrasena WHERE id_usuario = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":correo", $this->correo);
        $stmt->bindParam(":nombre_usuario", $this->nombre_usuario);
        $stmt->bindParam(":contrasena", $this->contraseña);
        $stmt->bindParam(":id", $this->id_usuario);
        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id_usuario = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id_usuario);
        return $stmt->execute();
    }

    public function getByNombreUsuario($nombreUsuario) {
        $query = "SELECT id_usuario, correo, nombre_usuario, `contraseña` FROM " . $this->table . " WHERE nombre_usuario = :nombre LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nombre", $nombreUsuario, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByCorreo($correo) {
        $query = "SELECT id_usuario, correo, nombre_usuario, `contraseña` FROM " . $this->table . " WHERE correo = :correo LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":correo", $correo, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePasswordById($id, $hashedPassword) {
        $query = "UPDATE " . $this->table . " SET `contraseña` = :contrasena WHERE id_usuario = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":contrasena", $hashedPassword, PDO::PARAM_STR);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
