<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'SISWA') {
    header("Location: login.php?error=akses_ditolak");
    exit();
}

$idSiswa = $_SESSION['idUser'];

// Total materi tervalidasi
$qMateri = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM materi WHERE statusValidasi = 'Tervalidasi'");
$totalMateri = mysqli_fetch_assoc($qMateri)['total'];

// Total nilai yang sudah dikerjakan siswa ini
$qNilai = mysqli_prepare($koneksi, "SELECT COUNT(*) as total FROM nilai WHERE idSiswa = ?");
mysqli_stmt_bind_param($qNilai, "i", $idSiswa);
mysqli_stmt_execute($qNilai);
$totalNilai = mysqli_fetch_assoc(mysqli_stmt_get_result($qNilai))['total'];

// Rata-rata nilai siswa ini
$qAvg = mysqli_prepare($koneksi, "SELECT ROUND(AVG(nilaiAkhir), 1) as rata FROM nilai WHERE idSiswa = ?");
mysqli_stmt_bind_param($qAvg, "i", $idSiswa);
mysqli_stmt_execute($qAvg);
$rataRata = mysqli_fetch_assoc(mysqli_stmt_get_result($qAvg))['rata'] ?? 0;

// Nilai terbaru
$qTerbaru = mysqli_prepare($koneksi, "
    SELECT n.nilaiAkhir, m.judul, m.kategori, u.nama as namaGuru, m.createdAt
    FROM nilai n
    JOIN materi m ON n.idMateri = m.idMateri
    JOIN guru g ON m.idGuru = g.idUser
    JOIN user u ON g.idUser = u.idUser
    WHERE n.idSiswa = ?
    ORDER BY m.createdAt DESC
    LIMIT 5
");
mysqli_stmt_bind_param($qTerbaru, "i", $idSiswa);
mysqli_stmt_execute($qTerbaru);
$hasilTerbaru = mysqli_stmt_get_result($qTerbaru);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa - MonEdu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="./script.js"></script>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div>
            <div class="sidebar-brand">
                <span class="text-link">MonEdu</span>
                <i class="fa-solid fa-angle-left" id="toggleSidebar" onclick="toggleSidebar()"></i>
            </div>
            <ul class="sidebar-menu">
                <li class="active"><a href="dashboard_siswa.php"><i class="fa-solid fa-gauge"></i> <span class="text-link">Dashboard</span></a></li>
                <li><a href="materi_siswa.php"><i class="fa-solid fa-book-open"></i> <span class="text-link">Materi</span></a></li>
                <li><a href="#"><i class="fa-solid fa-pencil"></i> <span class="text-link">Soal</span></a></li>
                <li><a href="nilai_siswa.php"><i class="fa-solid fa-chart-simple"></i> <span class="text-link">Lihat Nilai</span></a></li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> <span class="text-link">Keluar</span></a>
        </div>
    </div>

    <div class="main-content">
        <div class="topbar">
            <div class="profile-info">
                <p><?php echo strtoupper($_SESSION['role']); ?> <br><span><?php echo $_SESSION['nama']; ?></span></p>
                <i class="fa-solid fa-circle-user"></i>
            </div>
        </div>

        <div class="page-content">
            <div class="header">
                <h1>Selamat Datang, <?php echo $_SESSION['nama']; ?>!</h1>
                <p>Ini adalah ringkasan aktivitas belajar kamu di MonEdu.</p>
            </div>

            <div class="card-container">
                <div class="card">
                    <div class="card-info">
                        <p>Materi Tersedia</p>
                        <h3><?php echo $totalMateri; ?></h3>
                    </div>
                    <i class="fa-solid fa-book-open" style="font-size:2rem;color:#3498db;opacity:0.3;"></i>
                </div>
                <div class="card">
                    <div class="card-info">
                        <p>Soal Dikerjakan</p>
                        <h3><?php echo $totalNilai; ?></h3>
                    </div>
                    <i class="fa-solid fa-pencil" style="font-size:2rem;color:#2ecc71;opacity:0.3;"></i>
                </div>
                <div class="card">
                    <div class="card-info">
                        <p>Rata-rata Nilai</p>
                        <h3><?php echo $rataRata > 0 ? $rataRata : '-'; ?></h3>
                    </div>
                    <i class="fa-solid fa-star" style="font-size:2rem;color:#f1c40f;opacity:0.3;"></i>
                </div>
            </div>

            <div class="recent-section">
                <h2>Nilai Terbaru</h2>
                <div class="table-responsive">
                    <table class="recent-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Judul Materi</th>
                                <th>Kategori</th>
                                <th>Guru</th>
                                <th>Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            if (mysqli_num_rows($hasilTerbaru) > 0) {
                                while ($row = mysqli_fetch_assoc($hasilTerbaru)) {
                                    $nilaiVal = $row['nilaiAkhir'];
                                    $warna = $nilaiVal >= 80 ? '#27ae60' : ($nilaiVal >= 70 ? '#f39c12' : '#e74c3c');
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($row['judul']); ?></td>
                                <td><?php echo htmlspecialchars($row['kategori']); ?></td>
                                <td><?php echo htmlspecialchars($row['namaGuru']); ?></td>
                                <td><strong style="color:<?php echo $warna; ?>"><?php echo $nilaiVal; ?> / 100</strong></td>
                            </tr>
                            <?php
                                }
                            } else {
                                echo "<tr><td colspan='5' style='text-align:center;color:#aaa;'>Belum ada nilai tercatat.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
