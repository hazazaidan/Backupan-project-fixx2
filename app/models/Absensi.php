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

    public function bulkCreate(array $rows): bool {
        if (empty($rows)) return false;
        $response = Database::request("POST", "kehadiran", $rows);
        return !isset($response['error']);
    }

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

    public function getByRange(string $tglMulai, string $tglAkhir, string $kelas = '', string $status = ''): array {
        $query = "kehadiran?tanggal=gte." . $tglMulai
               . "&tanggal=lte." . $tglAkhir
               . "&select=*,students(nama,nis,kelas)"
               . "&order=tanggal.desc,waktu_masuk.desc";

        if (!empty($status)) $query .= "&status=eq." . urlencode($status);

        $response = Database::request("GET", $query);
        if (empty($response) || isset($response['error'])) return [];

        $result = array_map(function($row) {
            return [
                'id'         => $row['id']                   ?? '',
                'tanggal'    => $row['tanggal']              ?? '-',
                'nama'       => $row['students']['nama']     ?? '-',
                'kelas'      => $row['students']['kelas']    ?? '-',
                'jam_datang' => !empty($row['waktu_masuk'])  ? date('H:i', strtotime($row['waktu_masuk']))  : '-',
                'jam_pulang' => !empty($row['waktu_pulang']) ? date('H:i', strtotime($row['waktu_pulang'])) : '-',
                'status'     => $row['status']               ?? '-',
                'ket'        => $row['keterangan']           ?? $row['status'] ?? '-',
            ];
        }, $response);

        if (!empty($kelas)) {
            $result = array_values(array_filter($result, fn($r) => $r['kelas'] === $kelas));
        }

        return $result;
    }

    // ── [BARU] Method-method berikut untuk filter data per kelas guru ─────────

    // Hitung total siswa dari array nama kelas yang diampu
    public function countSiswaByKelas(array $namaKelasAmpu): int {
        $total = 0;
        foreach ($namaKelasAmpu as $nk) {
            $res = Database::request("GET", "students?kelas=eq." . urlencode($nk) . "&select=id");
            if (!empty($res) && !isset($res['error'])) $total += count($res);
        }
        return $total;
    }

    // Hitung hadir hari ini hanya dari kelas yang diampu
    public function countHadirByKelas(string $tanggal, array $namaKelasAmpu): int {
        $res = Database::request("GET",
            "kehadiran?tanggal=eq." . $tanggal .
            "&status=in.(Hadir,Terlambat)" .
            "&select=siswa_id,students(kelas)"
        );
        if (empty($res) || isset($res['error'])) return 0;
        return count(array_filter($res, fn($r) => in_array($r['students']['kelas'] ?? '', $namaKelasAmpu)));
    }

    // Hitung izin/sakit hari ini hanya dari kelas yang diampu
    public function countIzinSakitByKelas(string $tanggal, array $namaKelasAmpu): int {
        $res = Database::request("GET",
            "kehadiran?tanggal=eq." . $tanggal .
            "&status=in.(Izin,Sakit)" .
            "&select=siswa_id,students(kelas)"
        );
        if (empty($res) || isset($res['error'])) return 0;
        return count(array_filter($res, fn($r) => in_array($r['students']['kelas'] ?? '', $namaKelasAmpu)));
    }

    // Ambil 5 aktivitas terkini hanya dari kelas yang diampu
    public function getRecentByKelas(array $namaKelasAmpu, int $limit = 5): array {
        $today = date('Y-m-d');
        $res   = Database::request("GET",
            "kehadiran?tanggal=eq." . $today .
            "&select=*,students(nama,nis,kelas)" .
            "&order=waktu_masuk.desc&limit=50"
        );
        if (empty($res) || isset($res['error'])) return [];

        $filtered = array_filter($res, fn($r) => in_array($r['students']['kelas'] ?? '', $namaKelasAmpu));
        return array_slice(array_values($filtered), 0, $limit);
    }

    // Ambil data chart 7 hari hanya dari kelas yang diampu
    public function getLast7DaysByKelas(array $namaKelasAmpu): array {
        $from = date('Y-m-d', strtotime('-6 days'));
        $res  = Database::request("GET",
            "kehadiran?tanggal=gte." . $from .
            "&select=tanggal,status,students(kelas)" .
            "&order=tanggal.asc"
        );
        if (empty($res) || isset($res['error'])) return [];

        // Filter hanya kelas yang diampu
        $res = array_filter($res, fn($r) => in_array($r['students']['kelas'] ?? '', $namaKelasAmpu));

        $grouped = [];
        foreach ($res as $row) {
            $tgl = $row['tanggal'];
            if (!isset($grouped[$tgl])) {
                $grouped[$tgl] = ['tanggal' => $tgl, 'hadir' => 0, 'izin' => 0, 'alpha' => 0];
            }
            $status = strtolower($row['status']);
            if ($status === 'hadir' || $status === 'terlambat') $grouped[$tgl]['hadir']++;
            elseif ($status === 'izin' || $status === 'sakit')  $grouped[$tgl]['izin']++;
            else                                                 $grouped[$tgl]['alpha']++;
        }
        return array_values($grouped);
    }
}