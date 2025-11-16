<?php
session_start();
require '../../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: data_pengajar.php");
    exit();
}

$id = $_GET['id'];

$query = "DELETE FROM data_pengajar WHERE id='$id'";

if ($koneksi->query($query)) {
    header("Location: data_pengajar.php?status=deleted");
    exit();
} else {
    echo "Gagal menghapus data: " . $koneksi->error;
}
