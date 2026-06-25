<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'GURU') {
    header("Location: login.php"); exit();
}
$idGuru = $_SESSION['idUser'];

$queryMateri = "SELECT m.*, mp.namaMapel FROM materi m 
                LEFT JOIN mata_pelajaran mp ON m.idMapel = mp.idMapel 
                WHERE m.idGuru = '$idGuru' ORDER BY m.idMateri DESC";
$resultMateri = mysqli_query($koneksi, $queryMateri);
if (!$resultMateri) {
    die("<h1>Error Database: " . mysqli_error($koneksi) . "</h1>");
}

$queryMapel = "SELECT * FROM mata_pelajaran ORDER BY namaMapel ASC";
$resultMapel = mysqli_query($koneksi, $queryMapel);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Materi - MonEdu</title>
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
                <li class="active"><a href="kelola-materi-guru.php"><i class="fa-solid fa-book-open"></i> <span class="text-link">Kelola Materi</span></a></li>
                <li><a href="kelola-soal.php"><i class="fa-solid fa-clipboard-list"></i> <span class="text-link">Kelola Soal</span></a></li>
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
            <h2 class="page-title">Kelola Materi</h2>

            <?php if (isset($_GET['status'])): ?>
            <div class="alert-box <?php echo str_starts_with($_GET['status'], 'sukses') ? 'alert-sukses' : 'alert-gagal'; ?>">
                <?php
                $pesan = [
                    'sukses_tambah' => 'Materi berhasil ditambahkan.',
                    'sukses_edit'   => 'Materi berhasil diperbarui.',
                    'sukses_hapus'  => 'Materi berhasil dihapus.',
                    'gagal'         => 'Terjadi kesalahan. Silakan coba lagi.',
                    'file_besar'    => 'File terlalu besar. Maksimal 30MB.',
                    'bukan_pdf'     => 'File harus berformat PDF.',
                ];
                echo $pesan[$_GET['status']] ?? 'Terjadi kesalahan.';
                ?>
            </div>
            <?php endif; ?>

            <div class="data-card">
                <div class="card-header">
                    <h3>Daftar Materi</h3>
                    <div class="header-actions">
                        <div class="search-box">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" id="searchInput" placeholder="Search..." onkeyup="searchTable()">
                        </div>
                        <button class="btn-tambah" onclick="openModal('modalTambah')">
                            <i class="fa-solid fa-plus"></i> Tambah Materi
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="custom-table" id="mapelTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Judul</th>
                                <th>Kategori</th>
                                <th>Mata Pelajaran</th>
                                <th>File PDF</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            if ($resultMateri && mysqli_num_rows($resultMateri) > 0):
                                while ($row = mysqli_fetch_assoc($resultMateri)): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($row['judul']); ?></td>
                                <td><?php echo htmlspecialchars($row['kategori']); ?></td>
                                <td><?php echo htmlspecialchars($row['namaMapel'] ?? 'Umum'); ?></td>
                                <td>
                                    <?php if (!empty($row['isi'])): ?>
                                        <a href="uploads/<?php echo htmlspecialchars($row['isi']); ?>" target="_blank" style="color:#577883; font-weight:600; font-size:13px;">
                                            <i class="fa-solid fa-file-pdf"></i> Lihat PDF
                                        </a>
                                    <?php else: ?>
                                        <span style="color:#aaa; font-size:13px;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <button class="btn-edit" title="Edit"
                                            onclick="openEditModal(
                                                '<?php echo $row['idMateri']; ?>',
                                                '<?php echo htmlspecialchars($row['judul'], ENT_QUOTES); ?>',
                                                '<?php echo htmlspecialchars($row['deskripsi'] ?? '', ENT_QUOTES); ?>',
                                                '<?php echo htmlspecialchars($row['kategori'], ENT_QUOTES); ?>',
                                                '<?php echo $row['idMapel']; ?>',
                                                '<?php echo htmlspecialchars($row['isi'] ?? '', ENT_QUOTES); ?>'
                                            )">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button class="btn-delete" title="Hapus"
                                            onclick="openDeleteModal('<?php echo $row['idMateri']; ?>', '<?php echo htmlspecialchars($row['judul'], ENT_QUOTES); ?>')">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile;
                            else: ?>
                            <tr><td colspan="6" style="text-align:center; padding:30px;">Belum ada materi.</td></tr>
                            <?php endif; ?>
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
                <h3>Tambah Materi</h3>
                <button class="close-modal" onclick="closeModal('modalTambah')"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="proses_tambah_materi.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Judul Materi</label>
                    <input type="text" name="judul" placeholder="Contoh: Pengantar Aljabar" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <input type="text" name="deskripsi" placeholder="Deskripsi singkat materi">
                </div>
                <div class="form-group">
                    <label>Kategori</label>
                    <input type="text" name="kategori" placeholder="Contoh: Teori, Praktik" required>
                </div>
                <div class="form-group">
                    <label>Mata Pelajaran</label>
                    <select name="idMapel">
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        <?php while ($mp = mysqli_fetch_assoc($resultMapel)): ?>
                        <option value="<?php echo $mp['idMapel']; ?>"><?php echo htmlspecialchars($mp['namaMapel']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>File PDF <span style="color:#aaa; font-weight:400;">(maks. 30MB)</span></label>
                    <input type="file" name="filePdf" accept=".pdf" required>
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
                <h3>Edit Materi</h3>
                <button class="close-modal" onclick="closeModal('modalEdit')"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="proses_edit_materi.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="edit_id">
                <input type="hidden" name="isiLama" id="edit_isiLama">
                <div class="form-group">
                    <label>Judul Materi</label>
                    <input type="text" name="judul" id="edit_judul" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <input type="text" name="deskripsi" id="edit_deskripsi">
                </div>
                <div class="form-group">
                    <label>Kategori</label>
                    <input type="text" name="kategori" id="edit_kategori" required>
                </div>
                <div class="form-group">
                    <label>Mata Pelajaran</label>
                    <select name="idMapel" id="edit_idMapel">
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        <?php
                        mysqli_data_seek($resultMapel, 0);
                        while ($mp = mysqli_fetch_assoc($resultMapel)): ?>
                        <option value="<?php echo $mp['idMapel']; ?>"><?php echo htmlspecialchars($mp['namaMapel']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Ganti File PDF <span style="color:#aaa; font-weight:400;">(kosongkan jika tidak ingin mengganti)</span></label>
                    <input type="file" name="filePdf" accept=".pdf">
                    <p id="edit_namaFile" style="font-size:12px; color:#577883; margin-top:5px;"></p>
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
            <p>Apakah Anda yakin ingin menghapus materi <strong id="hapus_nama_text"></strong>? Data dan file PDF akan dihapus permanen.</p>
            <form action="proses_hapus_materi.php" method="POST">
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
    function openEditModal(id, judul, deskripsi, kategori, idMapel, isi) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_judul').value = judul;
        document.getElementById('edit_deskripsi').value = deskripsi;
        document.getElementById('edit_kategori').value = kategori;
        document.getElementById('edit_idMapel').value = idMapel;
        document.getElementById('edit_isiLama').value = isi;
        document.getElementById('edit_namaFile').textContent = isi ? 'File saat ini: ' + isi : '';
        openModal('modalEdit');
    }
    </script>
</body>
</html>
