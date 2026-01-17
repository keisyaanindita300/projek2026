<?php
require_once 'database.php';

$conn = koneksiDatabase();

$id = $_GET['id'] ?? 0;
$error = '';
$success = '';

// Ambil data siswa berdasarkan ID
$sql = "SELECT * FROM siswa WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$siswa = $result->fetch_assoc();

if (!$siswa) {
    header('Location: index.php?error=Data siswa tidak ditemukan');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'] ?? '';
    $npm = $_POST['npm'] ?? '';
    $mata_kuliah = $_POST['mata_kuliah'] ?? '';
    $nilai_angka = $_POST['nilai_angka'] ?? '';
    
    // Validasi
    if (empty($nama) || empty($npm) || empty($mata_kuliah) || empty($nilai_angka)) {
        $error = 'Semua field harus diisi!';
    } elseif (!is_numeric($nilai_angka) || $nilai_angka < 0 || $nilai_angka > 100) {
        $error = 'Nilai harus berupa angka antara 0-100!';
    } else {
        // Cek apakah NPM sudah ada (kecuali untuk data yang sedang diedit)
        $checkSql = "SELECT id FROM siswa WHERE npm = ? AND id != ?";
        $stmt = $conn->prepare($checkSql);
        $stmt->bind_param("si", $npm, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'NPM sudah terdaftar!';
        } else {
            // Konversi nilai angka ke huruf
            $nilai_huruf = getNilaiHuruf($nilai_angka);
            
            // Update data
            $sql = "UPDATE siswa SET nama = ?, npm = ?, mata_kuliah = ?, 
                    nilai_angka = ?, nilai_huruf = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssisi", $nama, $npm, $mata_kuliah, $nilai_angka, $nilai_huruf, $id);
            
            if ($stmt->execute()) {
                $success = 'Data siswa berhasil diperbarui!';
                // Update data yang ditampilkan
                $siswa = array_merge($siswa, [
                    'nama' => $nama,
                    'npm' => $npm,
                    'mata_kuliah' => $mata_kuliah,
                    'nilai_angka' => $nilai_angka,
                    'nilai_huruf' => $nilai_huruf
                ]);
            } else {
                $error = 'Terjadi kesalahan: ' . $stmt->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Nilai Siswa</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-edit"></i> Edit Data Nilai Siswa</h1>
            <p class="subtitle">Perbarui data nilai siswa di bawah ini</p>
        </header>

        <div class="card">
            <div class="card-header">
                <h2>Form Edit Data</h2>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="nama"><i class="fas fa-user"></i> Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" 
                           value="<?php echo htmlspecialchars($siswa['nama']); ?>" 
                           placeholder="Masukkan nama lengkap" required>
                </div>

                <div class="form-group">
                    <label for="npm"><i class="fas fa-id-card"></i> NPM</label>
                    <input type="text" id="npm" name="npm" 
                           value="<?php echo htmlspecialchars($siswa['npm']); ?>" 
                           placeholder="Masukkan NPM" required>
                </div>

                <div class="form-group">
                    <label for="mata_kuliah"><i class="fas fa-book"></i> Mata Kuliah</label>
                    <input type="text" id="mata_kuliah" name="mata_kuliah" 
                           value="<?php echo htmlspecialchars($siswa['mata_kuliah']); ?>" 
                           placeholder="Masukkan nama mata kuliah" required>
                </div>

                <div class="form-group">
                    <label for="nilai_angka"><i class="fas fa-chart-bar"></i> Nilai Angka (0-100)</label>
                    <input type="number" id="nilai_angka" name="nilai_angka" 
                           value="<?php echo htmlspecialchars($siswa['nilai_angka']); ?>" 
                           min="0" max="100" placeholder="Masukkan nilai angka" required>
                    <div class="form-help">
                        <small>Nilai akan otomatis dikonversi ke huruf</small>
                    </div>
                </div>

                <div class="form-group">
                    <label>Nilai Huruf Saat Ini</label>
                    <div class="preview-nilai">
                        <span class="nilai-huruf nilai-<?php echo strtolower($siswa['nilai_huruf']); ?>">
                            <?php echo $siswa['nilai_huruf']; ?>
                        </span>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Data
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> - Aplikasi CRUD Nilai Siswa</p>
        </footer>
    </div>

    <script>
        // Preview nilai huruf saat mengubah nilai angka
        document.getElementById('nilai_angka').addEventListener('input', function() {
            const nilai = this.value;
            if (nilai >= 0 && nilai <= 100) {
                // Kirim request untuk preview
                const form = this.closest('form');
                const formData = new FormData(form);
                formData.append('preview', 'true');
                
                fetch('', {
                    method: 'POST',
                    body: formData
                }).then(response => response.text())
                  .then(html => {
                      const tempDiv = document.createElement('div');
                      tempDiv.innerHTML = html;
                      const preview = tempDiv.querySelector('.preview-nilai');
                      if (preview) {
                          document.querySelector('.preview-nilai').innerHTML = preview.innerHTML;
                      }
                  });
            }
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>