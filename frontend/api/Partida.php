<?php
class Partida {
    private $conn;
    private $table = "Partida";

    public $id_partida;
    public $puntaje_partida;
    public $can_jugadores;
    public $estado;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id_partida = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " (puntaje_partida, can_jugadores, estado) VALUES (:puntaje_partida, :can_jugadores, :estado)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":puntaje_partida", $this->puntaje_partida);
        $stmt->bindParam(":can_jugadores", $this->can_jugadores);
        $stmt->bindParam(":estado", $this->estado);
        return $stmt->execute();
    }

    public function update() {
        $query = "UPDATE " . $this->table . " SET puntaje_partida = :puntaje_partida, can_jugadores = :can_jugadores, estado = :estado WHERE id_partida = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":puntaje_partida", $this->puntaje_partida);
        $stmt->bindParam(":can_jugadores", $this->can_jugadores);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":id", $this->id_partida);
        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id_partida = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id_partida);
        return $stmt->execute();
    }
}
