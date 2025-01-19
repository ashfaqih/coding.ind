<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_buah = $_POST['nama_buah'];
    $stok_masuk = $_POST['stok_masuk'];
    $stok_keluar = $_POST['stok_keluar'];

    // Mengambil (Get) Transaksi Terbaru Untuk Buah Yang Dipilih
    $query_last = "SELECT * FROM transaksi WHERE nama_buah = '$nama_buah' 
                   ORDER BY id DESC LIMIT 1";
    $result_last = mysqli_query($conn, $query_last);
    $last_transaction = mysqli_fetch_assoc($result_last);

    // Mengambil (Get) Detail Buah Seperti Kategori dan Satuan
    $query_details = "SELECT DISTINCT kategori, satuan FROM transaksi 
                     WHERE nama_buah = '$nama_buah' LIMIT 1";
    $result_details = mysqli_query($conn, $query_details);
    $details = mysqli_fetch_assoc($result_details);

    $stok_awal = $last_transaction ? $last_transaction['stok_akhir'] : 0;
    $stok_akhir = $stok_awal + $stok_masuk - $stok_keluar;
    $kategori = $details['kategori'];
    $satuan = $details['satuan'];

    $query = "INSERT INTO transaksi (nama_buah, kategori, satuan, stok_awal, stok_masuk, 
              stok_keluar, stok_akhir, tanggal_masuk) 
              VALUES ('$nama_buah', '$kategori', '$satuan', $stok_awal, $stok_masuk, 
              $stok_keluar, $stok_akhir, CURRENT_DATE())";
    
    if (mysqli_query($conn, $query)) {
        header("Location: table.php");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}