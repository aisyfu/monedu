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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - MonEdu</title> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { display: flex; background-color: #f4f6f8; height: 100vh; overflow: hidden; }

        .sidebar {
            width: 260px; background-color: #577883; color: #ffffff;
            display: flex; flex-direction: column; justify-content: space-between;
            padding: 30px 20px; transition: width 0.3s ease;
        }
        .sidebar.collapsed { width: 80px; align-items: center; }
        .sidebar.collapsed .text-link, .sidebar.collapsed .sidebar-brand span { display: none; }
        
        .sidebar-brand {
            display: flex; justify-content: space-between; align-items: center;
            font-size: 24px; font-weight: 700; margin-bottom: 40px; padding-left: 10px; width: 100%;
        }
        .sidebar.collapsed .sidebar-brand { justify-content: center; padding-left: 0; }
        .sidebar-brand i { font-size: 18px; cursor: pointer; color: rgba(255, 255, 255, 0.7); }

        .sidebar-menu { list-style: none; flex-grow: 1; width: 100%; }
        .sidebar-menu li { margin-bottom: 8px; }
        .sidebar-menu a {
            display: flex; align-items: center; gap: 15px; color: rgba(255, 255, 255, 0.7);
            text-decoration: none; padding: 14px 18px; border-radius: 12px; font-size: 15px;
            font-weight: 500; transition: all 0.3s ease; white-space: nowrap;
        }
        .sidebar-menu li.active a, .sidebar-menu a:hover { background-color: rgba(255, 255, 255, 0.15); color: #ffffff; }
        .sidebar.collapsed .sidebar-menu a { justify-content: center; padding: 14px 0; width: 100%; }
        .sidebar.collapsed .sidebar-menu i { font-size: 20px; margin: 0; }

        .sidebar-footer { width: 100%; }
        .sidebar-footer a {
            display: flex; align-items: center; gap: 15px; color: rgba(255, 255, 255, 0.7);
            text-decoration: none; padding: 14px 18px; font-size: 15px;
        }
        .sidebar.collapsed .sidebar-footer a { justify-content: center; padding: 14px 0; }

        /* Main Content Styles */
        .main-content { flex-grow: 1; display: flex; flex-direction: column; overflow-y: auto; }
        
        .topbar {
            background-color: #ffffff; height: 70px; display: flex; justify-content: flex-end;
            align-items: center; padding: 0 40px; box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }
        .profile-info { display: flex; align-items: center; gap: 12px; font-weight: 600; font-size: 14px; color: #333; text-align: right; }
        .profile-info span { color: #888; font-weight: 400; font-size: 12px; }
        .profile-info i { font-size: 38px; color: #577883; }

        /* Penyesuaian Padding Konten Bawah Topbar */
        .page-content {
            padding: 30px 40px; /* Jarak atas-bawah 30px, kiri-kanan 40px (sejajar topbar) */
        }

        .header {
            margin-bottom: 30px;
        }
        .header h1 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header p {
            color: #7f8c8d;
            font-size: 15px;
        }

        /* Card Styles */
        .card-container {
            display: flex;
            gap: 20px;
        }
        .card {
            background-color: white;
            padding: 25px 20px; /* Padding dalam card sedikit diperbesar */
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-left: 5px solid #3498db;
            transition: transform 0.3s ease;
        }
        .card:hover { transform: translateY(-5px); } /* Efek hover tipis */
        .card:nth-child(2) { border-left-color: #2ecc71; }
        .card:nth-child(3) { border-left-color: #f1c40f; }
        
        .card-info h3 {
            font-size: 2.2rem;
            color: #2c3e50;
            margin-top: 5px;
        }
        .card-info p {
            color: #95a5a6;
            font-size: 0.9rem;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div>
            <div class="sidebar-brand">
                <span class="text-link">MonEdu</span>
                <i class="fa-solid fa-angle-left" id="toggleSidebar"></i>
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
        </div>
    </div>

    <script>
        const toggleBtn = document.getElementById('toggleSidebar');
        const sidebar = document.getElementById('sidebar');

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            if (sidebar.classList.contains('collapsed')) {
                toggleBtn.classList.replace('fa-angle-left', 'fa-angle-right');
            } else {
                toggleBtn.classList.replace('fa-angle-right', 'fa-angle-left');
            }
        });
    </script>
</body>
</html>