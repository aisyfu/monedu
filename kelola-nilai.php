<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'GURU') {
    header("Location: login.php"); exit();
}
$idGuru = $_SESSION['idUser'];

$queryNilai = "SELECT n.*, u.nama, u.email, m.judul as judulMateri
               FROM nilai n
               JOIN siswa s ON n.idSiswa = s.idUser
               JOIN user u ON s.idUser = u.idUser
               JOIN materi m ON n.idMateri = m.idMateri
               WHERE m.idGuru = '$idGuru'
               ORDER BY n.idNilai DESC";

$resultNilai = mysqli_query($koneksi, $queryNilai);
if (!$resultNilai) {
    die("Query Error: " . mysqli_error($koneksi));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Nilai - MonEdu</title>
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
                <li><a href="dashboard_guru.php"><i class="fa-solid fa-gauge"></i> <span class="text-link">Dashboard</span></a></li>
                <li><a href="kelola-materi-guru.php"><i class="fa-solid fa-book-open"></i> <span class="text-link">Kelola Materi</span></a></li>
                <li><a href="kelola-soal.php"><i class="fa-solid fa-clipboard-list"></i> <span class="text-link">Kelola Soal</span></a></li>
                <li class="active"><a href="kelola-nilai.php"><i class="fa-solid fa-clipboard-check"></i> <span class="text-link">Kelola Nilai</span></a></li>
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
                <h1>Kelola Nilai</h1>
                <p>Pantau dan kelola hasil pengerjaan kuis siswa Anda.</p>
            </div>

            <div class="recent-section">
                <div class="table-responsive">
                    <table class="recent-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Siswa</th>
                                <th>Email</th>
                                <th>Materi / Kuis</th>
                                <th>Nilai Akhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            if (mysqli_num_rows($resultNilai) > 0):
                                while($row = mysqli_fetch_assoc($resultNilai)):
                                    $nilai = $row['nilaiAkhir'] ?? 0;
                                    $badgeStyle = $nilai >= 75
                                        ? 'background:#d1fae5; color:#16a34a;'
                                        : 'background:#fee2e2; color:#dc2626;';
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['judulMateri']); ?></td>
                                <td>
                                    <span style="<?php echo $badgeStyle; ?> padding:3px 10px; border-radius:20px; font-size:13px; font-weight:600;">
                                        <?php echo $nilai; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile;
                            else: ?>
                            <tr><td colspan="5" style="text-align:center;">Belum ada data nilai siswa.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
