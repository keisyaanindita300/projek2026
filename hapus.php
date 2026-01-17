<?php
require_once 'database.php';

$conn = koneksiDatabase();

$id = $_GET['id'] ?? 0;

if ($id) {
    // Hapus data siswa berdasarkan ID
    $sql = "DELETE FROM siswa WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header('Location: index.php?message=Data siswa berhasil dihapus');
    } else {
        header('Location: index.php?error=Gagal menghapus data siswa');
    }
} else {
    header('Location: index.php?error=ID siswa tidak valid');
}

$conn->close();
exit();
?>