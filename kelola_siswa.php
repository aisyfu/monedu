<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['nama'])) { $_SESSION['nama'] = ""; }
if (!isset($_SESSION['role'])) { $_SESSION['role'] = "ADMIN"; }

$query = "SELECT u.idUser as id, u.nama, u.username, u.email, s.nis, s.kelas
          FROM user u
          JOIN siswa s ON u.idUser = s.idUser
          JOIN role r ON u.idRole = r.idRole
          ORDER BY u.idUser DESC";
$result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Siswa - MonEdu</title>
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
                <li><a href="dashboard_admin.php"><i class="fa-solid fa-gauge"></i> <span class="text-link">Dashboard</span></a></li>
                <li><a href="kelola_guru.php"><i class="fa-solid fa-user-tie"></i> <span class="text-link">Kelola Guru</span></a></li>
                <li class="active"><a href="kelola_siswa.php"><i class="fa-solid fa-users"></i> <span class="text-link">Kelola Siswa</span></a></li>
                <li><a href="kelola_mapel.php"><i class="fa-solid fa-book-open"></i> <span class="text-link">Kelola Mata Pelajaran</span></a></li>
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
            <h2 class="page-title">Kelola Siswa</h2>

            <div class="data-card">
                <div class="card-header">
                    <h3>Daftar Siswa</h3>
                    <div class="header-actions">
                        <div class="search-box">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" placeholder="Search...">
                        </div>
                        <button class="btn-tambah" onclick="openModal('modalTambah')">
                            <i class="fa-solid fa-plus"></i> Tambah Siswa
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>NIS</th>
                                <th>Kelas</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            if ($result && mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) { ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nis']); ?></td>
                                    <td><?php echo htmlspecialchars($row['kelas']); ?></td>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td>
                                        <div class="action-btns">
                                            <button class="btn-edit" title="Edit"
                                                onclick="openEditModal(
                                                    '<?php echo $row['id']; ?>',
                                                    '<?php echo htmlspecialchars($row['nama'], ENT_QUOTES); ?>',
                                                    '<?php echo htmlspecialchars($row['nis'], ENT_QUOTES); ?>',
                                                    '<?php echo htmlspecialchars($row['kelas'], ENT_QUOTES); ?>',
                                                    '<?php echo htmlspecialchars($row['username'], ENT_QUOTES); ?>',
                                                    '<?php echo htmlspecialchars($row['email'], ENT_QUOTES); ?>'
                                                )">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <button class="btn-delete" title="Hapus"
                                                onclick="openDeleteModal('<?php echo $row['id']; ?>', '<?php echo htmlspecialchars($row['nama'], ENT_QUOTES); ?>')">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php }
                            } else {
                                echo "<tr><td colspan='7' style='text-align: center; padding: 30px;'>Belum ada data siswa.</td></tr>";
                            } ?>
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
                <h3>Tambah Siswa</h3>
                <button class="close-modal" onclick="closeModal('modalTambah')"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="proses_tambah_siswa.php" method="POST">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" placeholder="Masukkan nama lengkap" required>
                </div>
                <div class="form-group">
                    <label>NIS</label>
                    <input type="text" name="nis" placeholder="Masukkan NIS" required>
                </div>
                <div class="form-group">
                    <label>Kelas</label>
                    <input type="text" name="kelas" placeholder="Contoh: X-A" required>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" placeholder="Masukkan username" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="Masukkan email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Masukkan password" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-batal" onclick="closeModal('modalTambah')">Batal</button>
                    <button type="submit" class="btn-simpan">Tambah Siswa</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal-overlay" id="modalEdit">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Edit Siswa</h3>
                <button class="close-modal" onclick="closeModal('modalEdit')"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="proses_edit_siswa.php" method="POST">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" id="edit_nama" required>
                </div>
                <div class="form-group">
                    <label>NIS</label>
                    <input type="text" name="nis" id="edit_nis" required>
                </div>
                <div class="form-group">
                    <label>Kelas</label>
                    <input type="text" name="kelas" id="edit_kelas" required>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" id="edit_username" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="edit_email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah">
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
            <h3 style="margin-bottom: 10px;">Konfirmasi Hapus</h3>
            <p>Apakah Anda yakin ingin menghapus siswa <strong id="hapus_nama_text"></strong>? Data akan dihapus secara permanen.</p>
            <form action="proses_hapus_siswa.php" method="POST">
                <input type="hidden" name="id" id="hapus_id">
                <div class="modal-footer" style="justify-content: center;">
                    <button type="button" class="btn-batal" onclick="closeModal('modalHapus')">Batal</button>
                    <button type="submit" class="btn-hapus">Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>

</body>
<script>
    function openEditModal(id, nama, nis, kelas, username, email) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_nama').value = nama;
        document.getElementById('edit_nis').value = nis;
        document.getElementById('edit_kelas').value = kelas;
        document.getElementById('edit_username').value = username;
        document.getElementById('edit_email').value = email;
        openModal('modalEdit');
    }
</script>
</html>