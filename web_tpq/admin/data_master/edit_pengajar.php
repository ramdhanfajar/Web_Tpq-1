<?php
session_start();
require '../../koneksi.php';

// CEK LOGIN ADMIN
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: data_pengajar.php");
    exit();
}

$id = $_GET['id'];

// PROSES UPDATE DATA
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user_id     = $_POST['user_id'];
    $nip         = $_POST['nip'];
    $nama        = $_POST['nama_lengkap'];
    $telepon     = $_POST['no_telepon'];
    $alamat      = $_POST['alamat'];

    $query = "UPDATE data_pengajar SET 
                user_id='$user_id',
                nip='$nip',
                nama_lengkap='$nama',
                no_telepon='$telepon',
                alamat='$alamat'
              WHERE id='$id'";

    if ($koneksi->query($query)) {
        header("Location: data_pengajar.php?status=updated");
        exit();
    } else {
        echo "Gagal memperbarui data: " . $koneksi->error;
    }
}

// AMBIL DATA UNTUK EDIT
$data = $koneksi->query("SELECT * FROM data_pengajar WHERE id='$id'")->fetch_assoc();

// AMBIL USERS UNTUK DROPDOWN
$list_user = $koneksi->query("SELECT id, email FROM users ORDER BY email ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Edit Pengajar</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
<?php include 'style_santri.css'; ?>
</style>

</head>
<body>

<div class="form-container">

    <a href="data_pengajar.php" class="btn-kembali"><i class="fa fa-arrow-left"></i> Kembali</a>

    <h2>Edit Data Pengajar</h2>

    <form method="POST">

        <label>Pilih User Login</label>
        <select name="user_id" required>
            <?php while ($u = $list_user->fetch_assoc()): ?>
                <option value="<?= $u['id']; ?>" 
                    <?= ($u['id'] == $data['user_id']) ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($u['email']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>NIP</label>
        <input type="text" name="nip" value="<?= htmlspecialchars($data['nip']); ?>">

        <label>Nama Lengkap</label>
        <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($data['nama_lengkap']); ?>" required>

        <label>No. Telepon</label>
        <input type="text" name="no_telepon" value="<?= htmlspecialchars($data['no_telepon']); ?>">

        <label>Alamat</label>
        <textarea name="alamat"><?= htmlspecialchars($data['alamat']); ?></textarea>

        <button type="submit" class="btn-submit">Perbarui</button>

    </form>

</div>

</body>
</html>
