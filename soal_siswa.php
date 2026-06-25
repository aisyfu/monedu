<?php
session_start();
include 'koneksi.php';

// Pastikan yang login adalah SISWA
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'SISWA') {
    header("Location: login.php"); exit();
}

$idSiswa = $_SESSION['idUser'];
// Menangkap ID Materi dari URL secara aman
$idMateri = isset($_GET['idMateri']) ? mysqli_real_escape_string($koneksi, $_GET['idMateri']) : '';

if (empty($idMateri)) {
    die("<h1 style='text-align:center; margin-top:50px; font-family:sans-serif;'>Materi tidak ditemukan!</h1>");
}

// 1. Ambil info detail materi yang sedang diujikan
$queryMateri = "SELECT judul FROM materi WHERE idMateri = '$idMateri'";
$resMateri = mysqli_query($koneksi, $queryMateri);
$materi = mysqli_fetch_assoc($resMateri);

if (!$materi) {
    die("<h1 style='text-align:center; margin-top:50px; font-family:sans-serif;'>Materi tidak ditemukan!</h1>");
}

// 2. Ambil semua soal berdasarkan materi ini (Diacak menggunakan ORDER BY RAND())
$querySoal = "SELECT * FROM soal WHERE idMateri = '$idMateri' ORDER BY RAND()";
$resultSoal = mysqli_query($koneksi, $querySoal);
$totalSoal = mysqli_num_rows($resultSoal);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mengerjakan Soal - MonEdu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f7f9fa;
            margin: 0;
            padding: 0;
            overflow-y: auto !important; 
            height: auto !important;
        }
        .quiz-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px 6px 20px;
        }
        .quiz-header {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.03);
            margin-bottom: 25px;
            border-left: 5px solid #577883;
        }
        .quiz-header h1 {
            margin: 0 0 10px 0;
            font-size: 22px;
            color: #333;
        }
        .quiz-header p {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }
        .soal-card {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.03);
            margin-bottom: 20px;
        }
        .soal-text {
            font-size: 16px;
            font-weight: 500;
            color: #2d3748;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        .opsi-container {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .opsi-label {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 14px;
            color: #4a5568;
        }
        .opsi-label:hover {
            background: #edf2f7;
            border-color: #cbd5e0;
        }
        .opsi-label input[type="radio"] {
            margin-right: 12px;
            accent-color: #577883;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        .btn-submit-quiz {
            background-color: #577883;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            display: block;
            width: 100%;
            box-shadow: 0 4px 6px rgba(87, 120, 131, 0.2);
            transition: background 0.2s;
            margin-top: 30px;
            margin-bottom: 5px;
            font-family: 'Poppins', sans-serif;
        }
        .btn-submit-quiz:hover {
            background-color: #445e67;
        }
        .empty-state {
            text-align: center;
            padding: 50px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.03);
        }
    </style>
</head>
<body>

    <div class="quiz-container">
        <div class="quiz-header">
            <h1><i class="fa-solid fa-graduation-cap"></i> Lembar Kerja Ujian</h1>
            <p>Materi: <strong><?= htmlspecialchars($materi['judul']); ?></strong></p>
            <p>Jumlah Soal: <strong><?= $totalSoal; ?> Pertanyaan Pilihan Ganda</strong></p>
        </div>

        <?php if ($totalSoal > 0): ?>
            <form action="proses_hitung_nilai.php" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyelesaikan kuis dan mengirim semua jawaban?')">
                <input type="hidden" name="idMateri" value="<?= $idMateri; ?>">
                
                <?php 
                $no = 1;
                while ($row = mysqli_fetch_assoc($resultSoal)): 
                ?>
                    <div class="soal-card">
                        <div class="soal-text">
                            <strong><?= $no++; ?>.</strong> <?= htmlspecialchars($row['pertanyaan']); ?>
                        </div>
                        <div class="opsi-container">
                            <label class="opsi-label">
                                <input type="radio" name="jawaban[<?= $row['idSoal']; ?>]" value="A" required>
                                <strong>A.</strong> &nbsp;<?= htmlspecialchars($row['opsi_a']); ?>
                            </label>
                            <label class="opsi-label">
                                <input type="radio" name="jawaban[<?= $row['idSoal']; ?>]" value="B">
                                <strong>B.</strong> &nbsp;<?= htmlspecialchars($row['opsi_b']); ?>
                            </label>
                            <label class="opsi-label">
                                <input type="radio" name="jawaban[<?= $row['idSoal']; ?>]" value="C">
                                <strong>C.</strong> &nbsp;<?= htmlspecialchars($row['opsi_c']); ?>
                            </label>
                            <label class="opsi-label">
                                <input type="radio" name="jawaban[<?= $row['idSoal']; ?>]" value="D">
                                <strong>D.</strong> &nbsp;<?= htmlspecialchars($row['opsi_d']); ?>
                            </label>
                        </div>
                    </div>
                <?php endwhile; ?>

                <button type="submit" class="btn-submit-quiz">
                    <i class="fa-solid fa-paper-plane"></i> Selesai & Kirim Jawaban
                </button>
            </form>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa-solid fa-clipboard-question fa-3x" style="color: #ccc; margin-bottom: 15px;"></i>
                <h3>Belum Ada Soal</h3>
                <p>Guru belum mengunggah pertanyaan kuis untuk materi ini.</p>
                <br>
                <a href="dashboard_siswa.php" style="color: #577883; font-weight: 600; text-decoration: none;"><i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard</a>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>