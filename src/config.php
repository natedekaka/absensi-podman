<?php
//session_start();

$host = 'db';
$user = 'absensi_user';
$pass = 'userpass123';
$db   = 'absensi_db';

$koneksi = new mysqli($host, $user, $pass, $db);

if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}
// JANGAN ADA SPASI ATAU NEWLINE SETELAH INI
