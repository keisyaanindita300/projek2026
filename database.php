<?php
// Konfigurasi database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'nilai_murid');

// Fungsi untuk koneksi database
function koneksiDatabase() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Cek koneksi
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }
    
    return $conn;
}

// Fungsi untuk mendapatkan nilai huruf berdasarkan nilai angka
function getNilaiHuruf($nilai) {
    if ($nilai >= 85) return 'A';
    if ($nilai >= 80) return 'A-';
    if ($nilai >= 75) return 'B+';
    if ($nilai >= 70) return 'B';
    if ($nilai >= 65) return 'B-';
    if ($nilai >= 60) return 'C+';
    if ($nilai >= 55) return 'C';
    if ($nilai >= 50) return 'C-';
    if ($nilai >= 40) return 'D';
    return 'E';
}
?>