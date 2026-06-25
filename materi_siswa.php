<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'SISWA') {
    header("Location: login.php?error=akses_ditolak");
    exit();
}

// Ambil filter dari GET
$search   = isset($_GET['search'])   ? trim($_GET['search'])   : '';
$kategori = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';

// Query materi tervalidasi + join guru + mata_pelajaran
$sql = "
    SELECT m.idMateri, m.judul, m.deskripsi, m.isi, m.kategori,
           m.statusValidasi, m.createdAt,
           u.nama AS namaGuru,
           mp.namaMapel
    FROM materi m
    JOIN guru g ON m.idGuru = g.idUser
    JOIN user u ON g.idUser = u.idUser
    LEFT JOIN mata_pelajaran mp ON m.idMapel = mp.idMapel
    WHERE 1=1
";

$params = [];
$types  = "";

if ($search !== '') {
    $sql .= " AND (m.judul LIKE ? OR m.kategori LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $types .= "ss";
}
if ($kategori !== '') {
    $sql .= " AND m.kategori = ?";
    $params[] = $kategori;
    $types .= "s";
}

$sql .= " ORDER BY m.createdAt DESC";

$stmt = mysqli_prepare($koneksi, $sql);
if ($types !== '') {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Ambil semua kategori untuk dropdown filter
$qKat   = mysqli_query($koneksi, "SELECT DISTINCT kategori FROM materi ORDER BY kategori");
$kategoriList = [];
while ($k = mysqli_fetch_assoc($qKat)) {
    $kategoriList[] = $k['kategori'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materi - MonEdu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="./script.js"></script>
    <style>
        .materi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 20px;
            margin-top: 10px;
        }
        .materi-card {
            background: #fff;
            border-radius: 14px;
            padding: 22px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            gap: 8px;
            transition: transform 0.2s, box-shadow 0.2s;
            border-top: 4px solid #577883;
        }
        .materi-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(87,120,131,0.15);
        }
        .materi-card-icon {
            width: 44px; height: 44px;
            background: #eef3f5;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: #577883; font-size: 20px;
            margin-bottom: 6px;
        }
        .materi-card h4 {
            font-size: 15px; font-weight: 600; color: #2c3e50;
            line-height: 1.4; margin: 0;
        }
        .materi-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11.5px; font-weight: 600;
            background: #e8f4f8; color: #577883;
        }
        .materi-meta {
            font-size: 12.5px; color: #7f8c8d; margin: 0;
        }
        .materi-meta span { color: #444; font-weight: 500; }
        .materi-card-actions {
            display: flex; gap: 8px; margin-top: 6px;
        }
        .btn-lihat {
            flex: 1; padding: 9px;
            border: 1.5px solid #577883; border-radius: 8px;
            background: #fff; color: #577883;
            font-size: 13px; font-weight: 500;
            cursor: pointer; text-align: center;
            text-decoration: none; display: flex;
            align-items: center; justify-content: center; gap: 6px;
            transition: all 0.2s;
        }
        .btn-lihat:hover { background: #eef3f5; }
        .btn-unduh {
            flex: 1; padding: 9px;
            border: none; border-radius: 8px;
            background: #577883; color: #fff;
            font-size: 13px; font-weight: 500;
            cursor: pointer; text-align: center;
            text-decoration: none; display: flex;
            align-items: center; justify-content: center; gap: 6px;
            transition: background 0.2s;
        }
        .btn-unduh:hover { background: #456069; }
        .empty-state {
            text-align: center; padding: 60px 20px; color: #aaa;
        }
        .empty-state i { font-size: 48px; margin-bottom: 12px; display: block; }
        .filter-bar {
            display: flex; gap: 12px; align-items: center; flex-wrap: wrap;
            margin-bottom: 6px;
        }
        .filter-bar select {
            padding: 9px 14px; border-radius: 10px;
            border: 1px solid #e0e0e0; background: #f1f3f5;
            font-size: 13px; outline: none; font-family: 'Poppins', sans-serif;
        }
        .notif {
            padding: 12px 18px; border-radius: 10px;
            font-size: 13.5px; font-weight: 500;
            margin-bottom: 20px; display: flex; align-items: center; gap: 10px;
        }
        .notif.sukses { background: #e8f5e9; color: #2e7d32; border-left: 4px solid #2e7d32; }
        .notif.gagal { background: #fee2e2; color: #991b1b; border-left: 4px solid #ef4444; }
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
                <li class="active"><a href="materi_siswa.php"><i class="fa-solid fa-book-open"></i> <span class="text-link">Materi</span></a></li>
                <li><a href="daftar_soal.php"><i class="fa-solid fa-pencil"></i> <span class="text-link">Soal</span></a></li>
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

        <div class="container">
            <h2 class="page-title">Materi</h2>

            <div class="data-card">
                <div class="card-header">
                    <h3>Daftar Materi</h3>
                    <div class="header-actions">
                        <!-- Filter kategori -->
                        <form method="GET" action="materi_siswa.php" id="filterForm" style="display:flex;gap:10px;align-items:center;">
                            <select name="kategori" onchange="document.getElementById('filterForm').submit()" class="filter-select"
                                style="padding:9px 14px;border-radius:10px;border:1px solid #e0e0e0;background:#f1f3f5;font-size:13px;outline:none;font-family:'Poppins',sans-serif;">
                                <option value="">Semua Kategori</option>
                                <?php foreach ($kategoriList as $kat): ?>
                                    <option value="<?php echo htmlspecialchars($kat); ?>"
                                        <?php echo ($kategori === $kat) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($kat); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <!-- Search box -->
                            <div class="search-box">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" name="search" placeholder="Cari materi..."
                                    value="<?php echo htmlspecialchars($search); ?>"
                                    onchange="document.getElementById('filterForm').submit()">
                            </div>
                            <?php if ($search || $kategori): ?>
                                <a href="materi_siswa.php" style="font-size:12px;color:#e74c3c;text-decoration:none;">
                                    <i class="fa-solid fa-xmark"></i> Reset
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <?php if (mysqli_num_rows($result) > 0): ?>
                    <div class="materi-grid">
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <div class="materi-card">
                            <div class="materi-card-icon">
                                <i class="fa-solid fa-file-pdf"></i>
                            </div>
                            <h4><?php echo htmlspecialchars($row['judul']); ?></h4>
                            <div><span class="materi-badge"><?php echo htmlspecialchars($row['kategori']); ?></span></div>
                            <p class="materi-meta">Guru: <span><?php echo htmlspecialchars($row['namaGuru']); ?></span></p>
                            <p class="materi-meta">Upload: <span><?php echo date('d M Y', strtotime($row['createdAt'])); ?></span></p>
                            <p class="materi-meta">File: <span><?php echo htmlspecialchars($row['isi']); ?></span></p>
                            <div class="materi-card-actions">
                                <a href="detail_materi.php?id=<?php echo $row['idMateri']; ?>" class="btn-lihat">
                                    <i class="fa-solid fa-eye"></i> Lihat
                                </a>
                                <a href="download_materi.php?id=<?php echo $row['idMateri']; ?>" class="btn-unduh">
                                    <i class="fa-solid fa-download"></i> Unduh
                                </a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-folder-open"></i>
                        <p>Tidak ada materi yang ditemukan.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
