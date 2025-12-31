<?php
require_once '../config.php';

// Cek metode request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

// === HAPUS SEMUA RIWAYAT ABSENSI (RESET TOTAL) ===
if (isset($_POST['action']) && $_POST['action'] === 'hapus_semua_absensi') {
    // Gunakan TRUNCATE untuk hapus semua + reset auto increment (lebih cepat & bersih)
    $result = $koneksi->query("TRUNCATE TABLE absensi");

    if ($result) {
        $msg = "Berhasil menghapus SELURUH riwayat absensi. Rekap historis semua siswa telah direset ke 0.";
    } else {
        $msg = "Gagal menghapus data absensi: " . $koneksi->error;
    }

    header("Location: index.php?msg=" . urlencode($msg));
    exit;
}

// === SIMPAN / UPDATE ABSENSI HARIAN ===
$tanggal = $_POST['tanggal'] ?? '';
$statuses = $_POST['status'] ?? [];

if (empty($tanggal) || empty($statuses)) {
    header("Location: index.php?msg=" . urlencode("Data tanggal atau status absensi tidak lengkap."));
    exit;
}

// Proses setiap siswa
foreach ($statuses as $siswa_id => $status) {
    $siswa_id = (int)$siswa_id; // Pastikan ID integer
    $status = trim($status);

    // Validasi status
    if (!in_array($status, ['Hadir', 'Sakit', 'Izin', 'Alfa', 'Terlambat'])) {
        continue; // Lewati jika status tidak valid
    }

    // Cek apakah sudah ada record
    $check = $koneksi->prepare("SELECT id FROM absensi WHERE siswa_id = ? AND tanggal = ?");
    $check->bind_param("is", $siswa_id, $tanggal);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        // Update
        $stmt = $koneksi->prepare("UPDATE absensi SET status = ? WHERE siswa_id = ? AND tanggal = ?");
        $stmt->bind_param("sis", $status, $siswa_id, $tanggal);
    } else {
        // Insert
        $stmt = $koneksi->prepare("INSERT INTO absensi (siswa_id, tanggal, status) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $siswa_id, $tanggal, $status);
    }

    $stmt->execute();
    $stmt->close();
    $check->close();
}

header("Location: index.php?success=1");
exit;
?>