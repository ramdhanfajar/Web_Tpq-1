<?php
session_start();
require '../../koneksi.php';

// CEK LOGIN ADMIN
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// PROSES SIMPAN DATA
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user_id     = $_POST['user_id'];
    $nip         = $_POST['nip'];
    $nama        = $_POST['nama_lengkap'];
    $telepon     = $_POST['no_telepon'];
    $alamat      = $_POST['alamat'];

    $query = "INSERT INTO data_pengajar (user_id, nip, nama_lengkap, no_telepon, alamat)
              VALUES ('$user_id', '$nip', '$nama', '$telepon', '$alamat')";

    if ($koneksi->query($query)) {
        header("Location: data_pengajar.php?status=added");
        exit();
    } else {
        echo "Gagal menyimpan data: " . $koneksi->error;
    }
}

// AMBIL USER UNTUK DROPDOWN
$list_user = $koneksi->query("SELECT id, email FROM users ORDER BY email ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Tambah Pengajar</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet>

<style>
<?php include 'style_santri.css'; ?>
</style>

</head>
<body>

<div class="form-container">

    <a href="data_pengajar.php" class="btn-kembali"><i class="fa fa-arrow-left"></i> Kembali</a>

    <h2>Tambah Data Pengajar</h2>

    <form method="POST">

        <label>Pilih User Login (Email)</label>
        <select name="user_id" required>
            <option value="">-- Pilih User --</option>
            <?php while ($u = $list_user->fetch_assoc()): ?>
                <option value="<?= $u['id']; ?>"><?= htmlspecialchars($u['email']); ?></option>
            <?php endwhile; ?>
        </select>

        <label>NIP</label>
        <input type="text" name="nip">

        <label>Nama Lengkap</label>
        <input type="text" name="nama_lengkap" required>

        <label>No. Telepon</label>
        <input type="text" name="no_telepon">

        <label>Alamat</label>
        <textarea name="alamat"></textarea>

        <button type="submit" class="btn-submit">Simpan</button>

    </form>

</div>

</body>
</html>
