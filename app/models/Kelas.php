<?php

require_once __DIR__ . '/../core/Database.php';

class Kelas {

    // ── CRUD Admin ────────────────────────────────────────────────────────

    public function insert(array $data): bool {
        $response = Database::request("POST", "kelas", $data);
        return !isset($response['error']);
    }

    public function updateById(string $id, array $data): bool {
        $response = Database::request("PATCH", "kelas?id=eq.$id", $data);
        return !isset($response['error']);
    }

    public function deleteById(string $id): bool {
        $response = Database::request("DELETE", "kelas?id=eq.$id");
        return !isset($response['error']);
    }

    // ── Method Existing ───────────────────────────────────────────────────

    public function getAll(): array {
        $response = Database::request("GET", "kelas?select=id,nama_kelas,tingkat,status&order=nama_kelas.asc");
        if (empty($response) || isset($response['error'])) return [];
        return $response;
    }

    public function getByGuru($guruId): array {
        // Ambil nama guru dari tabel guru
        $guruResp = Database::request("GET",
            "guru?id=eq." . urlencode($guruId) . "&select=nama&limit=1"
        );
        $namaGuru = $guruResp[0]['nama'] ?? '';

        if (!$namaGuru) return [];

        // Ambil kelas dari tabel kelas berdasarkan wali_kelas
        $kelasResp = Database::request("GET",
            "kelas?wali_kelas=eq." . urlencode($namaGuru) .
            "&select=id,nama_kelas,tingkat,jumlah_siswa,wali_kelas,status" .
            "&order=nama_kelas.asc"
        );
        if (empty($kelasResp) || isset($kelasResp['error'])) return [];

        $result = [];
        foreach ($kelasResp as $k) {
            $tingkat = $k['tingkat'] ?? explode(' ', $k['nama_kelas'])[0];
            $result[] = [
                'id'           => $k['id'],
                'jadwal_id'    => $k['id'],
                'nama'         => $k['nama_kelas'],
                'nama_kelas'   => $k['nama_kelas'],
                'jumlah_siswa' => $k['jumlah_siswa'] ?? 0,
                'wali_kelas'   => $k['wali_kelas']   ?? '-',
                'jurusan'      => $tingkat,
            ];
        }

        return $result;
    }

    public function getMapelByGuruKelas($guruId, string $kelas): array {
        // Coba cari di jadwal pakai nama kelas langsung
        $response = Database::request(
            "GET",
            "jadwal?guru_id=eq." . urlencode($guruId) .
            "&kelas=eq." . urlencode($kelas) .
            "&select=id,mata_pelajaran,hari,jam_mulai,jam_selesai" .
            "&order=hari.asc,jam_mulai.asc"
        );

        // Kalau tidak ada di jadwal, kembalikan array kosong (mapel belum diinput)
        if (empty($response) || isset($response['error'])) return [];

        $hariMap = [
            'Sunday'    => 'Minggu',
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
        ];
        $hariIni = $hariMap[date('l')] ?? date('l');

        return array_map(fn($j) => [
            'id'             => $j['id'],
            'mata_pelajaran' => $j['mata_pelajaran'] ?? '-',
            'nama'           => $j['mata_pelajaran'] ?? '-',
            'hari'           => $j['hari']           ?? '-',
            'jam_mulai'      => $j['jam_mulai']    ? substr($j['jam_mulai'],  0, 5) : '-',
            'jam_selesai'    => $j['jam_selesai']  ? substr($j['jam_selesai'], 0, 5) : '-',
            'ruangan'        => '-',
            'status'         => (strtolower($j['hari'] ?? '') === strtolower($hariIni))
                                 ? 'tersedia'
                                 : 'tidak_tersedia',
        ], $response);
    }

    public function getWithKehadiran(string $tanggal): array {
        $siswaResp = Database::request("GET", "students?select=id,kelas");
        if (empty($siswaResp) || isset($siswaResp['error'])) return [];

        $hadirResp = Database::request("GET", "kehadiran?tanggal=eq." . $tanggal . "&select=siswa_id,status");
        $hadirMap  = [];
        if (!empty($hadirResp) && !isset($hadirResp['error'])) {
            foreach ($hadirResp as $h) {
                $hadirMap[$h['siswa_id']] = $h['status'];
            }
        }

        $kelasData = [];
        foreach ($siswaResp as $siswa) {
            $kelas = $siswa['kelas'];
            if (!isset($kelasData[$kelas])) {
                $kelasData[$kelas] = ['nama_kelas' => $kelas, 'total_siswa' => 0, 'hadir' => 0, 'izin' => 0, 'alpha' => 0];
            }
            $kelasData[$kelas]['total_siswa']++;
            $status = strtolower($hadirMap[$siswa['id']] ?? 'alpha');
            if ($status === 'hadir')      $kelasData[$kelas]['hadir']++;
            elseif ($status === 'izin')   $kelasData[$kelas]['izin']++;
            else                          $kelasData[$kelas]['alpha']++;
        }

        foreach ($kelasData as &$k) {
            $k['persen'] = $k['total_siswa'] > 0
                ? round($k['hadir'] / $k['total_siswa'] * 100, 1)
                : 0;
        }

        ksort($kelasData);
        return array_values($kelasData);
    }

    public function getRekapBulanan(string $bulan): array {
        $dari   = $bulan . '-01';
        $sampai = date('Y-m-t', strtotime($dari));

        $siswaResp = Database::request("GET", "students?select=id,nama,nis,kelas");
        if (empty($siswaResp) || isset($siswaResp['error'])) return [];

        $hadirResp = Database::request("GET",
            "kehadiran?tanggal=gte." . $dari . "&tanggal=lte." . $sampai .
            "&select=siswa_id,tanggal,status&order=tanggal.asc"
        );
        $hadirResp = (!empty($hadirResp) && !isset($hadirResp['error'])) ? $hadirResp : [];

        $hadirPerSiswa = [];
        foreach ($hadirResp as $h) {
            $sid    = $h['siswa_id'];
            $status = strtolower($h['status'] ?? '');
            if (!isset($hadirPerSiswa[$sid])) {
                $hadirPerSiswa[$sid] = ['hadir' => 0, 'terlambat' => 0, 'izin' => 0, 'alpha' => 0];
            }
            if ($status === 'hadir')                          $hadirPerSiswa[$sid]['hadir']++;
            elseif ($status === 'terlambat')                  $hadirPerSiswa[$sid]['terlambat']++;
            elseif (in_array($status, ['izin', 'sakit']))     $hadirPerSiswa[$sid]['izin']++;
            else                                              $hadirPerSiswa[$sid]['alpha']++;
        }

        $hariEfektif = 0;
        $current = strtotime($dari);
        $end     = strtotime($sampai);
        while ($current <= $end) {
            if ((int)date('N', $current) <= 5) $hariEfektif++;
            $current = strtotime('+1 day', $current);
        }

        $kelasData = [];
        foreach ($siswaResp as $siswa) {
            $kls = $siswa['kelas'] ?? 'Tanpa Kelas';
            $sid = $siswa['id'];
            if (!isset($kelasData[$kls])) {
                $kelasData[$kls] = [
                    'nama_kelas'   => $kls,
                    'total_siswa'  => 0,
                    'hadir'        => 0,
                    'terlambat'    => 0,
                    'izin'         => 0,
                    'alpha'        => 0,
                    'hari_efektif' => $hariEfektif,
                    'siswa'        => [],
                ];
            }
            $kelasData[$kls]['total_siswa']++;
            $r = $hadirPerSiswa[$sid] ?? ['hadir' => 0, 'terlambat' => 0, 'izin' => 0, 'alpha' => 0];
            $kelasData[$kls]['hadir']     += $r['hadir'];
            $kelasData[$kls]['terlambat'] += $r['terlambat'];
            $kelasData[$kls]['izin']      += $r['izin'];
            $kelasData[$kls]['alpha']     += $r['alpha'];
            $kelasData[$kls]['siswa'][]    = [
                'nama'      => $siswa['nama'] ?? '-',
                'nis'       => $siswa['nis']  ?? '-',
                'hadir'     => $r['hadir'],
                'terlambat' => $r['terlambat'],
                'izin'      => $r['izin'],
                'alpha'     => $r['alpha'],
            ];
        }

        foreach ($kelasData as &$k) {
            $totalAbsensi = $k['total_siswa'] * $hariEfektif;
            $k['persen']  = $totalAbsensi > 0
                ? round(($k['hadir'] + $k['terlambat']) / $totalAbsensi * 100, 1)
                : 0;
        }

        ksort($kelasData);
        return array_values($kelasData);
    }
}