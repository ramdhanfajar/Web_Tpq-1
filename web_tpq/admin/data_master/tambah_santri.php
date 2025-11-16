<?php
// tambah_santri.php
include '../../koneksi.php'; // sesuaikan path jika berbeda

if (isset($_POST['simpan'])) {
    // otomatis isi user_id (misalnya 1)
    $user_id = 1;

    // ambil data dari form
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

    // simpan ke database
    $query = "INSERT INTO data_santri 
        (user_id, id_kelas, nama_lengkap, nis, nama_wali, no_telepon_wali, alamat, jenis_kelamin, 
        tempat_lahir, tanggal_lahir, no_hp, nama_ayah, pekerjaan_ayah, nama_ibu, pekerjaan_ibu, gaji_per_bulan)
        VALUES 
        ('$user_id', " . ($id_kelas ? "'$id_kelas'" : "NULL") . ", '$nama_lengkap', '$nis', '$nama_wali', 
        '$no_telepon_wali', '$alamat', '$jenis_kelamin', '$tempat_lahir', '$tanggal_lahir', '$no_hp', 
        '$nama_ayah', '$pekerjaan_ayah', '$nama_ibu', '$pekerjaan_ibu', '$gaji_per_bulan')";

    $result = mysqli_query($koneksi, $query);

    if ($result) {
        echo "<script>alert('Data santri berhasil ditambahkan!'); window.location='data_santri.php';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan data santri: " . mysqli_error($koneksi) . "');</script>";
    }
}

// ambil daftar kelas (opsional)
$kelas_result = mysqli_query($koneksi, "SELECT id, nama_kelas FROM kelas");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Santri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0">Tambah Data Santri</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>NIS</label>
                        <input type="text" name="nis" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Kelas</label>
                        <select name="id_kelas" class="form-select">
                            <option value="">-- Pilih Kelas --</option>
                            <?php while ($row = mysqli_fetch_assoc($kelas_result)) : ?>
                                <option value="<?= $row['id']; ?>"><?= $row['nama_kelas']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-select">
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>No HP Santri</label>
                        <input type="text" name="no_hp" class="form-control">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Alamat</label>
                        <textarea name="alamat" class="form-control"></textarea>
                    </div>

                    <hr class="my-3">

                    <div class="col-md-6 mb-3">
                        <label>Nama Ayah</label>
                        <input type="text" name="nama_ayah" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Pekerjaan Ayah</label>
                        <input type="text" name="pekerjaan_ayah" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Nama Ibu</label>
                        <input type="text" name="nama_ibu" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Pekerjaan Ibu</label>
                        <input type="text" name="pekerjaan_ibu" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Nama Wali</label>
                        <input type="text" name="nama_wali" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>No Telepon Wali</label>
                        <input type="text" name="no_telepon_wali" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Gaji Per Bulan</label>
                        <input type="number" name="gaji_per_bulan" class="form-control">
                    </div>
                </div>

                <div class="text-end">
                    <a href="data_santri.php" class="btn btn-secondary">Kembali</a>
                    <button type="submit" name="simpan" class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
