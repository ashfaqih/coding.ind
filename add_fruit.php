<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include('config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Mengambil dan Membersihkan Input
    $nama_buah = mysqli_real_escape_string($conn, $_POST['nama_buah']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $satuan = mysqli_real_escape_string($conn, $_POST['satuan']);
    $stok_masuk = (int)$_POST['stok_masuk'];
    $stok_keluar = (int)$_POST['stok_keluar'];
    
    // Melakukan Validasi Input
    if ($stok_keluar > $stok_masuk) {
        header("Location: table.php?error=Stok keluar tidak boleh lebih besar dari stok masuk!");
        exit();
    }
    
    // Memeriksa Apakah Buahnya Sudah Ada
    $check_query = "SELECT nama_buah FROM transaksi WHERE nama_buah = '$nama_buah' LIMIT 1";
    $result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($result) > 0) {
        header("Location: table.php?error=Buah sudah ada dalam database!");
        exit();
    }
    
    // Menghitung Nilai Stok
    $stok_awal = 0; // Karena Buah Baru, Maka Stok Awalnya Adalah 0
    $stok_akhir = $stok_masuk - $stok_keluar; // Hitung Stok Akhir Secara Otomatis
    $tanggal_masuk = date('Y-m-d H:i:s');
    
    // Memasukkan Transaksi Buah Baru
    $query = "INSERT INTO transaksi (nama_buah, kategori, satuan, stok_awal, stok_masuk, stok_keluar, stok_akhir, tanggal_masuk) 
              VALUES ('$nama_buah', '$kategori', '$satuan', $stok_awal, $stok_masuk, $stok_keluar, $stok_akhir, '$tanggal_masuk')";
    
    if (mysqli_query($conn, $query)) {
        header("Location: table.php?success=Data buah baru berhasil ditambahkan!");
    } else {
        header("Location: table.php?error=Error menambahkan buah baru: " . mysqli_error($conn));
    }
} else {
    header("Location: table.php");
}
?>