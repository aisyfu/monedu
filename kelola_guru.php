<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['nama'])) { $_SESSION['nama'] = ""; }
if (!isset($_SESSION['role'])) { $_SESSION['role'] = "ADMIN"; }

$query = "SELECT u.idUser as id, u.nama, u.username, u.email, r.namaRole as role, g.nip 
          FROM user u 
          JOIN guru g ON u.idUser = g.idUser 
          JOIN role r ON u.idRole = r.idRole
          ORDER BY u.idUser DESC";
$result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Guru - MonEdu</title>
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

        .main-content { flex-grow: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .topbar {
            background-color: #ffffff; height: 70px; display: flex; justify-content: flex-end;
            align-items: center; padding: 0 40px; box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }
        .profile-info { display: flex; align-items: center; gap: 12px; font-weight: 600; font-size: 14px; color: #333; text-align: right; }
        .profile-info span { color: #888; font-weight: 400; font-size: 12px; }
        .profile-info i { font-size: 38px; color: #577883; }

        .container { padding: 40px; }
        .page-title { font-size: 20px; font-weight: 700; color: #333; margin-bottom: 25px; text-transform: uppercase; }

        .data-card { background-color: #ffffff; border-radius: 20px; padding: 30px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02); }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .card-header h3 { font-size: 18px; font-weight: 600; color: #333; }
        .header-actions { display: flex; align-items: center; gap: 15px; }
        
        .search-box { position: relative; display: flex; align-items: center; }
        .search-box input { background-color: #f1f3f5; border: 1px solid transparent; padding: 10px 16px 10px 40px; border-radius: 10px; font-size: 14px; outline: none; width: 220px; }
        .search-box i { position: absolute; left: 14px; color: #b0bac3; font-size: 14px; }
        
        .btn-tambah { background-color: #577883; color: white; padding: 10px 20px; border-radius: 10px; font-size: 14px; font-weight: 500; border: none; cursor: pointer; display: flex; align-items: center; gap: 8px; }
        
        .custom-table { width: 100%; border-collapse: collapse; text-align: left; }
        .custom-table th { background-color: #eef2f4; color: #577883; font-weight: 600; font-size: 14px; padding: 16px 20px; }
        .custom-table td { padding: 20px; font-size: 14px; color: #4f4f4f; border-bottom: 1px solid #edf1f4; }
        .role-text { font-weight: 700; font-size: 13px; color: #333; }
        .action-btns { display: flex; gap: 16px; }
        .action-btns button { background: none; border: none; font-size: 16px; cursor: pointer; transition: transform 0.2s; }
        .action-btns button:hover { transform: scale(1.15); }
        .btn-edit { color: #577883; }
        .btn-delete { color: #e74c3c; }

        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.5); display: flex; justify-content: center; align-items: center;
            opacity: 0; visibility: hidden; transition: all 0.3s ease; z-index: 1000;
        }
        .modal-overlay.active { opacity: 1; visibility: visible; }
        
        .modal-box {
            background: #fff; width: 450px; border-radius: 16px; padding: 30px;
            transform: translateY(-20px); transition: all 0.3s ease;
        }
        .modal-overlay.active .modal-box { transform: translateY(0); }
        
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .modal-header h3 { font-size: 18px; color: #333; font-weight: 600; }
        .close-modal { background: none; border: none; font-size: 20px; cursor: pointer; color: #888; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: #333; margin-bottom: 6px; }
        .form-group input, .form-group select {
            width: 100%; padding: 12px 15px; border-radius: 8px; border: none;
            background-color: #f4f6f8; font-size: 14px; outline: none;
        }
        .form-group input:focus { border: 1px solid #577883; }

        .modal-footer { display: flex; justify-content: flex-end; gap: 10px; margin-top: 25px; }
        .btn-batal { padding: 10px 20px; border-radius: 8px; border: 1px solid #ccc; background: white; color: #555; cursor: pointer; font-weight: 500; font-size: 14px; }
        .btn-simpan { padding: 10px 20px; border-radius: 8px; border: none; background: #577883; color: white; cursor: pointer; font-weight: 500; font-size: 14px; }
        
        .modal-box.delete-box { text-align: center; width: 400px; padding: 40px 30px; }
        .delete-icon { font-size: 50px; color: #e74c3c; margin-bottom: 15px; }
        .delete-box p { font-size: 14px; color: #555; margin-bottom: 25px; line-height: 1.6; }
        .btn-hapus { background: #e74c3c; color: white; padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; font-size: 14px; }
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
                <li><a href="dashboard_admin.php"><i class="fa-solid fa-gauge"></i> <span class="text-link">Dashboard</span></a></li>
                <li class="active"><a href="kelola_guru.php"><i class="fa-solid fa-user-tie"></i> <span class="text-link">Kelola Guru</span></a></li>
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

        <div class="container">
            <h2 class="page-title">Kelola Guru</h2>

            <div class="data-card">
                <div class="card-header">
                    <h3>Daftar Guru</h3>
                    <div class="header-actions">
                        <div class="search-box">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" placeholder="Search...">
                        </div>
                        <button class="btn-tambah" onclick="openModal('modalTambah')">
                            <i class="fa-solid fa-plus"></i> Tambah Guru
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>NIP</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Akses</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            if ($result && mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $nip = isset($row['nip']) ? $row['nip'] : '-';
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                        <td><?php echo htmlspecialchars($row['nip']); ?></td>
                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td class="role-text"><?php echo strtoupper($row['role']); ?></td>
                                        <td>
                                            <div class="action-btns">
                                                <button class="btn-edit" title="Edit" onclick="openEditModal('<?php echo $row['id']; ?>', '<?php echo htmlspecialchars($row['nama'], ENT_QUOTES); ?>', '<?php echo $nip; ?>', '<?php echo $row['username']; ?>', '<?php echo $row['email']; ?>')">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                
                                                <button class="btn-delete" title="Hapus" onclick="openDeleteModal('<?php echo $row['id']; ?>', '<?php echo htmlspecialchars($row['nama'], ENT_QUOTES); ?>')">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo "<tr><td colspan='7' style='text-align: center; padding: 30px;'>Belum ada data guru.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modalTambah">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Tambah Guru</h3>
                <button class="close-modal" onclick="closeModal('modalTambah')"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="proses_tambah.php" method="POST">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" placeholder="Masukkan nama lengkap" required>
                </div>
                <div class="form-group">
                    <label>NIP</label>
                    <input type="text" name="nip" placeholder="Masukkan NIP" required>
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
                <input type="hidden" name="role" value="GURU">
                
                <div class="modal-footer">
                    <button type="button" class="btn-batal" onclick="closeModal('modalTambah')">Batal</button>
                    <button type="submit" class="btn-simpan">Tambah User</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="modalEdit">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Edit User</h3>
                <button class="close-modal" onclick="closeModal('modalEdit')"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="proses_edit.php" method="POST">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" id="edit_nama" required>
                </div>
                <div class="form-group">
                    <label>NIP</label>
                    <input type="text" name="nip" id="edit_nip" required>
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

    <div class="modal-overlay" id="modalHapus">
        <div class="modal-box delete-box">
            <i class="fa-solid fa-triangle-exclamation delete-icon"></i>
            <h3 style="margin-bottom: 10px;">Konfirmasi Hapus</h3>
            <p>Apakah Anda yakin ingin menghapus user <strong id="hapus_nama_text"></strong>? Semua data yang terkait dengan user ini akan dihapus secara permanen.</p>
            
            <form action="proses_hapus.php" method="POST">
                <input type="hidden" name="id" id="hapus_id">
                <div class="modal-footer" style="justify-content: center;">
                    <button type="button" class="btn-batal" onclick="closeModal('modalHapus')">Batal</button>
                    <button type="submit" class="btn-hapus">Ya, Hapus</button>
                </div>
            </form>
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

        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
        }
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
            let form = document.querySelector(`#${modalId} form`);
            if (form) { form.reset(); }
        }

        function openEditModal(id, nama, nip, username, email) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_nip').value = nip;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_email').value = email;
            openModal('modalEdit');
        }

        function openDeleteModal(id, nama) {
            document.getElementById('hapus_id').value = id;
            document.getElementById('hapus_nama_text').innerText = nama;
            openModal('modalHapus');
        }
    </script>
</body>
</html>