<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include('config.php');

// Header Untuk Download CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="data_transaksi_' . date('Y-m-d') . '.csv"');

// Pointer File
$output = fopen('php://output', 'w');

// Merender Karakter Indonesia
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Menambahkan Header ke CSV
fputcsv($output, array('ID', 'Nama Buah', 'Kategori', 'Satuan', 'Stok Awal', 'Stok Masuk', 'Stok Keluar', 'Stok Akhir', 'Tanggal Masuk'));

// Mengambil (Get) Data Dari Database
$query = "SELECT * FROM transaksi ORDER BY id DESC";
$result = mysqli_query($conn, $query);

// Output Setiap Baris Data
while($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, $row);
}

// Menutup File Pointer
fclose($output);
exit();
?>