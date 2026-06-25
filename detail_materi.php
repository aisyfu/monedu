<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'SISWA') {
    header("Location: login.php?error=akses_ditolak");
    exit();
}

// Validasi ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: materi_siswa.php");
    exit();
}

$idMateri = (int)$_GET['id'];

// Query detail materi
$stmt = mysqli_prepare($koneksi, "
    SELECT m.idMateri, m.judul, m.deskripsi, m.isi, m.kategori,
           m.statusValidasi, m.createdAt,
           u.nama AS namaGuru,
           mp.namaMapel
    FROM materi m
    JOIN guru g ON m.idGuru = g.idUser
    JOIN user u ON g.idUser = u.idUser
    LEFT JOIN mata_pelajaran mp ON m.idMapel = mp.idMapel
    WHERE m.idMateri = ?
");
mysqli_stmt_bind_param($stmt, "i", $idMateri);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$materi = mysqli_fetch_assoc($result);

// Jika materi tidak ditemukan
if (!$materi) {
    header("Location: materi_siswa.php");
    exit();
}

// Cek apakah file ada di server
$filePath = 'uploads/' . $materi['isi'];
$fileAda  = file_exists($filePath);
$ext      = strtolower(pathinfo($materi['isi'], PATHINFO_EXTENSION));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($materi['judul']); ?> - MonEdu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="./script.js"></script>
    <style>
        .detail-wrapper { max-width: 900px; }
        .detail-header-card {
            background: #fff; border-radius: 16px;
            padding: 28px 30px; margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.04);
        }
        .detail-title-row {
            display: flex; align-items: flex-start;
            justify-content: space-between; gap: 16px;
            flex-wrap: wrap; margin-bottom: 20px;
        }
        .detail-title-left { display: flex; align-items: center; gap: 16px; }
        .detail-icon {
            width: 52px; height: 52px; border-radius: 12px;
            background: #eef3f5; display: flex;
            align-items: center; justify-content: center;
            font-size: 22px; color: #577883; flex-shrink: 0;
        }
        .detail-title-left h2 { font-size: 20px; font-weight: 700; color: #2c3e50; margin-bottom: 6px; }
        .detail-badge {
            display: inline-block; padding: 3px 12px;
            border-radius: 20px; font-size: 12px; font-weight: 600;
            background: #e8f4f8; color: #577883;
        }
        .btn-unduh-detail {
            display: flex; align-items: center; gap: 8px;
            padding: 11px 22px; border-radius: 10px;
            background: #577883; color: #fff; border: none;
            font-size: 14px; font-weight: 600;
            text-decoration: none; white-space: nowrap;
            transition: background 0.2s; cursor: pointer;
        }
        .btn-unduh-detail:hover { background: #456069; }
        .detail-meta-grid {
            display: grid; grid-template-columns: repeat(3, 1fr);
            gap: 14px; background: #f8f9fb;
            border-radius: 10px; padding: 16px;
        }
        .detail-meta-item { display: flex; align-items: center; gap: 12px; }
        .detail-meta-ico {
            width: 36px; height: 36px; border-radius: 8px;
            background: #fff; border: 1px solid #e0e6ea;
            display: flex; align-items: center; justify-content: center;
            color: #577883; font-size: 15px; flex-shrink: 0;
        }
        .detail-meta-lbl { font-size: 10.5px; text-transform: uppercase; color: #9aa0b0; font-weight: 600; letter-spacing: 0.5px; }
        .detail-meta-val { font-size: 13.5px; font-weight: 600; color: #2c3e50; }
        .detail-section-card {
            background: #fff; border-radius: 16px;
            padding: 24px 30px; margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.04);
        }
        .detail-section-card h3 { font-size: 16px; font-weight: 700; color: #2c3e50; margin-bottom: 12px; }
        .detail-desc { font-size: 14px; color: #555; line-height: 1.8; }
        .preview-area {
            background: #f8f9fb; border-radius: 10px;
            border: 1.5px dashed #c8d0e0;
            min-height: 400px; display: flex;
            align-items: center; justify-content: center;
            flex-direction: column; gap: 10px;
        }
        .preview-area iframe { width: 100%; height: 500px; border: none; border-radius: 8px; }
        .preview-area .no-preview { text-align: center; padding: 40px; }
        .preview-area .no-preview i { font-size: 52px; color: #c0c8d8; display: block; margin-bottom: 12px; }
        .preview-area .no-preview p { font-size: 14px; color: #9aa0b0; }
        .back-link {
            display: inline-flex; align-items: center; gap: 8px;
            color: #577883; font-size: 14px; font-weight: 500;
            text-decoration: none; margin-bottom: 20px;
            transition: opacity 0.2s;
        }
        .back-link:hover { opacity: 0.75; }
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
            <a href="materi_siswa.php" class="back-link">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Materi
            </a>

            <div class="detail-wrapper">
                <!-- Header Card -->
                <div class="detail-header-card">
                    <div class="detail-title-row">
                        <div class="detail-title-left">
                            <div class="detail-icon"><i class="fa-solid fa-file-pdf"></i></div>
                            <div>
                                <h2><?php echo htmlspecialchars($materi['judul']); ?></h2>
                                <span class="detail-badge"><?php echo htmlspecialchars($materi['kategori']); ?></span>
                            </div>
                        </div>
                        <a href="download_materi.php?id=<?php echo $materi['idMateri']; ?>" class="btn-unduh-detail">
                            <i class="fa-solid fa-download"></i> Unduh Materi
                        </a>
                    </div>

                    <div class="detail-meta-grid">
                        <div class="detail-meta-item">
                            <div class="detail-meta-ico"><i class="fa-solid fa-user-tie"></i></div>
                            <div>
                                <div class="detail-meta-lbl">Pengajar</div>
                                <div class="detail-meta-val"><?php echo htmlspecialchars($materi['namaGuru']); ?></div>
                            </div>
                        </div>
                        <div class="detail-meta-item">
                            <div class="detail-meta-ico"><i class="fa-solid fa-calendar"></i></div>
                            <div>
                                <div class="detail-meta-lbl">Tanggal Upload</div>
                                <div class="detail-meta-val"><?php echo date('d M Y', strtotime($materi['createdAt'])); ?></div>
                            </div>
                        </div>
                        <div class="detail-meta-item">
                            <div class="detail-meta-ico"><i class="fa-solid fa-file"></i></div>
                            <div>
                                <div class="detail-meta-lbl">Nama File</div>
                                <div class="detail-meta-val"><?php echo htmlspecialchars($materi['isi']); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Deskripsi -->
                <div class="detail-section-card">
                    <h3>Deskripsi Materi</h3>
                    <p class="detail-desc">
                        <?php echo nl2br(htmlspecialchars($materi['deskripsi'] ?? 'Tidak ada deskripsi.')); ?>
                    </p>
                </div>

                <!-- Preview -->
                <div class="detail-section-card">
                    <h3>Preview Materi</h3>
                    <div class="preview-area">
                        <?php if ($fileAda && $ext === 'pdf'): ?>
                            <iframe src="<?php echo $filePath; ?>#toolbar=0" allowfullscreen></iframe>
                        <?php elseif ($fileAda && in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                            <img src="<?php echo $filePath; ?>" style="max-width:100%;border-radius:8px;" alt="Preview">
                        <?php else: ?>
                            <div class="no-preview">
                                <i class="fa-solid fa-file-circle-question"></i>
                                <p>Preview tidak tersedia untuk file ini.<br>Klik <strong>Unduh Materi</strong> untuk mengakses file.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
