<?php
class RiwayatController extends Controller {

    public function index(): void {
        $this->requireRole('guru');

        $tanggal      = $_GET['tanggal'] ?? date('Y-m-d');
        $kelasFilter  = $_GET['kelas'] ?? '';
        $statusFilter = $_GET['status'] ?? '';
        $cariNama     = $_GET['nama'] ?? '';
        $page         = max(1, (int)($_GET['page'] ?? 1));
        $perPage      = 10;

        // Data dummy 10 siswa
        $allData = [
            ['no'=>1,'nama'=>'Ahmad Fauzi',    'nis'=>'2024001','kelas'=>'XI RPL 1','waktu'=>'07:15','tanggal'=>'2024-05-15','status'=>'Hadir'],
            ['no'=>2,'nama'=>'Bella Safitri',  'nis'=>'2024002','kelas'=>'XI RPL 1','waktu'=>'07:20','tanggal'=>'2024-05-15','status'=>'Hadir'],
            ['no'=>3,'nama'=>'Cahya Ramadan',  'nis'=>'2024003','kelas'=>'XI RPL 1','waktu'=>'07:45','tanggal'=>'2024-05-15','status'=>'Izin'],
            ['no'=>4,'nama'=>'Dewi Lestari',   'nis'=>'2024004','kelas'=>'XI RPL 1','waktu'=>'07:10','tanggal'=>'2024-05-15','status'=>'Hadir'],
            ['no'=>5,'nama'=>'Eko Prasetyo',   'nis'=>'2024005','kelas'=>'XI RPL 1','waktu'=>'-',    'tanggal'=>'2024-05-15','status'=>'Alpha'],
            ['no'=>6,'nama'=>'Fitri Handayani','nis'=>'2024006','kelas'=>'XI RPL 1','waktu'=>'07:30','tanggal'=>'2024-05-15','status'=>'Hadir'],
            ['no'=>7,'nama'=>'Gilang Saputra', 'nis'=>'2024007','kelas'=>'XI RPL 1','waktu'=>'07:55','tanggal'=>'2024-05-15','status'=>'Izin'],
            ['no'=>8,'nama'=>'Hana Permata',   'nis'=>'2024008','kelas'=>'XI RPL 1','waktu'=>'07:08','tanggal'=>'2024-05-15','status'=>'Hadir'],
            ['no'=>9,'nama'=>'Irfan Maulana',  'nis'=>'2024009','kelas'=>'XI RPL 1','waktu'=>'-',    'tanggal'=>'2024-05-15','status'=>'Alpha'],
            ['no'=>10,'nama'=>'Jasmine Aulia', 'nis'=>'2024010','kelas'=>'XI RPL 1','waktu'=>'07:22','tanggal'=>'2024-05-15','status'=>'Hadir'],
        ];

        $total      = count($allData);
        $totalPages = ceil($total / $perPage);
        $absensiList = array_slice($allData, ($page - 1) * $perPage, $perPage);

        $this->view('guru/riwayat', [
            'title'        => 'Riwayat Absensi',
            'absensiList'  => $absensiList,
            'tanggal'      => $tanggal,
            'kelasFilter'  => $kelasFilter,
            'statusFilter' => $statusFilter,
            'cariNama'     => $cariNama,
            'page'         => $page,
            'totalPages'   => $totalPages,
            'total'        => $total,
        ]);
    }
}