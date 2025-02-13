-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 19 Jan 2025 pada 13.28
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
(1, 'Durian', 'buah musiman', 'kg', 0, 120, 85, 35, '2024-10-01'),
(2, 'Manggis', 'buah musiman', 'box', 15, 200, 180, 35, '2024-10-03'),
(3, 'Rambutan', 'buah musiman', 'kg', 10, 150, 120, 40, '2024-10-04'),
(4, 'Alpukat', 'buah musiman', 'kg', 5, 80, 65, 20, '2024-10-06'),
(5, 'Melon', 'buah tropis', 'kg', 0, 100, 85, 15, '2024-10-07'),
(6, 'Durian', 'buah musiman', 'kg', 35, 100, 110, 25, '2024-10-09'),
(7, 'Jeruk Nipis', 'buah lokal', 'kg', 8, 75, 60, 23, '2024-10-10'),
(8, 'Manggis', 'buah musiman', 'box', 35, 180, 160, 55, '2024-10-12'),
(9, 'Salak', 'buah lokal', 'kg', 12, 90, 75, 27, '2024-10-13'),
(10, 'Alpukat', 'buah musiman', 'kg', 20, 85, 70, 35, '2024-10-15'),
(11, 'Melon', 'buah tropis', 'kg', 15, 110, 95, 30, '2024-10-17'),
(12, 'Rambutan', 'buah musiman', 'kg', 40, 140, 135, 45, '2024-10-19'),
(13, 'Jeruk Nipis', 'buah lokal', 'kg', 23, 70, 65, 28, '2024-10-21'),
(14, 'Durian', 'buah musiman', 'kg', 25, 110, 95, 40, '2024-10-22'),
(15, 'Manggis', 'buah musiman', 'box', 55, 170, 185, 40, '2024-10-24'),
(16, 'Salak', 'buah lokal', 'kg', 27, 85, 80, 32, '2024-10-26'),
(17, 'Melon', 'buah tropis', 'kg', 30, 105, 100, 35, '2024-10-28'),
(18, 'Alpukat', 'buah musiman', 'kg', 35, 75, 85, 25, '2024-10-29'),
(19, 'Rambutan', 'buah musiman', 'kg', 45, 130, 140, 35, '2024-10-30'),
(20, 'Jeruk Nipis', 'buah lokal', 'kg', 28, 65, 70, 23, '2024-10-31'),
(21, 'Durian', 'buah musiman', 'kg', 40, 115, 120, 35, '2024-11-01'),
(22, 'Manggis', 'buah musiman', 'box', 40, 190, 175, 55, '2024-11-03'),
(23, 'Kesemek', 'buah musiman', 'pcs', 0, 300, 250, 50, '2024-11-04'),
(24, 'Alpukat', 'buah musiman', 'kg', 25, 90, 80, 35, '2024-11-06'),
(25, 'Melon', 'buah tropis', 'kg', 35, 95, 100, 30, '2024-11-07'),
(26, 'Durian', 'buah musiman', 'kg', 35, 125, 115, 45, '2024-11-09'),
(27, 'Jeruk Manis', 'buah lokal', 'kg', 0, 110, 85, 25, '2024-11-10'),
(28, 'Manggis', 'buah musiman', 'box', 55, 175, 185, 45, '2024-11-12'),
(29, 'Kesemek', 'buah musiman', 'pcs', 50, 280, 260, 70, '2024-11-13'),
(30, 'Alpukat', 'buah musiman', 'kg', 35, 85, 90, 30, '2024-11-15'),
(31, 'Melon', 'buah tropis', 'kg', 30, 105, 95, 40, '2024-11-17'),
(32, 'Jeruk Manis', 'buah lokal', 'kg', 25, 100, 90, 35, '2024-11-19'),
(33, 'Durian', 'buah musiman', 'kg', 45, 120, 130, 35, '2024-11-21'),
(34, 'Manggis', 'buah musiman', 'box', 45, 185, 175, 55, '2024-11-22'),
(35, 'Kesemek', 'buah musiman', 'pcs', 70, 260, 280, 50, '2024-11-24'),
(36, 'Alpukat', 'buah musiman', 'kg', 30, 95, 85, 40, '2024-11-26'),
(37, 'Melon', 'buah tropis', 'kg', 40, 90, 105, 25, '2024-11-28'),
(38, 'Jeruk Manis', 'buah lokal', 'kg', 35, 95, 100, 30, '2024-11-29'),
(39, 'Durian', 'buah musiman', 'kg', 35, 130, 120, 45, '2024-11-30'),
(40, 'Manggis', 'buah musiman', 'box', 55, 170, 180, 45, '2024-12-01'),
(41, 'Durian', 'buah musiman', 'kg', 45, 135, 140, 40, '2024-12-02'),
(42, 'Rambutan', 'buah musiman', 'kg', 0, 160, 130, 30, '2024-12-04'),
(43, 'Alpukat', 'buah musiman', 'kg', 40, 95, 105, 30, '2024-12-05'),
(44, 'Melon', 'buah tropis', 'kg', 25, 115, 100, 40, '2024-12-07'),
(45, 'Durian', 'buah musiman', 'kg', 40, 140, 135, 45, '2024-12-09'),
(46, 'Manggis', 'buah musiman', 'box', 45, 195, 185, 55, '2024-12-10'),
(47, 'Rambutan', 'buah musiman', 'kg', 30, 155, 140, 45, '2024-12-12'),
(48, 'Alpukat', 'buah musiman', 'kg', 30, 100, 90, 40, '2024-12-13'),
(49, 'Melon', 'buah tropis', 'kg', 40, 110, 120, 30, '2024-12-15'),
(50, 'Durian', 'buah musiman', 'kg', 45, 145, 150, 40, '2024-12-17'),
(51, 'Manggis', 'buah musiman', 'box', 55, 185, 195, 45, '2024-12-19'),
(52, 'Rambutan', 'buah musiman', 'kg', 45, 150, 155, 40, '2024-12-21'),
(53, 'Alpukat', 'buah musiman', 'kg', 40, 105, 115, 30, '2024-12-22'),
(54, 'Melon', 'buah tropis', 'kg', 30, 120, 110, 40, '2024-12-24'),
(55, 'Durian', 'buah musiman', 'kg', 40, 150, 145, 45, '2024-12-26'),
(56, 'Manggis', 'buah musiman', 'box', 45, 190, 185, 50, '2024-12-28'),
(57, 'Rambutan', 'buah musiman', 'kg', 40, 165, 160, 45, '2024-12-29'),
(58, 'Alpukat', 'buah musiman', 'kg', 30, 110, 100, 40, '2024-12-30'),
(59, 'Melon', 'buah tropis', 'kg', 40, 115, 125, 30, '2024-12-31'),
(60, 'Durian', 'buah musiman', 'kg', 45, 155, 160, 40, '2025-01-02'),
(61, 'Manggis', 'buah musiman', 'box', 50, 200, 205, 45, '2025-01-04'),
(62, 'Rambutan', 'buah musiman', 'kg', 45, 170, 175, 40, '2025-01-06'),
(63, 'Alpukat', 'buah musiman', 'kg', 40, 115, 120, 35, '2025-01-08'),
(64, 'Melon', 'buah tropis', 'kg', 30, 125, 115, 40, '2025-01-10'),
(65, 'Durian', 'buah musiman', 'kg', 40, 160, 155, 45, '2025-01-11'),
(66, 'Manggis', 'buah musiman', 'box', 45, 205, 200, 50, '2025-01-13'),
(67, 'Rambutan', 'buah musiman', 'kg', 40, 175, 170, 45, '2025-01-14'),
(68, 'Alpukat', 'buah musiman', 'kg', 35, 120, 115, 40, '2025-01-15'),
(69, 'Melon', 'buah tropis', 'kg', 40, 130, 135, 35, '2025-01-16'),
(70, 'Durian', 'buah musiman', 'kg', 45, 165, 170, 40, '2025-01-17');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
