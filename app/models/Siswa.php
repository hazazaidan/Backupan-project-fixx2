<?php
class Siswa {

    public function countAll(): int {
        $response = Database::request("GET", "students?select=id");
        if (empty($response) || isset($response['error'])) return 0;
        return count($response);
    }

    public function findByQR(string $qrCode): array|false {
        $response = Database::request(
            "GET",
            "students?select=id,nis,nama,kelas,qr_image&qr_image=eq." . urlencode($qrCode) . "&limit=1"
        );
        if (empty($response) || isset($response['error'])) return false;
        return $response[0];
    }

    public function findById(string $id): array|false {
        $response = Database::request(
            "GET",
            "students?select=id,nis,nama,kelas,qr_image&id=eq." . urlencode($id) . "&limit=1"
        );
        if (empty($response) || isset($response['error'])) return false;
        return $response[0];
    }

    public function getByKelas(string $kelas): array {
        $response = Database::request(
            "GET",
            "students?select=id,nis,nama,kelas,qr_image&kelas=eq." . urlencode($kelas) . "&order=nama.asc"
        );
        if (empty($response) || isset($response['error'])) return [];
        return $response;
    }

    public function getAll(): array {
        $response = Database::request(
            "GET",
            "students?select=id,nis,nama,kelas,qr_image&order=nama.asc"
        );
        if (empty($response) || isset($response['error'])) return [];
        return $response;
    }

    public function create(array $data): bool {
        $response = Database::request("POST", "students", $data);
        return !isset($response['error']);
    }

    public function update(string $id, array $data): bool {
        $response = Database::request(
            "PATCH",
            "students?id=eq." . urlencode($id),
            $data
        );
        return !isset($response['error']);
    }

    public function delete(string $id): bool {
        $response = Database::request(
            "DELETE",
            "students?id=eq." . urlencode($id)
        );
        return !isset($response['error']);
    }
}