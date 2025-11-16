<?php
session_start();
require '../koneksi.php';

// --- 1. KEAMANAN: Cek apakah sudah login dan rolenya santri ---
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'santri') {
    header("Location: login.php");
    exit();
}

// --- 2. AMBIL SEMUA DATA SANTRI YANG LOGIN ---
$user_id_login = $_SESSION['user_id'];
$santri_data = [];

// Query ini mengambil semua data dari data_santri, dan email dari tabel users
$sql = "SELECT s.*, u.email
        FROM data_santri s
        JOIN users u ON s.user_id = u.id
        WHERE s.user_id = ?
        LIMIT 1";
        
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $user_id_login);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $santri_data = $result->fetch_assoc();
}
$stmt->close();
$koneksi->close();

// Helper function untuk menampilkan data atau '-' jika kosong
function tampilkanData($data) {
    return htmlspecialchars(!empty($data) ? $data : '-');
}

// Format tanggal lahir dari YYYY-MM-DD menjadi DD MMMM YYYY (misal: 17 Agustus 1945)
$tanggal_lahir_formatted = '-';
if (!empty($santri_data['tanggal_lahir'])) {
    // Set locale ke Indonesia
    setlocale(LC_TIME, 'id_ID.UTF-8', 'Indonesian');
    $timestamp = strtotime($santri_data['tanggal_lahir']);
    $tanggal_lahir_formatted = strftime('%d %B %Y', $timestamp);
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biodata Santri</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --warna-hijau: #00a86b;
            --warna-hijau-muda: #e6f7f0;
            --warna-latar: #f4f7f6;
            --warna-teks: #333333;
            --warna-teks-abu: #555;
        }

        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background-color: var(--warna-latar);
        }

        /* Kontainer utama (simulasi HP) */
        .biodata-container {
            width: 100%;
            max-width: 420px; /* Lebar umum HP */
            margin: 0 auto; /* Tengah di layar besar */
            background-color: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Bagian Header Hijau */
        .profile-header {
            background-color: var(--warna-hijau);
            color: white;
            padding: 30px 20px 40px 20px;
            text-align: center;
            position: relative;
        }

        .back-btn, .edit-btn {
            position: absolute;
            top: 20px;
            text-decoration: none;
            color: white;
            font-size: 1.2rem;
            padding: 10px;
        }
        .back-btn { left: 15px; }
        .edit-btn { 
            right: 15px; 
            font-size: 0.9rem;
            font-weight: 600;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 8px 15px;
        }
        .edit-btn i { margin-right: 5px; }

        .profile-pic {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.15);
            margin: 20px auto 15px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        .profile-header h2 {
            margin: 0;
            font-size: 1.4rem;
            font-weight: 600;
        }

        .nis-badge {
            display: inline-block;
            background-color: white;
            color: var(--warna-hijau);
            padding: 8px 25px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.9rem;
            margin-top: 10px;
        }

        /* Bagian Konten Putih */
        .profile-content {
            background-color: white;
            padding: 25px 20px;
            flex-grow: 1;
            /* Trik untuk membuat lengkungan di atas */
            border-radius: 30px 30px 0 0;
            margin-top: -25px; /* Tarik ke atas menimpa header hijau */
            z-index: 10;
        }

        .card {
            border: 1px solid #eee;
            border-radius: 15px;
            margin-bottom: 25px;
            overflow: hidden; /* Agar h3 pas di lengkungan */
        }
        
        .card h3 {
            margin: 0;
            padding: 12px 20px;
            background-color: var(--warna-hijau-muda);
            color: var(--warna-hijau);
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .card-body {
            padding: 15px 20px;
        }

        .info-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .info-list li {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px dashed #f0f0f0;
            font-size: 0.9rem;
            line-height: 1.5;
        }
        .info-list li:last-child {
            border-bottom: none;
        }

        .info-list li span {
            color: var(--warna-teks-abu);
            flex-basis: 40%; /* Lebar label */
            flex-shrink: 0; /* Jangan sampai label mengecil */
        }
        
        .info-list li strong {
            color: var(--warna-teks);
            text-align: right;
            flex-basis: 60%; /* Lebar nilai */
        }
        
        /* CSS untuk card Data Orangtua (sedikit berbeda) */
        .card-body.orangtua {
            background-color: var(--warna-hijau-muda);
            padding: 0;
        }
        .card-body.orangtua .info-list {
            padding: 15px 20px;
        }
        .card-body.orangtua .info-list li {
            border-bottom: 1px dashed rgba(0, 168, 107, 0.2);
        }
        .card-body.orangtua .info-list li span,
        .card-body.orangtua .info-list li strong {
            color: var(--warna-teks); /* Warna teks lebih gelap */
        }
        /* --- Tata Letak Responsif (Desktop) --- */

        /* Ini adalah 'breakpoint'. CSS di dalamnya HANYA aktif
           jika lebar layar 960px atau lebih (desktop) */
        @media (min-width: 960px) {

            .biodata-container {
                /* Lebarkan kontainer utama di desktop */
                max-width: 960px; 

                /* Beri jarak atas-bawah & radius */
                margin-top: 40px;
                margin-bottom: 40px;
                border-radius: 15px;
            }

            /* Sesuaikan radius header & konten */
            .profile-header {
                border-radius: 15px 15px 0 0;
            }
            .profile-content {
                border-radius: 0 0 15px 15px;
            }

            /* Ini adalah inti perubahannya:
               Ubah grid menjadi 2 kolom berdampingan */
            .card-grid {
                display: grid;
                grid-template-columns: 1fr 1fr; /* 2 kolom sama lebar */
                gap: 25px; /* Jarak antar kartu */
            }

            /* Hentikan kartu agar tidak menumpuk di desktop */
            .card {
                margin-bottom: 0;
            }
        }

    </style>
</head>
<body>

    <div class="biodata-container">
        
        <header class="profile-header">
            <a href="dashboard_santri.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            
            <a href="edit_biodata.php" class="edit-btn">
                <i class="fas fa-pencil-alt"></i> Edit
            </a>

            <div class="profile-pic">
                <i class="fas fa-user"></i>
            </div>
            
            <h2><?php echo tampilkanData($santri_data['nama_lengkap']); ?></h2>
            
            <div class="nis-badge">
                ID = <?php echo tampilkanData($santri_data['nis']); ?>
            </div>
        </header>

        <main class="profile-content">

            <div class="card-grid"> 

                <div class="card">
                    <h3>DATA DIRI</h3>
                <div class="card-body">
                    <ul class="info-list">
                        <li><span>NIK</span> <strong><?php echo tampilkanData($santri_data['nik']); ?></strong></li>
                        <li><span>No. KK</span> <strong><?php echo tampilkanData($santri_data['no_kk']); ?></strong></li>
                        <li><span>Nama Lengkap</span> <strong><?php echo tampilkanData($santri_data['nama_lengkap']); ?></strong></li>
                        <li><span>Jenis Kelamin</span> <strong><?php echo tampilkanData($santri_data['jenis_kelamin']); ?></strong></li>
                        <li><span>Tempat Lahir</span> <strong><?php echo tampilkanData($santri_data['tempat_lahir']); ?></strong></li>
                        <li><span>Tanggal Lahir</span> <strong><?php echo $tanggal_lahir_formatted; ?></strong></li>
                        <li><span>Alamat</span> <strong><?php echo tampilkanData($santri_data['alamat']); ?></strong></li>
                        <li><span>No. Hp</span> <strong><?php echo tampilkanData($santri_data['no_hp']); ?></strong></li>
                        <li><span>Email</span> <strong><?php echo tampilkanData($santri_data['email']); ?></strong></li>
                    </ul>
                </div>
            </div>

            <div class="card">
                    <h3>DATA ORANGTUA</h3>
                <div class="card-body orangtua">
                    <ul class="info-list">
                        <li><span>Nama Ayah</span> <strong><?php echo tampilkanData($santri_data['nama_ayah']); ?></strong></li>
                        <li><span>Pekerjaan Ayah</span> <strong><?php echo tampilkanData($santri_data['pekerjaan_ayah']); ?></strong></li>
                        <li><span>Nama Ibu</span> <strong><?php echo tampilkanData($santri_data['nama_ibu']); ?></strong></li>
                        <li><span>Pekerjaan Ibu</span> <strong><?php echo tampilkanData($santri_data['pekerjaan_ibu']); ?></strong></li>
                        <li><span>Total Gaji Per Bulan</span> <strong><?php echo tampilkanData($santri_data['gaji_per_bulan']); ?></strong></li>
                    </ul>
                </div>
            </div>
            
        </main>

    </div>

</body>
</html>