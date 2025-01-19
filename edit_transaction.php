<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $stok_masuk = $_POST['stok_masuk'];
    $stok_keluar = $_POST['stok_keluar'];

    // Mendapatkan (Get) Transaksi Saat Ini
    $query_current = "SELECT * FROM transaksi WHERE id = $id";
    $result_current = mysqli_query($conn, $query_current);
    $current = mysqli_fetch_assoc($result_current);

    $stok_akhir = $current['stok_awal'] + $stok_masuk - $stok_keluar;

    $query = "UPDATE transaksi 
              SET stok_masuk = $stok_masuk, 
                  stok_keluar = $stok_keluar, 
                  stok_akhir = $stok_akhir 
              WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        // Memperbarui Transaksi Berikutnya
        $nama_buah = $current['nama_buah'];
        $query_subsequent = "SELECT * FROM transaksi 
                           WHERE nama_buah = '$nama_buah' 
                           AND id > $id 
                           ORDER BY id ASC";
        $result_subsequent = mysqli_query($conn, $query_subsequent);

        while ($row = mysqli_fetch_assoc($result_subsequent)) {
            $new_stok_awal = $stok_akhir;
            $new_stok_akhir = $new_stok_awal + $row['stok_masuk'] - $row['stok_keluar'];
            
            mysqli_query($conn, "UPDATE transaksi 
                                SET stok_awal = $new_stok_awal, 
                                    stok_akhir = $new_stok_akhir 
                                WHERE id = {$row['id']}");
            
            $stok_akhir = $new_stok_akhir;
        }

        header("Location: table.php");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>