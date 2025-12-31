<?php
session_start();
if (!isset($_SESSION['user'])) {
    exit('Access denied');
}

require_once '../config.php';

$database = 'absensi_db3'; // Sesuaikan kalau nama DB kamu beda

// Header untuk download file .sql
header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="backup_' . $database . '_' . date('Y-m-d_H-i-s') . '.sql"');

// Fungsi backup tabel
function backupTable($koneksi, $table) {
    $output = "--\n-- Struktur dari tabel `$table`\n--\n\n";

    // Ambil struktur CREATE TABLE
    $result = $koneksi->query("SHOW CREATE TABLE `$table`");
    $row = $result->fetch_row();
    $output .= $row[1] . ";\n\n";

    // Ambil semua data
    $result = $koneksi->query("SELECT * FROM `$table`");
    if ($result->num_rows > 0) {
        $output .= "--\n-- Dumping data untuk tabel `$table`\n--\n\n";
        while ($row = $result->fetch_assoc()) {
            $values = array_map(function($value) use ($koneksi) {
                return $value === null ? 'NULL' : "'" . $koneksi->real_escape_string($value) . "'";
            }, array_values($row));
            $output .= "INSERT INTO `$table` VALUES (" . implode(', ', $values) . ");\n";
        }
    }
    $output .= "\n";
    return $output;
}

// Header dump seperti phpMyAdmin
echo "-- phpMyAdmin SQL Dump\n";
echo "-- version 5.2.1\n";
echo "-- https://www.phpmyadmin.net/\n";
echo "--\n";
echo "-- Host: localhost\n";
echo "-- Waktu pembuatan: " . date('d M Y') . " pada " . date('H.i') . "\n";
echo "-- Versi server: " . $koneksi->server_info . "\n";
echo "-- Versi PHP: " . phpversion() . "\n\n";

echo "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
echo "START TRANSACTION;\n";
echo "SET time_zone = \"+00:00\";\n\n";

// Backup semua tabel
$tables = ['kelas', 'siswa', 'absensi', 'users']; // Urutkan sesuai dependency

foreach ($tables as $table) {
    echo backupTable($koneksi, $table);
}

echo "COMMIT;\n";

// Tambahkan restore setting karakter seperti phpMyAdmin
echo "\n";
echo "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n";
echo "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n";
echo "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n";

exit;
?>