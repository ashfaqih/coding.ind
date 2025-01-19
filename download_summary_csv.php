<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include('config.php');

// Header Untuk Download CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="rangkuman_stok_' . date('Y-m-d') . '.csv"');

// Pointer File
$output = fopen('php://output', 'w');

// Merender Karakter Indonesia
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Menambahkan Header ke CSV
fputcsv($output, array('Nama Buah', 'Kategori', 'Satuan', 'Stok Terkini'));

// Mengambil (Get) Data Dari Database
$query = "SELECT 
            nama_buah,
            kategori,
            satuan,
            MAX(CASE WHEN id = (
                SELECT MAX(id) 
                FROM transaksi t2 
                WHERE t2.nama_buah = t1.nama_buah
            ) THEN stok_akhir END) as stok_terkini
         FROM transaksi t1
         GROUP BY nama_buah, kategori, satuan";

$result = mysqli_query($conn, $query);

// Output Setiap Baris Data
while($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, $row);
}

// Menutup File Pointer
fclose($output);
exit();
?>