-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 17 Jan 2025 pada 13.20
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gudang_buah`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `login`
--

CREATE TABLE `login` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `tanggal_dibuat` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `login`
--

INSERT INTO `login` (`id`, `username`, `password`, `tanggal_dibuat`) VALUES
(1, 'admin', 'admin123', '2025-01-17 15:35:43');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int(11) NOT NULL,
  `nama_buah` varchar(100) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `satuan` varchar(20) NOT NULL,
  `stok_awal` int(11) NOT NULL,
  `stok_masuk` int(11) NOT NULL,
  `stok_keluar` int(11) NOT NULL,
  `stok_akhir` int(11) NOT NULL,
  `tanggal_masuk` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi`
--

INSERT INTO `transaksi` (`id`, `nama_buah`, `kategori`, `satuan`, `stok_awal`, `stok_masuk`, `stok_keluar`, `stok_akhir`, `tanggal_masuk`) VALUES
(1, 'durian', 'buah musiman', 'kg', 0, 100, 30, 70, '2024-10-05'),
(2, 'manggis', 'buah musiman', 'kg', 0, 120, 40, 80, '2024-10-15'),
(3, 'rambutan', 'buah musiman', 'kg', 0, 150, 50, 100, '2024-10-20'),
(4, 'alpukat', 'buah musiman', 'kg', 0, 90, 25, 65, '2024-10-25'),
(5, 'kesemek', 'buah musiman', 'kg', 0, 110, 35, 75, '2024-11-05'),
(6, 'jeruk_manis', 'buah musiman', 'kg', 0, 130, 45, 85, '2024-11-15'),
(7, 'salak', 'buah tropis', 'kg', 0, 140, 50, 90, '2024-11-20'),
(8, 'jeruk_nipis', 'buah musiman', 'kg', 0, 160, 60, 100, '2024-11-25'),
(9, 'durian', 'buah musiman', 'kg', 70, 125, 45, 150, '2024-12-05'),
(10, 'rambutan', 'buah musiman', 'kg', 100, 155, 55, 200, '2024-12-15'),
(11, 'manggis', 'buah musiman', 'kg', 80, 135, 50, 165, '2024-12-20'),
(12, 'alpukat', 'buah musiman', 'kg', 65, 110, 40, 135, '2024-12-25'),
(13, 'kesemek', 'buah musiman', 'kg', 75, 105, 30, 150, '2025-01-05'),
(14, 'jeruk_manis', 'buah musiman', 'kg', 85, 115, 35, 165, '2025-01-15'),
(15, 'belimbing', 'buah tropis', 'kg', 0, 125, 40, 85, '2025-01-20'),
(16, 'melon', 'buah tropis', 'kg', 0, 145, 50, 95, '2025-01-25');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `login`
--
ALTER TABLE `login`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `login`
--
ALTER TABLE `login`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
