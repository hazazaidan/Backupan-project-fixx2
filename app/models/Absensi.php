<?php
class Absensi {

    public function countByStatusAndDate(string $status, string $tanggal): int {
        $response = Database::request("GET", "kehadiran?status=eq." . urlencode($status) . "&tanggal=eq." . $tanggal . "&select=id");
        if (empty($response) || isset($response['error'])) return 0;
        return count($response);
    }

    public function sudahAbsenHariIni(string $siswaId, string $tanggal): bool {
        $response = Database::request("GET", "kehadiran?siswa_id=eq." . $siswaId . "&tanggal=eq." . $tanggal . "&limit=1");
        return !empty($response) && !isset($response['error']);
    }

    public function create(array $data): bool {
        $response = Database::request("POST", "kehadiran", $data);
        return !isset($response['error']);
    }

    // ── [BARU] Bulk insert absensi satu sesi (array of rows) ─────────
    // Setiap row: { siswa_id, tanggal, status, jadwal_id, waktu_masuk }
    public function bulkCreate(array $rows): bool {
        if (empty($rows)) return false;
        $response = Database::request("POST", "kehadiran", $rows);
        return !isset($response['error']);
    }

    // ── [BARU] Cek apakah sudah ada absensi di jadwal & tanggal tsb ──
    public function sudahAbsenJadwal(string $jadwalId, string $tanggal): bool {
        $response = Database::request(
            "GET",
            "kehadiran?jadwal_id=eq." . $jadwalId .
            "&tanggal=eq." . $tanggal .
            "&limit=1"
        );
        return !empty($response) && !isset($response['error']);
    }

    public function getRecent(int $limit = 5): array {
        $today    = date('Y-m-d');
        $response = Database::request("GET", "kehadiran?tanggal=eq." . $today . "&select=*,students(nama,nis,kelas)&order=waktu_masuk.desc&limit=" . $limit);
        if (empty($response) || isset($response['error'])) return [];
        return $response;
    }

    public function getLast7Days(): array {
        $from     = date('Y-m-d', strtotime('-6 days'));
        $response = Database::request("GET", "kehadiran?tanggal=gte." . $from . "&select=tanggal,status&order=tanggal.asc");
        if (empty($response) || isset($response['error'])) return [];

        $grouped = [];
        foreach ($response as $row) {
            $tgl = $row['tanggal'];
            if (!isset($grouped[$tgl])) {
                $grouped[$tgl] = ['tanggal' => $tgl, 'hadir' => 0, 'izin' => 0, 'alpha' => 0];
            }
            $status = strtolower($row['status']);
            if ($status === 'hadir')      $grouped[$tgl]['hadir']++;
            elseif ($status === 'izin')   $grouped[$tgl]['izin']++;
            else                          $grouped[$tgl]['alpha']++;
        }
        return array_values($grouped);
    }

    public function getByDate(string $tanggal, int $limit = 100): array {
        $response = Database::request("GET", "kehadiran?tanggal=eq." . $tanggal . "&select=*,students(nama,nis,kelas)&order=waktu_masuk.desc&limit=" . $limit);
        if (empty($response) || isset($response['error'])) return [];

        return array_map(function($row) {
            $row['nama']  = $row['students']['nama']  ?? '–';
            $row['kelas'] = $row['students']['kelas'] ?? '–';
            $row['waktu'] = $row['waktu_masuk']       ?? '–';
            return $row;
        }, $response);
    }

    public function countIzinSakitToday(): int {
        $today    = date('Y-m-d');
        $response = Database::request("GET", "kehadiran?tanggal=eq." . $today . "&status=in.(Izin,Sakit)&select=id");
        if (empty($response) || isset($response['error'])) return 0;
        return count($response);
    }

    public function getFiltered(string $tanggal, string $kelas, string $status, int $page, int $perPage): array {
        $offset = ($page - 1) * $perPage;
        $query  = "kehadiran?tanggal=eq." . $tanggal . "&select=*,students(nama,nis,kelas)&order=waktu_masuk.desc&limit=" . $perPage . "&offset=" . $offset;
        if (!empty($status)) $query .= "&status=eq." . urlencode($status);
        $response = Database::request("GET", $query);
        if (empty($response) || isset($response['error'])) return [];
        return $response;
    }

    public function countFiltered(string $tanggal, string $kelas, string $status): int {
        $query = "kehadiran?tanggal=eq." . $tanggal . "&select=id";
        if (!empty($status)) $query .= "&status=eq." . urlencode($status);
        $response = Database::request("GET", $query);
        if (empty($response) || isset($response['error'])) return 0;
        return count($response);
    }

    public function getSummaryBulanan(string $bulan): array {
        $dari   = $bulan . '-01';
        $sampai = date('Y-m-t', strtotime($dari));
        $response = Database::request("GET",
            "kehadiran?tanggal=gte." . $dari . "&tanggal=lte." . $sampai .
            "&select=tanggal,status,siswa_id,students(nama,nis,kelas)&order=tanggal.asc"
        );
        if (empty($response) || isset($response['error'])) return [];

        $summary = ['hadir' => 0, 'terlambat' => 0, 'izin' => 0, 'alpha' => 0, 'total' => 0];
        foreach ($response as $row) {
            $s = strtolower($row['status'] ?? '');
            $summary['total']++;
            if ($s === 'hadir')                      $summary['hadir']++;
            elseif ($s === 'terlambat')               $summary['terlambat']++;
            elseif ($s === 'izin' || $s === 'sakit')  $summary['izin']++;
            else                                      $summary['alpha']++;
        }
        return $summary;
    }
}