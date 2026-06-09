<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: login.php?error=akses_ditolak");
    exit();
}

$queryGuru = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM user u JOIN role r ON u.idRole = r.idRole WHERE r.namaRole = 'Guru'");
$totalGuru = mysqli_fetch_assoc($queryGuru)['total'];

$querySiswa = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM user u JOIN role r ON u.idRole = r.idRole WHERE r.namaRole = 'Siswa'");
$totalSiswa = mysqli_fetch_assoc($querySiswa)['total'];

$queryMapel = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM mata_pelajaran");
$totalMapel = $queryMapel ? mysqli_fetch_assoc($queryMapel)['total'] : 0;

$queryUserTerbaru = mysqli_query($koneksi, "
    SELECT u.nama, u.email, r.namaRole, u.createdAt 
    FROM user u 
    JOIN role r ON u.idRole = r.idRole 
    WHERE r.namaRole IN ('Guru', 'Siswa') 
    ORDER BY u.createdAt DESC 
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - MonEdu</title> 
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
                <li class="active"><a href="dashboard_admin.php"><i class="fa-solid fa-gauge"></i> <span class="text-link">Dashboard</span></a></li>
                <li><a href="kelola_guru.php"><i class="fa-solid fa-user-tie"></i> <span class="text-link">Kelola Guru</span></a></li>
                <li><a href="kelola_siswa.php"><i class="fa-solid fa-users"></i> <span class="text-link">Kelola Siswa</span></a></li>
                <li><a href="kelola_pelajaran.php"><i class="fa-solid fa-book-open"></i> <span class="text-link">Kelola Mata Pelajaran</span></a></li>
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
                <p>Ini adalah ringkasan data pada sistem monitoring pembelajaran saat ini.</p>
            </div>

            <div class="card-container">
                <div class="card">
                    <div class="card-info">
                        <p>Total Guru</p>
                        <h3><?php echo $totalGuru; ?></h3>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-info">
                        <p>Total Siswa</p>
                        <h3><?php echo $totalSiswa; ?></h3>
                    </div>
                </div>

                <div class="card">
                    <div class="card-info">
                        <p>Mata Pelajaran</p>
                        <h3><?php echo $totalMapel; ?></h3>
                    </div>
                </div>
            </div>

            <div class="recent-section">
                <h2>User Terbaru</h2>
                <div class="table-responsive">
                    <table class="recent-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Role</th>
                                <th>Tanggal Dibuat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if(mysqli_num_rows($queryUserTerbaru) > 0) {
                                while($row = mysqli_fetch_assoc($queryUserTerbaru)) {
                                    $tanggal = date('d M Y, H:i', strtotime($row['createdAt']));
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo $row['nama']; ?></td>
                                <td><?php echo $row['namaRole']; ?></td>
                                <td><?php echo $tanggal; ?></td>
                            </tr>
                            <?php 
                                }
                            } else {
                                echo "<tr><td colspan='4' style='text-align:center;'>Belum ada data user.</td></tr>";
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