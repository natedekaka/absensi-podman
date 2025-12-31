<?php
require_once '../config.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="backup_absensi_semua_' . date('Y-m-d_H-i-s') . '.csv"');

$output = fopen('php://output', 'w');

// Header CSV
fputcsv($output, ['ID', 'NIS', 'Nama Siswa', 'Kelas', 'Tanggal', 'Status']);

// Ambil semua data absensi + join siswa & kelas
$query = "
    SELECT a.id, s.nis, s.nama, k.nama_kelas, a.tanggal, a.status
    FROM absensi a
    JOIN siswa s ON a.siswa_id = s.id
    JOIN kelas k ON s.kelas_id = k.id
    ORDER BY a.tanggal DESC, k.nama_kelas, s.nama
";

$result = $koneksi->query($query);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}

fclose($output);
exit;
?>