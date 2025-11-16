<?php
session_start();
require '../../koneksi.php';

// --- 1. CEK LOGIN DAN ROLE ADMIN ---
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// --- 2. FITUR PENCARIAN ---
$keyword = isset($_GET['search']) ? trim($_GET['search']) : "";

// Query pencarian
if ($keyword != "") {
    $sql = "SELECT dp.id, dp.nip, dp.nama_lengkap, dp.no_telepon, dp.alamat, u.email 
            FROM data_pengajar dp
            LEFT JOIN users u ON dp.user_id = u.id
            WHERE dp.nip LIKE '%$keyword%'
               OR dp.nama_lengkap LIKE '%$keyword%'
               OR u.email LIKE '%$keyword%'
               OR dp.no_telepon LIKE '%$keyword%'
               OR dp.alamat LIKE '%$keyword%'
            ORDER BY dp.nama_lengkap ASC";
} else {
    $sql = "SELECT dp.id, dp.nip, dp.nama_lengkap, dp.no_telepon, dp.alamat, u.email 
            FROM data_pengajar dp
            LEFT JOIN users u ON dp.user_id = u.id
            ORDER BY dp.nama_lengkap ASC";
}

$result = $koneksi->query($sql);

$data_pengajar = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data_pengajar[] = $row;
    }
}
$koneksi->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Data Pengajar - Admin</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --warna-hijau: #00a86b;
            --warna-hijau-muda: #e6f7f0;
            --warna-latar: #f4f7f6;
            --warna-teks: #333333;
            --warna-teks-abu: #555;
            --lebar-sidebar: 280px;
        }

        body, html { margin: 0; padding: 0; font-family: 'Poppins', sans-serif; background-color: var(--warna-latar); box-sizing: border-box; }

        *, *:before, *:after { box-sizing: inherit; }

        /* --- CSS Sidebar & Header (copy dari data_santri.php) --- */
        .sidebar { position: fixed; top: 0; left: 0; height: 100%; width: var(--lebar-sidebar); background-color: var(--warna-hijau); color: white; z-index: 1000; transform: translateX(-100%); transition: transform 0.3s ease-out; display: flex; flex-direction: column; }
        .sidebar.active { transform: translateX(0); }
        .sidebar-header { display: flex; align-items: center; justify-content: space-between; padding: 20px 25px; border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
        .sidebar-header img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        .close-btn { font-size: 1.5rem; cursor: pointer; }
        .sidebar-nav { list-style: none; padding: 20px 0; margin: 0; flex-grow: 1; overflow-y: auto; }
        .sidebar-nav li a { display: flex; align-items: center; padding: 15px 25px; color: white; text-decoration: none; font-size: 1rem; font-weight: 500; transition: background-color 0.2s; }
        .sidebar-nav li a:hover { background-color: rgba(255, 255, 255, 0.1); }
        .sidebar-nav li.active > a { background-color: var(--warna-latar); color: var(--warna-hijau); border-left: 5px solid white; padding-left: 20px; }
        .sidebar-nav li a i.fa-fw { width: 30px; margin-right: 15px; }
        .sidebar-nav li.logout { margin-top: auto; border-top: 1px solid rgba(255, 255, 255, 0.1); }

        .dropdown-toggle { display: flex; justify-content: space-between; align-items: center; }
        .toggle-icon { font-size: 0.8rem; transition: transform 0.3s ease; }
        .submenu { list-style: none; padding-left: 0; margin: 0; background-color: rgba(0, 0, 0, 0.15); max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out; }
        .submenu.active { max-height: 500px; }
        .submenu li a { padding-left: 65px; font-size: 0.9rem; }
        .submenu li.active-sub > a { background-color: rgba(255, 255, 255, 0.2); font-weight: 600; }

        .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 999; opacity: 0; visibility: hidden; transition: opacity 0.3s ease-out, visibility 0s 0.3s linear; }
        .overlay.active { opacity: 1; visibility: visible; transition: opacity 0.3s ease-out; }

        .main-content { width: 100%; min-height: 100vh; }

        .header { display: flex; align-items: center; justify-content: space-between; padding: 15px 20px; background-color: var(--warna-hijau); color: white; }
        .header-left { display: flex; align-items: center; }
        .hamburger-btn { font-size: 1.5rem; background: none; border: none; color: white; cursor: pointer; margin-right: 15px; }
        .header-logo img { width: 35px; height: 35px; border-radius: 50%; object-fit: cover; margin-right: 10px; }
        .header-title { font-size: 0.9rem; font-weight: 500; line-height: 1.3; }
        .header-right .user-profile { display: flex; align-items: center; text-align: right; text-decoration: none; color: white; }
        .user-profile .user-info { display: flex; flex-direction: column; }
        .icon-wrapper { background-color: white; color: var(--warna-hijau); border-radius: 50%; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; margin-left: 10px; font-size: 1.1rem; }

        .dashboard-area { padding: 25px 20px; }
        .dashboard-area h1 { color: var(--warna-teks); font-size: 1.8rem; margin-bottom: 20px; }

        .card-table { background-color: white; border-radius: 15px; box-shadow: 0 6px 15px rgba(0, 0, 0, 0.07); overflow: hidden; }
        .card-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; padding: 20px; border-bottom: 1px solid #f0f0f0; }

        .btn-tambah { background-color: var(--warna-hijau); color: white; text-decoration: none; padding: 10px 15px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; }
        .btn-tambah:hover { background-color: #008a5a; }
        .btn-tambah i { margin-right: 5px; }

        .search-bar { position: relative; }
        .search-bar i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #aaa; }
        .search-bar input { padding: 10px 15px 10px 40px; border: 1px solid #ddd; border-radius: 8px; }

        .card-body { padding: 0; overflow-x: auto; }
        .data-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        .data-table th, .data-table td { padding: 15px; border-bottom: 1px solid #f0f0f0; color: var(--warna-teks-abu); white-space: nowrap; }
        .data-table th { background-color: #f9f9f9; color: var(--warna-teks); font-weight: 600; }
        .data-table tbody tr:hover { background-color: var(--warna-hijau-muda); }

        .btn-aksi { padding: 5px 10px; border-radius: 5px; font-size: 0.8rem; text-decoration: none; color: white; }
        .btn-edit { background-color: #0d6efd; }
        .btn-hapus { background-color: #dc3545; }

        .no-data { text-align: center; padding: 40px; font-style: italic; color: var(--warna-teks-abu); }
    </style>
</head>
<body>

<nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="../../img/logo1.png" alt="Logo">
        <i class="fas fa-arrow-left close-btn" id="close-btn"></i>
    </div>

    <ul class="sidebar-nav">
        <li><a href="../dashboard_admin.php"><i class="fas fa-tachometer-alt fa-fw"></i> Dashboard</a></li>

        <li class="nav-item dropdown active">
            <a href="#" class="dropdown-toggle active">
                <span><i class="fas fa-database fa-fw"></i> Master Data</span>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </a>
            <ul class="submenu active">
                <li><a href="data_santri.php">Santri</a></li>
                <li class="active-sub"><a href="data_pengajar.php">Guru</a></li>
                <li><a href="data_kelas.php">Kelas</a></li>
                <li><a href="#">Tahun Ajaran</a></li>
            </ul>
        </li>

        <li><a href="#"><i class="fas fa-chart-bar fa-fw"></i> Pengolahan Nilai</a></li>

        <li class="nav-item dropdown">
            <a href="#" class="dropdown-toggle">
                <span><i class="fas fa-file-alt fa-fw"></i> Laporan</span>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </a>
            <ul class="submenu">
                <li><a href="../laporan/laporan_guru.php">Laporan Daftar Guru</a></li>
                <li><a href="#">Laporan Daftar Santri</a></li>
                <li><a href="#">Laporan Daftar Nilai</a></li>
            </ul>
        </li>

        <li><a href="#"><i class="fas fa-lock fa-fw"></i> Ganti Password</a></li>

        <li class="logout"><a href="../../logout.php"><i class="fas fa-sign-out-alt fa-fw"></i> Logout</a></li>
    </ul>
</nav>

<div class="overlay" id="overlay"></div>

<div class="main-content">

<header class="header">
    <div class="header-left">
        <button class="hamburger-btn" id="hamburger-btn"><i class="fas fa-bars"></i></button>
        <div class="header-logo"><img src="../../img/logo1.png" alt="Logo"></div>
        <div class="header-title">Sistem Raport<br>Taman Pendidikan Al-Qur'an</div>
    </div>

    <div class="header-right">
        <a href="#" class="user-profile">
            <div class="user-info">
                <span><?= htmlspecialchars($_SESSION['email']); ?></span>
            </div>
            <div class="icon-wrapper">
                <i class="fas fa-user-shield"></i>
            </div>
        </a>
    </div>
</header>

<main class="dashboard-area">
    <h1>Data Pengajar</h1>

    <div class="card-table">
        <div class="card-header">
            <a href="tambah_pengajar.php" class="btn-tambah">
                <i class="fas fa-plus"></i> Tambah Pengajar
            </a>

            <div class="search-bar">
                <form method="GET" action="data_pengajar.php">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Cari pengajar..." value="<?= htmlspecialchars($keyword); ?>">
                </form>
            </div>
        </div>

        <div class="card-body">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>NIP</th>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th>No. Telepon</th>
                        <th>Alamat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (empty($data_pengajar)): ?>
                        <tr>
                            <td colspan="7" class="no-data">Belum ada data pengajar.</td>
                        </tr>
                    <?php else: $no = 1; foreach ($data_pengajar as $p): ?>
                        <tr>
                            <td><?= $no++; ?>.</td>
                            <td><?= htmlspecialchars($p['nip'] ?: '-'); ?></td>
                            <td><?= htmlspecialchars($p['nama_lengkap']); ?></td>
                            <td><?= htmlspecialchars($p['email']); ?></td>
                            <td><?= htmlspecialchars($p['no_telepon'] ?: '-'); ?></td>
                            <td><?= htmlspecialchars($p['alamat'] ?: '-'); ?></td>
                            <td>
                                <a href="edit_pengajar.php?id=<?= $p['id']; ?>" class="btn-aksi btn-edit">
                                    <i class="fas fa-pencil-alt"></i> Edit
                                </a>
                                <a href="hapus_pengajar.php?id=<?= $p['id']; ?>" class="btn-aksi btn-hapus"
                                   onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                   <i class="fas fa-trash-alt"></i> Hapus
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</div>

<script>
const hamburgerBtn = document.getElementById('hamburger-btn');
const closeBtn = document.getElementById('close-btn');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');

function openSidebar() { sidebar.classList.add('active'); overlay.classList.add('active'); }
function closeSidebar() { sidebar.classList.remove('active'); overlay.classList.remove('active'); }

hamburgerBtn.addEventListener('click', openSidebar);
closeBtn.addEventListener('click', closeSidebar);
overlay.addEventListener('click', closeSidebar);

document.querySelectorAll('.dropdown-toggle').forEach(function(toggle) {
    toggle.addEventListener('click', function(e) {
        e.preventDefault();
        let submenu = this.nextElementSibling;

        let isAlreadyActive = this.classList.contains('active');

        document.querySelectorAll('.submenu.active').forEach(s => s.classList.remove('active'));
        document.querySelectorAll('.dropdown-toggle.active').forEach(t => t.classList.remove('active'));

        if (!isAlreadyActive) {
            this.classList.add('active');
            submenu.classList.add('active');
        }
    });
});
</script>

</body>
</html>
