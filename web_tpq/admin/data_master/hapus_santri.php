<?php
include '../../koneksi.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "<script>alert('ID santri tidak ditemukan!'); window.location='data_santri.php';</script>";
    exit;
}

// hapus data berdasarkan ID
$query = "DELETE FROM data_santri WHERE id = '$id'";

if (mysqli_query($koneksi, $query)) {
    echo "<script>alert('Data santri berhasil dihapus!'); window.location='data_santri.php';</script>";
} else {
    echo "<script>alert('Gagal menghapus data: " . mysqli_error($koneksi) . "'); window.location='data_santri.php';</script>";
}
?>
