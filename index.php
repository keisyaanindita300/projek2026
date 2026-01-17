<?php
// File: index.php
// Cek apakah file config/database.php ada
$config_file = __DIR__ . '/config/database.php';

if (file_exists($config_file)) {
    require_once $config_file;
} else {
    // Jika file config tidak ditemukan, buat koneksi database langsung
    $host = 'localhost';
    $user = 'root';
    $pass = ''; // Password default Laragon biasanya kosong
    $dbname = 'nilai_siswa';
    
    // Buat koneksi
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    // Cek koneksi
    if ($conn->connect_error) {
        die("Koneksi database gagal: " . $conn->connect_error);
    }
    
    // Fungsi untuk mendapatkan nilai huruf
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
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Nilai Siswa</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-graduation-cap"></i> Manajemen Nilai Siswa</h1>
            <p class="subtitle">Aplikasi CRUD untuk mengelola nilai mahasiswa</p>
        </header>

        <div class="card">
            <div class="card-header">
                <h2>Daftar Nilai Siswa</h2>
                <a href="create.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Data Nilai
                </a>
            </div>

            <?php
            // Ambil data siswa dari database
            if (isset($conn)) {
                $sql = "SELECT * FROM siswa ORDER BY nama ASC";
                $result = $conn->query($sql);
            } else {
                $result = null;
                echo '<div class="alert alert-danger">Koneksi database tidak tersedia.</div>';
            }
            ?>

            <?php if (isset($_GET['message'])): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($_GET['message']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>NPM</th>
                            <th>Mata Kuliah</th>
                            <th>Nilai Angka</th>
                            <th>Nilai Huruf</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php $no = 1; ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                    <td><?php echo htmlspecialchars($row['npm']); ?></td>
                                    <td><?php echo htmlspecialchars($row['mata_kuliah']); ?></td>
                                    <td>
                                        <span class="nilai-angka"><?php echo $row['nilai_angka']; ?></span>
                                    </td>
                                    <td>
                                        <span class="nilai-huruf nilai-<?php echo strtolower($row['nilai_huruf']); ?>">
                                            <?php echo $row['nilai_huruf']; ?>
                                        </span>
                                    </td>
                                    <td class="actions">
                                        <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="delete.php?id=<?php echo $row['id']; ?>" 
                                           class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">
                                    <?php 
                                    if ($result && $result->num_rows === 0) {
                                        echo 'Tidak ada data siswa. <a href="create.php">Tambah data pertama</a>';
                                    } else {
                                        echo 'Tidak dapat mengakses database.';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($result && $result->num_rows > 0): ?>
            <div class="stats">
                <div class="stat-item">
                    <span class="stat-label">Total Siswa</span>
                    <span class="stat-value"><?php echo $result->num_rows; ?></span>
                </div>
                <div class="stat-item">
                    <?php 
                    // Hitung rata-rata nilai
                    $sqlAvg = "SELECT AVG(nilai_angka) as rata_rata FROM siswa";
                    $resultAvg = $conn->query($sqlAvg);
                    $avg = $resultAvg->fetch_assoc();
                    ?>
                    <span class="stat-label">Rata-rata Nilai</span>
                    <span class="stat-value">
                        <?php echo number_format($avg['rata_rata'] ?? 0, 2); ?>
                    </span>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> - Aplikasi CRUD Nilai Siswa</p>
            <p>Status: 
                <?php 
                if (isset($conn) && $conn->connect_error) {
                    echo '<span style="color: red;">Database Error</span>';
                } elseif (isset($conn)) {
                    echo '<span style="color: green;">Database Connected</span>';
                } else {
                    echo '<span style="color: orange;">Using Direct Connection</span>';
                }
                ?>
            </p>
        </footer>
    </div>

    <script>
        // Highlight nilai berdasarkan grade
        document.addEventListener('DOMContentLoaded', function() {
            const nilaiCells = document.querySelectorAll('.nilai-angka');
            nilaiCells.forEach(cell => {
                const nilai = parseInt(cell.textContent);
                if (nilai >= 85) {
                    cell.classList.add('nilai-tinggi');
                } else if (nilai >= 70) {
                    cell.classList.add('nilai-sedang');
                } else {
                    cell.classList.add('nilai-rendah');
                }
            });
        });
    </script>
</body>
</html>

<?php 
// Tutup koneksi database jika ada
if (isset($conn)) {
    $conn->close();
}
?>