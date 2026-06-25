<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'SISWA') {
    header("Location: login.php"); exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idSiswa = $_SESSION['idUser'];
    $idMateri = $_POST['idMateri'];
    $jawabanSiswa = isset($_POST['jawaban']) ? $_POST['jawaban'] : []; // Array berisi [idSoal => 'A/B/C/D']

    if (empty($idMateri)) {
        header("Location: dashboard_siswa.php"); exit();
    }

    $benar = 0;
    $salah = 0;
    $totalSoal = 0;

    // Ambil semua soal asli materi ini untuk dicocokkan kuncinya
    $queryKunci = "SELECT idSoal, jawabanBenar FROM soal WHERE idMateri = '$idMateri'";
    $resultKunci = mysqli_query($koneksi, $queryKunci);

    while ($row = mysqli_fetch_assoc($resultKunci)) {
        $idSoal = $row['idSoal'];
        $kunciAsli = $row['jawabanBenar'];
        
        // Cek apakah siswa menjawab soal ini
        if (isset($jawabanSiswa[$idSoal])) {
            if (strtoupper($jawabanSiswa[$idSoal]) === strtoupper($kunciAsli)) {
                $benar++;
            } else {
                $salah++;
            }
        } else {
            $salah++; // Dianggap salah jika tidak diisi
        }
        $totalSoal++;
    }

    // Rumus hitung nilai skala 0 - 100
    $nilaiFinal = ($totalSoal > 0) ? round(($benar / $totalSoal) * 100) : 0;

    // Cek apakah siswa sudah pernah mengerjakan materi ini sebelumnya
    $cekNilai = "SELECT idNilai FROM nilai WHERE idSiswa = '$idSiswa' AND idMateri = '$idMateri'";
    $resultCek = mysqli_query($koneksi, $cekNilai);

    if (mysqli_num_rows($resultCek) > 0) {
        // Jika sudah pernah ada nilainya, lakukan UPDATE pada kolom nilaiAkhir
        $querySimpan = "UPDATE nilai SET nilaiAkhir = '$nilaiFinal' WHERE idSiswa = '$idSiswa' AND idMateri = '$idMateri'";
    } else {
        // Jika belum pernah, lakukan INSERT baru sesuai kolom database kamu
        $querySimpan = "INSERT INTO nilai (idSiswa, idMateri, nilaiAkhir) VALUES ('$idSiswa', '$idMateri', '$nilaiFinal')";
    }

    if (mysqli_query($koneksi, $querySimpan)) {
        // Alihkan halaman kembali dengan alert sukses informatif
        echo "<script>
                alert('Ujian selesai! Anda menjawab $benar benar dari $totalSoal soal. Nilai Anda: $nilaiFinal');
                window.location.href = 'materi_siswa.php';
              </script>";
    } else {
        echo "Gagal menyimpan nilai: " . mysqli_error($koneksi);
    }
} else {
    header("Location: dashboard_siswa.php");
}
?>