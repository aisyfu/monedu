<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'GURU') {
    header("Location: login.php"); exit();
}
$idGuru = $_SESSION['idUser'];

// Ambil data soal beserta nama materi yang terkait (hanya materi milik guru ini)
$querySoal = "SELECT s.*, m.judul AS judul_materi 
              FROM soal s 
              JOIN materi m ON s.idMateri = m.idMateri 
              WHERE m.idGuru = '$idGuru' 
              ORDER BY s.idSoal DESC";
$resultSoal = mysqli_query($koneksi, $querySoal);

if (!$resultSoal) {
    die("<h1>Error Database: " . mysqli_error($koneksi) . "</h1>");
}

// Ambil daftar materi untuk pilihan di dropdown (hanya materi milik guru ini)
$queryMateri = "SELECT idMateri, judul FROM materi WHERE idGuru = '$idGuru' ORDER BY judul ASC";
$resultMateri = mysqli_query($koneksi, $queryMateri);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Soal - MonEdu</title>
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
                <li><a href="dashboard_guru.php"><i class="fa-solid fa-gauge"></i> <span class="text-link">Dashboard</span></a></li>
                <li><a href="kelola-materi-guru.php"><i class="fa-solid fa-book-open"></i> <span class="text-link">Kelola Materi</span></a></li>
                <li class="active"><a href="kelola-soal.php"><i class="fa-solid fa-clipboard-list"></i> <span class="text-link">Kelola Soal</span></a></li>
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

        <div class="container">
            <h2 class="page-title">Kelola Soal Pilihan Ganda</h2>

            <?php if (isset($_GET['status'])): ?>
            <div class="alert-box <?php echo str_starts_with($_GET['status'], 'sukses') ? 'alert-sukses' : 'alert-gagal'; ?>">
                <?php
                $pesan = [
                    'sukses_tambah' => 'Soal berhasil ditambahkan.',
                    'sukses_edit'   => 'Soal berhasil diperbarui.',
                    'sukses_hapus'  => 'Soal berhasil dihapus.',
                    'gagal'         => 'Terjadi kesalahan. Silakan coba lagi.'
                ];
                echo $pesan[$_GET['status']] ?? 'Terjadi kesalahan.';
                ?>
            </div>
            <?php endif; ?>

            <div class="data-card">
                <div class="card-header">
                    <h3>Daftar Soal</h3>
                    <div class="header-actions">
                        <div class="search-box">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" id="searchInput" placeholder="Search..." onkeyup="searchTable()">
                        </div>
                        <button class="btn-tambah" onclick="openModal('modalTambah')">
                            <i class="fa-solid fa-plus"></i> Tambah Soal
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="custom-table" id="mapelTable">
                        <thead>
                            <tr>
                                <th style="width: 5%;">No</th>
                                <th style="width: 25%;">Materi Terkait</th>
                                <th style="width: 45%;">Pertanyaan</th>
                                <th style="width: 10%; text-align: center;">Kunci</th>
                                <th style="width: 15%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if(mysqli_num_rows($resultSoal) > 0) {
                                while ($row = mysqli_fetch_assoc($resultSoal)): 
                            ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= htmlspecialchars($row['judul_materi']); ?></td>
                                <td><?= htmlspecialchars(substr($row['pertanyaan'], 0, 80)) . (strlen($row['pertanyaan']) > 80 ? '...' : ''); ?></td>
                                <td style="text-align: center;"><strong style="color: #577883;"><?= htmlspecialchars($row['jawabanBenar']); ?></strong></td>
                                <td>
                                    <div class="action-btns">
                                        <button class="btn-edit" title="Edit" onclick="openEditModal(
                                            '<?= $row['idSoal']; ?>', 
                                            '<?= $row['idMateri']; ?>', 
                                            '<?= htmlspecialchars(addslashes($row['pertanyaan'])); ?>', 
                                            '<?= htmlspecialchars(addslashes($row['opsi_a'])); ?>', 
                                            '<?= htmlspecialchars(addslashes($row['opsi_b'])); ?>', 
                                            '<?= htmlspecialchars(addslashes($row['opsi_c'])); ?>', 
                                            '<?= htmlspecialchars(addslashes($row['opsi_d'])); ?>', 
                                            '<?= $row['jawabanBenar']; ?>'
                                        )">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        
                                        <button class="btn-delete" title="Hapus" onclick="openDeleteModal('<?= $row['idSoal']; ?>')">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                endwhile; 
                            } else {
                                echo "<tr><td colspan='5' style='text-align:center; padding:30px;'>Belum ada soal yang dibuat.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal-overlay" id="modalTambah">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Tambah Soal</h3>
                <button class="close-modal" onclick="closeModal('modalTambah')"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="proses_tambah_soal.php" method="POST">
                <div class="form-group">
                    <label>Terkait Materi</label>
                    <select name="idMateri" required>
                        <option value="">-- Pilih Materi --</option>
                        <?php 
                        mysqli_data_seek($resultMateri, 0);
                        while ($m = mysqli_fetch_assoc($resultMateri)): 
                        ?>
                            <option value="<?= $m['idMateri']; ?>"><?= htmlspecialchars($m['judul']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Pertanyaan</label>
                    <textarea name="pertanyaan" rows="3" placeholder="Tuliskan pertanyaan soal..." required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px; font-family:inherit;"></textarea>
                </div>
                <div class="form-group"><label>Opsi A</label><input type="text" name="opsi_a" placeholder="Pilihan A" required></div>
                <div class="form-group"><label>Opsi B</label><input type="text" name="opsi_b" placeholder="Pilihan B" required></div>
                <div class="form-group"><label>Opsi C</label><input type="text" name="opsi_c" placeholder="Pilihan C" required></div>
                <div class="form-group"><label>Opsi D</label><input type="text" name="opsi_d" placeholder="Pilihan D" required></div>
                <div class="form-group">
                    <label>Kunci Jawaban</label>
                    <select name="jawabanBenar" required>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-batal" onclick="closeModal('modalTambah')">Batal</button>
                    <button type="submit" class="btn-simpan">Tambah</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal-overlay" id="modalEdit">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Edit Soal</h3>
                <button class="close-modal" onclick="closeModal('modalEdit')"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="proses_edit_soal.php" method="POST">
                <input type="hidden" name="idSoal" id="edit_idSoal">
                <div class="form-group">
                    <label>Terkait Materi</label>
                    <select name="idMateri" id="edit_idMateri" required>
                        <?php 
                        mysqli_data_seek($resultMateri, 0);
                        while ($m = mysqli_fetch_assoc($resultMateri)): 
                        ?>
                            <option value="<?= $m['idMateri']; ?>"><?= htmlspecialchars($m['judul']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Pertanyaan</label>
                    <textarea name="pertanyaan" id="edit_pertanyaan" rows="3" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px; font-family:inherit;"></textarea>
                </div>
                <div class="form-group"><label>Opsi A</label><input type="text" name="opsi_a" id="edit_opsi_a" required></div>
                <div class="form-group"><label>Opsi B</label><input type="text" name="opsi_b" id="edit_opsi_b" required></div>
                <div class="form-group"><label>Opsi C</label><input type="text" name="opsi_c" id="edit_opsi_c" required></div>
                <div class="form-group"><label>Opsi D</label><input type="text" name="opsi_d" id="edit_opsi_d" required></div>
                <div class="form-group">
                    <label>Kunci Jawaban</label>
                    <select name="jawabanBenar" id="edit_jawabanBenar" required>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-batal" onclick="closeModal('modalEdit')">Batal</button>
                    <button type="submit" class="btn-simpan">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Hapus -->
    <div class="modal-overlay" id="modalHapus">
        <div class="modal-box delete-box">
            <i class="fa-solid fa-triangle-exclamation delete-icon"></i>
            <h3 style="margin-bottom:10px;">Konfirmasi Hapus</h3>
            <p>Apakah Anda yakin ingin menghapus soal ini? Data akan dihapus secara permanen.</p>
            <form action="proses_hapus_soal.php" method="POST">
                <input type="hidden" name="id" id="hapus_id">
                <div class="modal-footer" style="justify-content:center;">
                    <button type="button" class="btn-batal" onclick="closeModal('modalHapus')">Batal</button>
                    <button type="submit" class="btn-hapus">Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <script src="./script.js"></script>
    <script>
    function openEditModal(id, idMateri, pertanyaan, a, b, c, d, jawaban) {
        document.getElementById('edit_idSoal').value = id;
        document.getElementById('edit_idMateri').value = idMateri;
        document.getElementById('edit_pertanyaan').value = pertanyaan;
        document.getElementById('edit_opsi_a').value = a;
        document.getElementById('edit_opsi_b').value = b;
        document.getElementById('edit_opsi_c').value = c;
        document.getElementById('edit_opsi_d').value = d;
        document.getElementById('edit_jawabanBenar').value = jawaban;
        openModal('modalEdit');
    }

    function openDeleteModal(id) {
        document.getElementById('hapus_id').value = id;
        openModal('modalHapus');
    }
    </script>
</body>
</html>