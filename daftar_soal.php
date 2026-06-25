<?php
session_start();
include 'koneksi.php';

// Pastikan yang login adalah SISWA
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'SISWA') {
    header("Location: login.php"); exit();
}
$idSiswa = $_SESSION['idUser'];

// Query mengambil materi yang sudah memiliki soal di database
$query = "SELECT m.*, mp.namaMapel, COUNT(s.idSoal) as total_soal 
          FROM materi m
          LEFT JOIN mata_pelajaran mp ON m.idMapel = mp.idMapel
          JOIN soal s ON m.idMateri = s.idMateri
          GROUP BY m.idMateri
          ORDER BY m.idMateri DESC";
$result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Kuis - MonEdu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    
    <div class="sidebar" id="sidebar">
        <div>
            <div class="sidebar-brand">
                <span class="text-link">MonEdu</span>
                <i class="fa-solid fa-angle-left" id="toggleSidebar" onclick="toggleSidebar()"></i>
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard_siswa.php"><i class="fa-solid fa-gauge"></i> <span class="text-link">Dashboard</span></a></li>
                <li><a href="materi_siswa.php"><i class="fa-solid fa-book-open"></i> <span class="text-link">Materi</span></a></li>
                <li class="active"><a href="daftar_soal.php"><i class="fa-solid fa-clipboard-list"></i> <span class="text-link">Soal</span></a></li>
                <li><a href="nilai_siswa.php"><i class="fa-solid fa-clipboard-check"></i> <span class="text-link">Nilai Anda</span></a></li>
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

        <div class="container">
            <h2 class="page-title">Daftar Kuis Tersedia</h2>

            <div class="data-card">
                <div class="card-header">
                    <h3>Pilih Kuis / Ujian</h3>
                </div>

                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th style="width: 8%;">No</th>
                                <th style="width: 25%;">Mata Pelajaran</th>
                                <th>Judul Kuis / Materi</th>
                                <th style="text-align: center; width: 15%;">Jumlah Soal</th>
                                <th style="text-align: center; width: 18%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if ($result && mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)): 
                            ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><span style="background: #eef2f3; color: #577883; padding: 4px 8px; border-radius: 4px; font-weight: 600; font-size: 12px;"><?= htmlspecialchars($row['namaMapel'] ?? 'Umum'); ?></span></td>
                                <td><strong><?= htmlspecialchars($row['judul']); ?></strong></td>
                                <td style="text-align: center; font-weight: 500; color: #666;"><?= $row['total_soal']; ?> Soal</td>
                                <td style="text-align: center;">
                                    <a href="soal_siswa.php?idMateri=<?= $row['idMateri']; ?>" class="btn-tambah" style="text-decoration: none; display: inline-block; padding: 6px 15px; font-size: 13px; background-color: #577883; color: white; border-radius: 4px;">
                                        <i class="fa-solid fa-play" style="font-size: 11px; margin-right: 5px;"></i> Mulai
                                    </a>
                                </td>
                            </tr>
                            <?php 
                                endwhile; 
                            } else {
                                echo "<tr><td colspan='5' style='text-align:center; padding:30px; color: #aaa;'>Belum ada kuis atau soal yang dirilis oleh guru.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="./script.js"></script>
</body>
</html>