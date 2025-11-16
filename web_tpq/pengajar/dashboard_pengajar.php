<?php
session_start();
require '../koneksi.php';

// --- 1. KEAMANAN: Cek apakah sudah login dan rolenya pengajar ---
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'pengajar') {
    // Jika bukan pengajar, lempar kembali ke halaman login
    header("Location: login.php");
    exit();
}

// --- 2. AMBIL DATA DINAMIS UNTUK KARTU & TABEL ---
$user_id_login = $_SESSION['user_id'];
$nama_pengajar = "Pengajar";
$nip_pengajar = "-";

// Query untuk mengambil data profil pengajar
$sql_profil = "SELECT 
                    dp.nama_lengkap AS nama_pengajar,
                    dp.nip
                FROM data_pengajar dp
                JOIN users u ON dp.user_id = u.id
                WHERE u.id = ?";
                
$stmt_profil = $koneksi->prepare($sql_profil);
$stmt_profil->bind_param("i", $user_id_login);
$stmt_profil->execute();
$result_profil = $stmt_profil->get_result();

if ($result_profil->num_rows > 0) {
    $data_profil = $result_profil->fetch_assoc();
    $nama_pengajar = $data_profil['nama_pengajar'] ?: $nama_pengajar;
    $nip_pengajar = $data_profil['nip'] ?: $nip_pengajar;
}
$stmt_profil->close();

// Query untuk mengambil daftar kelas/mata pelajaran yang diampu pengajar ini
// Anda perlu memutuskan apakah pengajar mengajar 'materi' atau 'kelas'.
// Berdasarkan gambar, lebih ke 'kelas', dan 'Wali Kelas' nya adalah dia sendiri.
// Jadi kita akan menampilkan kelas yang dia wali-i.

$data_kelas_diampu = [];
$sql_kelas_diampu = "SELECT 
                        k.nama_kelas,
                        k.tahun_ajaran
                    FROM kelas k
                    JOIN data_pengajar dp ON k.id_pengajar = dp.id
                    WHERE dp.user_id = ?";

$stmt_kelas_diampu = $koneksi->prepare($sql_kelas_diampu);
$stmt_kelas_diampu->bind_param("i", $user_id_login);
$stmt_kelas_diampu->execute();
$result_kelas_diampu = $stmt_kelas_diampu->get_result();

if ($result_kelas_diampu->num_rows > 0) {
    while ($row = $result_kelas_diampu->fetch_assoc()) {
        $data_kelas_diampu[] = $row;
    }
}
$stmt_kelas_diampu->close();
$koneksi->close(); // Tutup koneksi setelah semua query selesai
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pengajar - Sistem Raport TPQ</title>
    
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

        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background-color: var(--warna-latar);
            box-sizing: border-box;
        }
        *, *:before, *:after {
            box-sizing: inherit;
        }

        /* --- Sidebar & Overlay (Ambil dari Dashboard Santri) --- */
        .sidebar { position: fixed; top: 0; left: 0; height: 100%; width: var(--lebar-sidebar); background-color: var(--warna-hijau); color: white; z-index: 1000; transform: translateX(-100%); transition: transform 0.3s ease-out; display: flex; flex-direction: column; }
        .sidebar.active { transform: translateX(0); }
        .sidebar-header { display: flex; align-items: center; justify-content: space-between; padding: 20px 25px; border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
        .sidebar-header img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        .sidebar-header .close-btn { font-size: 1.5rem; cursor: pointer; }
        .sidebar-nav { list-style: none; padding: 20px 0; margin: 0; flex-grow: 1; }
        .sidebar-nav li a { display: flex; align-items: center; padding: 15px 25px; color: white; text-decoration: none; font-size: 1rem; font-weight: 500; transition: background-color 0.2s; }
        .sidebar-nav li a:hover { background-color: rgba(255, 255, 255, 0.1); }
        .sidebar-nav li.active a { background-color: var(--warna-latar); color: var(--warna-hijau); border-left: 5px solid white; padding-left: 20px; }
        .sidebar-nav li.active i { color: var(--warna-hijau); }
        .sidebar-nav li a i { width: 30px; font-size: 1.2rem; margin-right: 15px; }
        .sidebar-nav li.logout { margin-top: auto; border-top: 1px solid rgba(255, 255, 255, 0.1); }
        .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 999; opacity: 0; visibility: hidden; transition: opacity 0.3s ease-out, visibility 0s 0.3s linear; }
        .overlay.active { opacity: 1; visibility: visible; transition: opacity 0.3s ease-out; }

        /* --- Header (Ambil dari Dashboard Santri) --- */
        .main-content { width: 100%; min-height: 100vh; }
        .header { display: flex; align-items: center; justify-content: space-between; padding: 15px 20px; background-color: var(--warna-hijau); color: white; }
        .header-left { display: flex; align-items: center; }
        .hamburger-btn { font-size: 1.5rem; background: none; border: none; color: white; cursor: pointer; margin-right: 15px; }
        .header-logo img { width: 35px; height: 35px; border-radius: 50%; object-fit: cover; margin-right: 10px; }
        .header-title { font-size: 0.9rem; font-weight: 500; line-height: 1.3; }
        .header-right .user-profile { display: flex; align-items: center; text-align: right; text-decoration: none; color: white; }
        .user-profile .user-info { display: flex; flex-direction: column; }
        .user-profile span { font-size: 0.8rem; font-weight: 500; }
        .user-profile .icon-wrapper { background-color: white; color: var(--warna-hijau); border-radius: 50%; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; margin-left: 10px; font-size: 1.1rem; }

        /* --- Area Dashboard --- */
        .dashboard-area {
            padding: 25px 20px;
        }
        .dashboard-area h1 {
            color: var(--warna-teks);
            font-size: 1.8rem;
            margin-top: 0;
            margin-bottom: 20px;
        }

        /* --- Grid Kartu (Ambil dari Dashboard Santri) --- */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr; /* Default 1 kolom untuk tablet/mobile */
            gap: 20px;
        }
        
        /* Tambahkan breakpoint untuk tampilan desktop/tablet */
        @media (min-width: 768px) {
            .dashboard-grid {
                grid-template-columns: repeat(2, 1fr); /* 2 kolom untuk layar lebih lebar */
            }
            .table-card {
                grid-column: span 2; /* Tabel mengambil 2 kolom */
            }
        }
        @media (min-width: 1024px) {
            .dashboard-grid {
                grid-template-columns: repeat(3, 1fr); /* 3 kolom untuk desktop */
            }
            .table-card {
                grid-column: span 3; /* Tabel mengambil 3 kolom */
            }
        }


        .card {
            background-color: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.07);
        }

        .card-welcome {
            grid-column: 1 / -1; /* Ambil semua kolom yang tersedia */
            background-color: var(--warna-hijau-muda);
            color: var(--warna-hijau);
            font-weight: 500;
        }
        .card-welcome h2 {
            margin: 0 0 5px 0;
            font-size: 1.2rem;
        }
        .card-welcome p {
            margin: 0;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        /* --- CSS Tambahan untuk Tabel Mata Pelajaran --- */
        .table-card h3 {
            color: var(--warna-hijau);
            font-size: 1.1rem;
            margin-top: 0;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            text-align: center;
        }
        .table-card table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 0.9rem;
        }
        .table-card th, .table-card td {
            padding: 12px 10px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }
        .table-card th {
            background-color: var(--warna-hijau-muda);
            color: var(--warna-hijau);
            font-weight: 600;
        }
        .table-card td {
            color: var(--warna-teks-abu);
        }
        .table-card tbody tr:last-child td {
            border-bottom: none;
        }
        .table-card tbody tr:hover {
            background-color: #f9f9f9;
        }
        .table-card .no-data {
            text-align: center;
            padding: 20px;
            color: var(--warna-teks-abu);
            font-style: italic;
        }
    </style>
</head>
<body>

    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="../img/logo1.png" alt="Logo">
            <i class="fas fa-arrow-left close-btn" id="close-btn"></i>
        </div>
        <ul class="sidebar-nav">
            <li class="active"><a href="#"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="#"><i class="fas fa-user"></i> Biodata</a></li>
            <li><a href="#"><i class="fas fa-edit"></i> Pengolahan Nilai</a></li> <li><a href="#"><i class="fas fa-users-cog"></i> Wali Kelas</a></li> <li><a href="#"><i class="fas fa-lock"></i> Ganti Password</a></li>
            <li class="logout"><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>

    <div class="overlay" id="overlay"></div>

    <div class="main-content">

        <header class="header">
            <div class="header-left">
                <button class="hamburger-btn" id="hamburger-btn">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="header-logo">
                    <img src="../img/logo1.png" alt="Logo">
                </div>
                <div class="header-title">
                    Sistem Raport<br>Taman Pendidikan Al-Qur'an
                </div>
            </div>
            <div class="header-right">
                <a href="#" class="user-profile">
                    <div class="user-info">
                        <span><?php echo htmlspecialchars($nama_pengajar); ?></span>
                    </div>
                    <div class="icon-wrapper">
                        <i class="fas fa-user"></i>
                    </div>
                </a>
            </div>
        </header>

        <main class="dashboard-area">
            <h1>Dashboard</h1>

            <div class="dashboard-grid">

                <div class="card card-welcome">
                    <h2>Selamat Datang</h2>
                    <p>Selamat datang <?php echo htmlspecialchars($nama_pengajar); ?> di Sistem Raport Taman Pendidikan Al-Qur'an Daarul Hikmah</p>
                </div>
                
                <?php if (!empty($nip_pengajar) && $nip_pengajar != "-"): ?>
                <div class="card card-small">
                    <h3><i class="fas fa-id-card"></i> NIP</h3>
                    <p><?php echo htmlspecialchars($nip_pengajar); ?></p>
                </div>
                <?php endif; ?>

                <div class="card table-card">
                    <h3>Kelas yang Diampu</h3>
                    <?php if (!empty($data_kelas_diampu)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Nama Kelas</th>
                                    <th>Tahun Ajaran</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($data_kelas_diampu as $kelas): ?>
                                <tr>
                                    <td><?php echo $no++; ?>.</td>
                                    <td><?php echo htmlspecialchars($kelas['nama_kelas']); ?></td>
                                    <td><?php echo htmlspecialchars($kelas['tahun_ajaran']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="no-data">Belum ada kelas yang diampu.</p>
                    <?php endif; ?>
                </div>

            </div>
        </main>
    </div>

    <script>
        const hamburgerBtn = document.getElementById('hamburger-btn');
        const closeBtn = document.getElementById('close-btn');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        function openSidebar() {
            sidebar.classList.add('active');
            overlay.classList.add('active');
        }
        function closeSidebar() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        }
        hamburgerBtn.addEventListener('click', openSidebar);
        closeBtn.addEventListener('click', closeSidebar);
        overlay.addEventListener('click', closeSidebar);
    </script>

</body>
</html>