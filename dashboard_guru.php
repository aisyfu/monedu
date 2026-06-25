<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'GURU') {
    header("Location: login.php");
    exit();
}

$idGuru = $_SESSION['idUser'];

$qMateri = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM materi WHERE idGuru = '$idGuru'");
$totalMateri = mysqli_fetch_assoc($qMateri)['total'] ?? 0;

$qSoal = mysqli_query($koneksi, "SELECT COUNT(s.idSoal) as total FROM soal s JOIN materi m ON s.idMateri = m.idMateri WHERE m.idGuru = '$idGuru'");
$totalSoal = mysqli_fetch_assoc($qSoal)['total'] ?? 0;

$qSiswa = mysqli_query($koneksi, "SELECT COUNT(DISTINCT idSiswa) as total FROM nilai n JOIN materi m ON n.idMateri = m.idMateri WHERE m.idGuru = '$idGuru'");
$totalSiswa = mysqli_fetch_assoc($qSiswa)['total'] ?? 0;

$queryNilaiTerbaru = mysqli_query($koneksi, "
    SELECT u.nama, m.judul, n.nilaiAkhir
    FROM nilai n
    JOIN user u ON n.idSiswa = u.idUser
    JOIN materi m ON n.idMateri = m.idMateri
    WHERE m.idGuru = '$idGuru'
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guru - MonEdu</title>
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
                <li class="active"><a href="dashboard_guru.php"><i class="fa-solid fa-gauge"></i> <span class="text-link">Dashboard</span></a></li>
                <li><a href="kelola-materi-guru.php"><i class="fa-solid fa-book-open"></i> <span class="text-link">Kelola Materi</span></a></li>
                <li><a href="kelola-soal.php"><i class="fa-solid fa-clipboard-list"></i> <span class="text-link">Kelola Soal</span></a></li>
                <li><a href="kelola-nilai.php"><i class="fa-solid fa-clipboard-check"></i> <span class="text-link">Kelola Nilai</span></a></li>
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
                <p>Ini adalah ringkasan aktivitas pembelajaran yang Anda kelola saat ini.</p>
            </div>

            <div class="card-container">
                <div class="card">
                    <div class="card-info">
                        <p>Total Materi</p>
                        <h3><?php echo $totalMateri; ?></h3>
                    </div>
                </div>

                <div class="card">
                    <div class="card-info">
                        <p>Total Soal</p>
                        <h3><?php echo $totalSoal; ?></h3>
                    </div>
                </div>

                <div class="card">
                    <div class="card-info">
                        <p>Siswa Berpartisipasi</p>
                        <h3><?php echo $totalSiswa; ?></h3>
                    </div>
                </div>
            </div>

            <div class="recent-section">
                <h2>Nilai Terbaru</h2>
                <div class="table-responsive">
                    <table class="recent-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Siswa</th>
                                <th>Materi</th>
                                <th>Nilai Akhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            if ($queryNilaiTerbaru && mysqli_num_rows($queryNilaiTerbaru) > 0) {
                                while ($row = mysqli_fetch_assoc($queryNilaiTerbaru)) {
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                <td><?php echo htmlspecialchars($row['judul']); ?></td>
                                <td><?php echo $row['nilaiAkhir']; ?></td>
                            </tr>
                            <?php
                                }
                            } else {
                                echo "<tr><td colspan='4' style='text-align:center;'>Belum ada data nilai.</td></tr>";
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
