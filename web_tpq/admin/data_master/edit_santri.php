<?php
include '../../koneksi.php';

// ambil id santri dari URL
$id = $_GET['id'] ?? null;
if (!$id) {
    echo "<script>alert('ID santri tidak ditemukan!'); window.location='data_santri.php';</script>";
    exit;
}

// ambil data santri berdasarkan ID
$result = mysqli_query($koneksi, "SELECT * FROM data_santri WHERE id = '$id'");
$data = mysqli_fetch_assoc($result);

// ambil daftar kelas (untuk dropdown)
$kelas_result = mysqli_query($koneksi, "SELECT id, nama_kelas FROM kelas");

// jika tombol update ditekan
if (isset($_POST['update'])) {
    $id_kelas         = $_POST['id_kelas'] ?: null;
    $nama_lengkap     = $_POST['nama_lengkap'];
    $nis              = $_POST['nis'];
    $nama_wali        = $_POST['nama_wali'];
    $no_telepon_wali  = $_POST['no_telepon_wali'];
    $alamat           = $_POST['alamat'];
    $jenis_kelamin    = $_POST['jenis_kelamin'];
    $tempat_lahir     = $_POST['tempat_lahir'];
    $tanggal_lahir    = $_POST['tanggal_lahir'];
    $no_hp            = $_POST['no_hp'];
    $nama_ayah        = $_POST['nama_ayah'];
    $pekerjaan_ayah   = $_POST['pekerjaan_ayah'];
    $nama_ibu         = $_POST['nama_ibu'];
    $pekerjaan_ibu    = $_POST['pekerjaan_ibu'];
    $gaji_per_bulan   = $_POST['gaji_per_bulan'];

    $query = "UPDATE data_santri SET 
        id_kelas = " . ($id_kelas ? "'$id_kelas'" : "NULL") . ",
        nama_lengkap='$nama_lengkap',
        nis='$nis',
        nama_wali='$nama_wali',
        no_telepon_wali='$no_telepon_wali',
        alamat='$alamat',
        jenis_kelamin='$jenis_kelamin',
        tempat_lahir='$tempat_lahir',
        tanggal_lahir='$tanggal_lahir',
        no_hp='$no_hp',
        nama_ayah='$nama_ayah',
        pekerjaan_ayah='$pekerjaan_ayah',
        nama_ibu='$nama_ibu',
        pekerjaan_ibu='$pekerjaan_ibu',
        gaji_per_bulan='$gaji_per_bulan'
        WHERE id='$id'";

    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Data santri berhasil diperbarui!'); window.location='data_santri.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data: " . mysqli_error($koneksi) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Santri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Edit Data Santri</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control" value="<?= $data['nama_lengkap']; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>NIS</label>
                        <input type="text" name="nis" class="form-control" value="<?= $data['nis']; ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Kelas</label>
                        <select name="id_kelas" class="form-select">
                            <option value="">-- Pilih Kelas --</option>
                            <?php while ($kelas = mysqli_fetch_assoc($kelas_result)) : ?>
                                <option value="<?= $kelas['id']; ?>" <?= ($data['id_kelas'] == $kelas['id']) ? 'selected' : ''; ?>>
                                    <?= $kelas['nama_kelas']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-select">
                            <option value="Laki-laki" <?= ($data['jenis_kelamin'] == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                            <option value="Perempuan" <?= ($data['jenis_kelamin'] == 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" class="form-control" value="<?= $data['tempat_lahir']; ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" class="form-control" value="<?= $data['tanggal_lahir']; ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>No HP</label>
                        <input type="text" name="no_hp" class="form-control" value="<?= $data['no_hp']; ?>">
                    </div>
                    <div class="col-md-12 mb-3">
                        <label>Alamat</label>
                        <textarea name="alamat" class="form-control"><?= $data['alamat']; ?></textarea>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Nama Ayah</label>
                        <input type="text" name="nama_ayah" class="form-control" value="<?= $data['nama_ayah']; ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Pekerjaan Ayah</label>
                        <input type="text" name="pekerjaan_ayah" class="form-control" value="<?= $data['pekerjaan_ayah']; ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Nama Ibu</label>
                        <input type="text" name="nama_ibu" class="form-control" value="<?= $data['nama_ibu']; ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Pekerjaan Ibu</label>
                        <input type="text" name="pekerjaan_ibu" class="form-control" value="<?= $data['pekerjaan_ibu']; ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Nama Wali</label>
                        <input type="text" name="nama_wali" class="form-control" value="<?= $data['nama_wali']; ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>No Telepon Wali</label>
                        <input type="text" name="no_telepon_wali" class="form-control" value="<?= $data['no_telepon_wali']; ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Gaji Per Bulan</label>
                        <input type="number" name="gaji_per_bulan" class="form-control" value="<?= $data['gaji_per_bulan']; ?>">
                    </div>
                </div>

                <div class="text-end">
                    <a href="data_santri.php" class="btn btn-secondary">Kembali</a>
                    <button type="submit" name="update" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
