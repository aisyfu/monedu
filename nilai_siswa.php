<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'SISWA') {
    header("Location: login.php?error=akses_ditolak");
    exit();
}

$idSiswa = $_SESSION['idUser'];

// Filter
$search   = isset($_GET['search'])   ? trim($_GET['search'])   : '';
$kategori = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';

// Statistik: avg, max, min
$stmtStat = mysqli_prepare($koneksi, "
    SELECT ROUND(AVG(n.nilaiAkhir), 1) as rata,
           MAX(n.nilaiAkhir) as tertinggi,
           MIN(n.nilaiAkhir) as terendah,
           COUNT(*) as total
    FROM nilai n
    WHERE n.idSiswa = ?
");
mysqli_stmt_bind_param($stmtStat, "i", $idSiswa);
mysqli_stmt_execute($stmtStat);
$stat = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtStat));

$rata      = $stat['rata']      ?? 0;
$tertinggi = $stat['tertinggi'] ?? 0;
$terendah  = $stat['terendah']  ?? 0;
$totalSoal = $stat['total']     ?? 0;

// Query riwayat nilai + filter
$sql = "
    SELECT n.idNilai, n.nilaiAkhir, m.judul, m.kategori,
           u.nama AS namaGuru, m.createdAt
    FROM nilai n
    JOIN materi m ON n.idMateri = m.idMateri
    JOIN guru g ON m.idGuru = g.idUser
    JOIN user u ON g.idUser = u.idUser
    WHERE n.idSiswa = ?
";
$params = [$idSiswa];
$types  = "i";

if ($search !== '') {
    $sql .= " AND m.judul LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}
if ($kategori !== '') {
    $sql .= " AND m.kategori = ?";
    $params[] = $kategori;
    $types .= "s";
}

$sql .= " ORDER BY m.createdAt DESC";

$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Daftar kategori untuk filter
$qKat = mysqli_prepare($koneksi, "
    SELECT DISTINCT m.kategori FROM nilai n
    JOIN materi m ON n.idMateri = m.idMateri
    WHERE n.idSiswa = ? ORDER BY m.kategori
");
mysqli_stmt_bind_param($qKat, "i", $idSiswa);
mysqli_stmt_execute($qKat);
$resKat = mysqli_stmt_get_result($qKat);
$kategoriList = [];
while ($k = mysqli_fetch_assoc($resKat)) {
    $kategoriList[] = $k['kategori'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lihat Nilai - MonEdu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="./script.js"></script>
    <style>
        .stat-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: #fff;
            border-radius: 14px;
            padding: 22px 24px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.04);
            transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-3px); }
        .stat-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; flex-shrink: 0;
        }
        .stat-icon.avg   { background: #e8f4ff; color: #2980b9; }
        .stat-icon.high  { background: #e8f8ee; color: #27ae60; }
        .stat-icon.low   { background: #fdeaea; color: #e74c3c; }
        .stat-icon.total { background: #fef9e7; color: #f39c12; }
        .stat-lbl { font-size: 12.5px; color: #95a5a6; font-weight: 500; margin-bottom: 4px; }
        .stat-val { font-size: 28px; font-weight: 700; line-height: 1; }
        .stat-val.avg   { color: #2c3e50; }
        .stat-val.high  { color: #27ae60; }
        .stat-val.low   { color: #e74c3c; }
        .stat-val.total { color: #f39c12; }
        .nilai-chip {
            font-weight: 700; font-size: 13.5px;
            padding: 4px 10px; border-radius: 20px;
        }
        .chip-hijau { color: #27ae60; background: #e8f8ee; }
        .chip-kuning { color: #d68910; background: #fef9e7; }
        .chip-merah { color: #e74c3c; background: #fdeaea; }
        .empty-state {
            text-align: center; padding: 60px 20px; color: #aaa;
        }
        .empty-state i { font-size: 48px; margin-bottom: 12px; display: block; }
    </style>
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
                <li><a href="#"><i class="fa-solid fa-pencil"></i> <span class="text-link">Soal</span></a></li>
                <li class="active"><a href="nilai_siswa.php"><i class="fa-solid fa-chart-simple"></i> <span class="text-link">Lihat Nilai</span></a></li>
                <li><a href="#"><i class="fa-solid fa-user"></i> <span class="text-link">Profil</span></a></li>
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
            <h2 class="page-title">Lihat Nilai</h2>

            <!-- Kartu Statistik -->
            <div class="stat-cards">
                <div class="stat-card">
                    <div class="stat-icon avg"><i class="fa-solid fa-award"></i></div>
                    <div>
                        <div class="stat-lbl">Rata-rata Nilai</div>
                        <div class="stat-val avg"><?php echo $totalSoal > 0 ? $rata : '-'; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon high"><i class="fa-solid fa-trending-up"></i></div>
                    <div>
                        <div class="stat-lbl">Nilai Tertinggi</div>
                        <div class="stat-val high"><?php echo $totalSoal > 0 ? $tertinggi : '-'; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon low"><i class="fa-solid fa-trending-down"></i></div>
                    <div>
                        <div class="stat-lbl">Nilai Terendah</div>
                        <div class="stat-val low"><?php echo $totalSoal > 0 ? $terendah : '-'; ?></div>
                    </div>
                </div>
            </div>

            <!-- Tabel Riwayat Nilai -->
            <div class="data-card">
                <div class="card-header">
                    <h3>Riwayat Nilai</h3>
                    <div class="header-actions">
                        <form method="GET" action="nilai_siswa.php" id="filterNilai" style="display:flex;gap:10px;align-items:center;">
                            <select name="kategori" onchange="document.getElementById('filterNilai').submit()"
                                style="padding:9px 14px;border-radius:10px;border:1px solid #e0e0e0;background:#f1f3f5;font-size:13px;outline:none;font-family:'Poppins',sans-serif;">
                                <option value="">Semua Kategori</option>
                                <?php foreach ($kategoriList as $kat): ?>
                                    <option value="<?php echo htmlspecialchars($kat); ?>"
                                        <?php echo ($kategori === $kat) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($kat); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="search-box">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" name="search" placeholder="Cari soal..."
                                    value="<?php echo htmlspecialchars($search); ?>"
                                    onchange="document.getElementById('filterNilai').submit()">
                            </div>
                            <?php if ($search || $kategori): ?>
                                <a href="nilai_siswa.php" style="font-size:12px;color:#e74c3c;text-decoration:none;">
                                    <i class="fa-solid fa-xmark"></i> Reset
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Judul Soal</th>
                                <th>Kategori</th>
                                <th>Guru</th>
                                <th>Tanggal</th>
                                <th>Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($result)):
                                $n = $row['nilaiAkhir'];
                                if ($n >= 80) {
                                    $chipClass = 'chip-hijau';
                                } elseif ($n >= 70) {
                                    $chipClass = 'chip-kuning';
                                } else {
                                    $chipClass = 'chip-merah';
                                }
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['judul']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['kategori']); ?></td>
                                <td><?php echo htmlspecialchars($row['namaGuru']); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($row['createdAt'])); ?></td>
                                <td>
                                    <span class="nilai-chip <?php echo $chipClass; ?>">
                                        <?php echo $n; ?> / 100
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fa-solid fa-chart-simple"></i>
                            <p>Belum ada nilai yang tersedia<?php echo ($search || $kategori) ? ' untuk filter ini' : ''; ?>.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
