<?php

require_once __DIR__ . '/../core/Database.php';

class Guru {

    public function getAll() {
        return Database::request("GET", "guru");
    }

    public function getById($id) {
        return Database::request("GET", "guru?id=eq.$id");
    }

    public function insert($data) {
        return Database::request("POST", "guru", $data);
    }

    public function update($id, $data) {
        return Database::request("PATCH", "guru?id=eq.$id", $data);
    }

    public function delete($id) {
        return Database::request("DELETE", "guru?id=eq.$id");
    }

    public function findByQR($qr) {
        return Database::request("GET", "guru?qr_code=eq.$qr");
    }
}