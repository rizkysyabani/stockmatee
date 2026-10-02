-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Waktu pembuatan: 21 Jun 2025 pada 11.20
-- Versi server: 10.11.10-MariaDB-log
-- Versi PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u115277227_stockbarang`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `login`
--

CREATE TABLE `login` (
  `iduser` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `roles` varchar(50) DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `login`
--

INSERT INTO `login` (`iduser`, `username`, `password`, `roles`) VALUES
(1, 'adminPusat', '$2y$10$IhiniSbNW5QHMmjHjtaQPulg0nN9XemlvMN4gUqUMDkLhTqvh3dte', 'adminPusat'),
(2, 'karyawanPusat', '$2y$10$IhiniSbNW5QHMmjHjtaQPulg0nN9XemlvMN4gUqUMDkLhTqvh3dte', 'karyawanPusat'),
(3, 'ilmimahrus', '$2y$10$IhiniSbNW5QHMmjHjtaQPulg0nN9XemlvMN4gUqUMDkLhTqvh3dte', 'owner'),
(4, 'adminParahita', '$2y$10$z/k2CyQ3cfoiCSuXcDU3zepjcsRhkNnc57Go1diex6JTHd9WPMav6', 'adminParahita'),
(5, 'adminSerpong', '$2y$10$z/k2CyQ3cfoiCSuXcDU3zepjcsRhkNnc57Go1diex6JTHd9WPMav6', 'adminSerpong'),
(6, 'karyawanParahita', '$2y$10$9M3EjFKV00Q2yXIe./z6Ke6Bxm6vLukstXuiZHePdLiwkjy11sZEa', 'karyawanParahita'),
(7, 'karyawanSerpong', '$2y$10$z/k2CyQ3cfoiCSuXcDU3zepjcsRhkNnc57Go1diex6JTHd9WPMav6', 'karyawanSerpong');

-- --------------------------------------------------------

--
-- Struktur dari tabel `parahita_keluar`
--

CREATE TABLE `parahita_keluar` (
  `idkeluar` int(11) NOT NULL,
  `idbarang` int(11) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `penerima` varchar(50) NOT NULL,
  `qty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `parahita_keluar`
--

INSERT INTO `parahita_keluar` (`idkeluar`, `idbarang`, `tanggal`, `penerima`, `qty`) VALUES
(1, 20, '2025-06-03 15:38:04', 'Pembeli ', 1),
(2, 29, '2025-06-03 15:38:31', 'Pembeli', 1),
(3, 16, '2025-06-03 16:08:28', 'Pembeli', 1),
(4, 19, '2025-06-03 16:21:34', 'Pembeli', 1),
(5, 15, '2025-06-03 17:17:04', 'pembeli', 1),
(6, 7, '2025-06-04 16:27:11', 'pembeli', 2),
(7, 7, '2025-06-04 16:27:22', 'pembeli', 2),
(8, 19, '2025-06-04 16:27:35', 'pembeli', 1),
(9, 35, '2025-06-04 16:37:34', 'pembeli', 2),
(10, 27, '2025-06-04 16:40:01', 'pembeli', 3),
(13, 7, '2025-06-15 02:51:33', 'Pembeli', 2),
(14, 20, '2025-06-15 02:51:54', 'Pembeli', 1),
(15, 19, '2025-06-15 02:52:05', 'Pembeli', 1),
(16, 27, '2025-06-15 02:52:22', 'Pembeli', 2),
(17, 28, '2025-06-15 02:58:03', 'Pembeli', 1),
(18, 16, '2025-06-15 03:08:01', 'Pembeli', 1),
(19, 8, '2025-06-15 03:46:58', 'Pembeli', 1),
(20, 34, '2025-06-15 03:47:22', 'Pembeli', 1),
(21, 18, '2025-06-15 04:43:07', 'Pembeli', 2),
(22, 50, '2025-06-15 04:44:33', 'Pembeli', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `parahita_masuk`
--

CREATE TABLE `parahita_masuk` (
  `idmasuk` int(11) NOT NULL,
  `idbarang` int(11) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `keterangan` varchar(50) NOT NULL,
  `qty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `parahita_masuk`
--

INSERT INTO `parahita_masuk` (`idmasuk`, `idbarang`, `tanggal`, `keterangan`, `qty`) VALUES
(7, 36, '2025-06-15 03:44:46', 'Diterima dari Gudang Pusat (PP ID: 11)', 30),
(8, 37, '2025-06-15 03:44:50', 'Diterima dari Gudang Pusat (PP ID: 10)', 14),
(9, 38, '2025-06-15 03:44:53', 'Diterima dari Gudang Pusat (PP ID: 9)', 20),
(10, 39, '2025-06-15 03:44:56', 'Diterima dari Gudang Pusat (PP ID: 8)', 2),
(11, 40, '2025-06-15 03:44:59', 'Diterima dari Gudang Pusat (PP ID: 7)', 4),
(12, 41, '2025-06-15 03:45:02', 'Diterima dari Gudang Pusat (PP ID: 6)', 5),
(13, 42, '2025-06-15 03:45:06', 'Diterima dari Gudang Pusat (PP ID: 5)', 6),
(14, 43, '2025-06-15 03:45:13', 'Diterima dari Gudang Pusat (PP ID: 4)', 6),
(15, 44, '2025-06-15 03:45:17', 'Diterima dari Gudang Pusat (PP ID: 3)', 12);

-- --------------------------------------------------------

--
-- Struktur dari tabel `parahita_stock`
--

CREATE TABLE `parahita_stock` (
  `idbarang` int(11) NOT NULL,
  `namabarang` varchar(50) NOT NULL,
  `deskripsi` varchar(50) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `image` varchar(99) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `parahita_stock`
--

INSERT INTO `parahita_stock` (`idbarang`, `namabarang`, `deskripsi`, `stock`, `image`) VALUES
(5, 'Antena tv kecil', 'Elektronik', 14, '00561e95b1218167759e115a1ceff7b8.jpg'),
(6, 'Antena tv Panjang', 'Elektronik', 12, '7875ad3fd9ceac06da765d956b718566.jpg'),
(7, 'Colokan Bulat', 'Elektronik', 116, 'b5b5fc492c843d4d1029f6bc96fd75d9.jpg'),
(8, 'Colokan Gepeng ', 'Elektronik', 123, '5f8030e3cf0d0447f675ebae715bd6ce.jpg'),
(9, 'Dispenser Panas, Dingin ', 'Elektronik', 8, '9d258c6c1d74d45b153ae85887dff209.jpg'),
(10, 'Dispenser Panas, Normal', 'Elektronik', 7, '74fd5204abd0de8ebd9c1f826bc74a01.jpg'),
(11, 'Garpu', 'Perabot', 29, '398ef493598b810bd137030efc1ad26d.jpg'),
(12, 'Gelas Kaca Besar', 'Perabot', 24, '214eba037e6b15345fdc1bfac06cd950.jpg'),
(13, 'Gelas Kopi Kacil', 'Perabot', 31, '5f98e9d653ae999efd4979b61a2c433a.jpg'),
(14, 'Gelas Pelastik', 'Perabot', 42, '37cefce454a9dcab9638ca02dbd30b7e.jpg'),
(15, 'Kipas Angin Karakter', 'Elektronik', 19, '5f166170f6248b5d626fc221bcd53180.jpg'),
(16, 'Kipas Angin Stand Fan 16\"', 'Elektronik', 16, 'feca189a4c4f74a2afa2ee57a2f491db.jpg'),
(17, 'Kipas Angin Wall Fan 16\"', 'Elektronik', 12, '9042be155f0f6c038ccd28aaf82d20c7.jpg'),
(18, 'Lampu 10 w', 'Elektronik', 121, '86009b853c86e27c6585d189302ade4d.jpg'),
(19, 'Lampu 20 w', 'Elektronik', 5, '72dbeff2df96afc3e19d18c605f6a636.jpg'),
(20, 'Lampu 30 w', 'Elektronik', 75, '9eb05007ca4d471fffe213adaf659e3a.jpg'),
(21, 'Magic com 1,2L (kecil)', 'Elektronik', 10, 'e2be5a9a1b7c2c0ab7f264462704ccfe.jpg'),
(22, 'Magic com 1,8L', 'Elektronik', 6, '6b4a24a740dcd67591295ac64da2be58.jpg'),
(23, 'Mangkok Keramik', 'Perabot', 32, 'a9eaa9fca3cbbabe6a7dfc1c7e9ca6e2.jpg'),
(24, 'Piring Keramik', 'Perabot', 41, 'e4bf4a984511f446c9925dd778155730.jpg'),
(25, 'Piring Melamin', 'Perabot', 40, '583bc0cb955f80d0372890b27770fefb.jpg'),
(26, 'Piring Plastik', 'Perabot', 34, 'bcc548fa5db0bc65b2918dfdd3f1f756.jpg'),
(27, 'Piting Lampu Gantung', 'Elektronik', 40, 'a44e075f05486dc5e03c4db1d4f0a1ff.jpg'),
(28, 'Regulator selang', 'Elektronik', 5, '0d9185d59fb0cfb355675e62b147a31d.jpg'),
(29, 'Rollan 2 Lubang, 3m', 'Elektronik', 10, '3861ec791cc7d626735bf65ee34cd531.jpg'),
(30, 'Rollan 3 Lubang, 3m', 'Elektronik', 11, '4b1f45c33a4d2fdb01f8e9b22f0538f8.jpg'),
(31, 'Rollan 4 Lubang, 5m', 'Elektronik', 13, '65174b32f819742a4446aacfb70395af.jpg'),
(32, 'Sendok Kecil', 'Perabot', 18, NULL),
(33, 'Sendok Makan', 'Perabot', 20, 'c8c0809556f7f03e12387589a84b3450.jpg'),
(34, 'Stop Kontak 2 Lubang', 'Elektronik', 30, '8fa61a42db8cac82d17f9e090e8eaa39.jpg'),
(35, 'Stop Kontak 4 Lubang', 'Elektronik', 23, '0fbc3e20d7ad45ea613f7ca6deaa156c.jpg'),
(36, 'Tempat Donat SL', 'Perabot', 30, '7cfbde7fd384ed82ad16a748ab37195c.jpeg'),
(37, 'Tempat Donat M', 'Perabot', 14, 'c0a5871a82e49899531a017df17b7da7.jpeg'),
(38, 'Tempat Donat L', 'Perabot', 20, '10f0f0a370d91e5a1f42353118a83c67.jpeg'),
(39, 'Box CB 130', 'Perabot', 2, '762e29bd292ea252ee65158786212490.jpeg'),
(40, 'Box CB 95', 'Perabot', 4, '9ce0fbe5bc093bf3c1c7e34a8075cfcf.jpeg'),
(41, 'Box CB 82', 'Perabot', 5, 'b7a858dadf7e7b0f93cb02dd198a94bf.jpeg'),
(42, 'Box CB 60', 'Perabot', 6, '4c84fe54ad819410cdc3fb3e2793c490.jpeg'),
(43, 'Box CB 45', 'Perabot', 6, 'c2fc53713de93a5bcdadff3776bb34fe.jpeg'),
(44, 'Box CB 25', 'Perabot', 12, '5ee6037b4e448ccbdc4ea92d92ab66f7.jpeg'),
(45, 'Sapu ijuk', 'Perabot', 65, 'a1b9b9aa26bbe4226c379b04cfe7dd86.png'),
(46, 'Sapu Lantai', 'Perabot', 46, '55dfbe4f7aec95c94b8764c679e35f24.jpg'),
(47, 'Sapu Lidi', 'Perabot', 22, 'cf0bf7184d6b401dcf10a898b7a733bf.jpg'),
(48, 'Sapu Lidi Gagang', 'Perabot', 24, '42513c38b152ac132c31fbc8984a099c.jpeg'),
(49, 'Sapu Lidi Kasur', 'Perabot', 28, 'a9bb606bad1cb836cbe279a212181ef1.jpg'),
(50, 'Pelan', 'Perabot', 55, '9bb2a2df39607c329a7201e84bdc4f7f.jpg'),
(51, 'Wiper Lantai', 'Perabot', 36, '394d1036fe18a6609c5441bfb20d467a.jpeg'),
(52, 'Sikat Cuci Kayu', 'Perabot', 88, '74471645ffffe5d6cd0aced1f755d131.jpeg'),
(53, 'Sikat Cuci Pelastk', 'Perabot', 78, '3bd76760105888c0739875c01f1776a3.jpg'),
(54, 'Sikat Lantai', 'Perabot', 32, '5dd412902244519381e5fccae8b7afbd.jpg'),
(55, 'Sikat WC', 'Perabot', 23, '643c123ad63c41d0d3e0880f06ac57a8.jpg'),
(56, 'Gayung', 'Perabot', 122, '79589c9956ea2d30f6f82f1a89662bc9.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pp`
--

CREATE TABLE `pp` (
  `idpp` int(11) NOT NULL,
  `idbarang` int(11) NOT NULL,
  `nopp` varchar(244) DEFAULT NULL,
  `tanggal` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `keterangan` text DEFAULT NULL,
  `qty` bigint(20) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'PENDING',
  `penerima` varchar(244) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `pp`
--

INSERT INTO `pp` (`idpp`, `idbarang`, `nopp`, `tanggal`, `keterangan`, `qty`, `status`, `penerima`) VALUES
(3, 47, '', '2025-06-15 03:45:17', 'kirim ke Toko Parahita', 12, 'DITERIMA', 'Toko Parahita'),
(4, 48, '', '2025-06-15 03:45:13', 'kirim ke Toko Parahita', 6, 'DITERIMA', 'Toko Parahita'),
(5, 49, '', '2025-06-15 03:45:06', 'kirim ke Toko Parahita', 6, 'DITERIMA', 'Toko Parahita'),
(6, 50, '', '2025-06-15 03:45:02', 'kirim ke Toko Parahita', 5, 'DITERIMA', 'Toko Parahita'),
(7, 51, '', '2025-06-15 03:44:59', 'kirim ke Toko Parahita', 4, 'DITERIMA', 'Toko Parahita'),
(8, 52, '', '2025-06-15 03:44:56', 'kirim ke Toko Parahita', 2, 'DITERIMA', 'Toko Parahita'),
(9, 55, '', '2025-06-15 03:44:53', 'kirim ke Toko Parahita', 20, 'DITERIMA', 'Toko Parahita'),
(10, 54, '', '2025-06-15 03:44:50', 'kirim ke Toko Parahita', 14, 'DITERIMA', 'Toko Parahita'),
(11, 53, '', '2025-06-15 03:44:46', 'kirim ke Toko Parahita', 30, 'DITERIMA', 'Toko Parahita'),
(12, 47, '', '2025-06-15 09:29:03', 'Kirim Ke Toko Serpong', 15, 'DITERIMA', 'Toko Serpong'),
(13, 48, '', '2025-06-15 09:29:00', '', 4, 'DITERIMA', 'Toko Serpong'),
(14, 49, '', '2025-06-15 09:28:56', 'Kirim Ke Toko Serpong', 4, 'DITERIMA', 'Toko Serpong'),
(15, 50, '', '2025-06-15 09:28:53', 'Kirim Ke Toko Serpong', 4, 'DITERIMA', 'Toko Serpong'),
(16, 51, '', '2025-06-15 09:28:50', 'Kirim Ke Toko Serpong', 3, 'DITERIMA', 'Toko Serpong'),
(17, 52, '', '2025-06-15 09:28:46', 'Kirim Ke Toko Serpong', 2, 'DITERIMA', 'Toko Serpong'),
(18, 55, '', '2025-06-15 09:28:43', 'Kirim Ke Toko Serpong', 15, 'DITERIMA', 'Toko Serpong'),
(19, 54, '', '2025-06-15 09:28:40', 'Kirim Ke Toko Serpong', 20, 'DITERIMA', 'Toko Serpong'),
(20, 53, '', '2025-06-15 09:28:36', 'Kirim Ke Toko Serpong', 31, 'DITERIMA', 'Toko Serpong'),
(21, 10, '', '2025-06-18 01:25:20', 'kirim', 4, 'PENDING', 'Toko Parahita');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pusatkeluar`
--

CREATE TABLE `pusatkeluar` (
  `idkeluar` int(11) NOT NULL,
  `idbarang` int(11) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `penerima` varchar(50) NOT NULL,
  `qty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `pusatkeluar`
--

INSERT INTO `pusatkeluar` (`idkeluar`, `idbarang`, `tanggal`, `penerima`, `qty`) VALUES
(4, 31, '2025-06-10 12:54:15', 'Penjual', 2),
(5, 37, '2025-06-16 02:39:02', 'Pembeli ', 5),
(6, 34, '2025-06-03 15:34:29', 'Pembeli', 6),
(7, 22, '2025-06-03 15:36:34', 'Pembeli', 2),
(8, 11, '2025-06-04 16:31:20', 'pembeli', 1),
(9, 34, '2025-06-04 16:31:36', 'pembeli', 3),
(10, 31, '2025-06-04 16:33:18', 'pembeli', 1),
(11, 35, '2025-06-04 16:33:29', 'pembeli', 6),
(15, 20, '2025-06-07 13:36:13', 'yohir', 19),
(16, 37, '2025-06-15 02:50:16', 'Pembeli', 3),
(17, 22, '2025-06-15 02:50:52', 'Pembeli', 1),
(18, 53, '2025-06-15 03:44:21', 'Pembeli', 1),
(19, 53, '2025-06-15 03:44:46', 'Toko Parahita', 30),
(20, 54, '2025-06-15 03:44:50', 'Toko Parahita', 14),
(21, 55, '2025-06-15 03:44:53', 'Toko Parahita', 20),
(22, 52, '2025-06-15 03:44:56', 'Toko Parahita', 2),
(23, 51, '2025-06-15 03:44:59', 'Toko Parahita', 4),
(24, 50, '2025-06-15 03:45:02', 'Toko Parahita', 5),
(25, 49, '2025-06-15 03:45:06', 'Toko Parahita', 6),
(26, 48, '2025-06-15 03:45:13', 'Toko Parahita', 6),
(27, 47, '2025-06-15 03:45:17', 'Toko Parahita', 12),
(28, 63, '2025-06-15 04:45:30', 'Pembeli', 1),
(29, 53, '2025-06-15 09:28:36', 'Toko Serpong', 31),
(30, 54, '2025-06-15 09:28:40', 'Toko Serpong', 20),
(31, 55, '2025-06-15 09:28:43', 'Toko Serpong', 15),
(32, 52, '2025-06-15 09:28:46', 'Toko Serpong', 2),
(33, 51, '2025-06-15 09:28:50', 'Toko Serpong', 3),
(34, 50, '2025-06-15 09:28:53', 'Toko Serpong', 4),
(35, 49, '2025-06-15 09:28:56', 'Toko Serpong', 4),
(36, 48, '2025-06-15 09:29:00', 'Toko Serpong', 4),
(37, 47, '2025-06-15 09:29:03', 'Toko Serpong', 15);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pusatmasuk`
--

CREATE TABLE `pusatmasuk` (
  `idmasuk` int(11) NOT NULL,
  `idbarang` int(11) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `keterangan` varchar(50) NOT NULL,
  `qty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `pusatmasuk`
--

INSERT INTO `pusatmasuk` (`idmasuk`, `idbarang`, `tanggal`, `keterangan`, `qty`) VALUES
(22, 47, '2025-06-15 02:43:54', 'Sales AA Jaya (Yudi)', 36),
(23, 48, '2025-06-15 02:44:26', 'Sales AA Jaya (Yudi)', 18),
(24, 49, '2025-06-15 02:44:47', 'Sales AA Jaya (Yudi)', 18),
(25, 50, '2025-06-15 02:45:23', 'Sales AA Jaya (Yudi)', 16),
(26, 51, '2025-06-15 02:45:55', 'Sales AA Jaya (Yudi)', 12),
(27, 52, '2025-06-15 02:46:29', 'Sales AA Jaya (Yudi)', 7),
(28, 53, '2025-06-15 02:47:42', 'Sales AA Jaya (Yudi)', 120),
(29, 54, '2025-06-15 02:48:38', 'Sales AA Jaya (Yudi)', 72),
(30, 55, '2025-06-15 02:49:18', 'Sales AA Jaya (Yudi)', 60);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pusatstock`
--

CREATE TABLE `pusatstock` (
  `idbarang` int(11) NOT NULL,
  `namabarang` varchar(50) NOT NULL,
  `deskripsi` varchar(50) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `image` varchar(99) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `pusatstock`
--

INSERT INTO `pusatstock` (`idbarang`, `namabarang`, `deskripsi`, `stock`, `image`) VALUES
(10, 'Antena tv kecil', 'Elektronik', 17, 'ac4d7b5faa13f42ad26b98ba46dd19f9.jpg'),
(11, 'Antena tv Panjang', 'Elektronik', 14, '7875ad3fd9ceac06da765d956b718566.jpg'),
(12, 'Kipas Angin Wall Fan 16\"', 'Elektronik', 21, '9042be155f0f6c038ccd28aaf82d20c7.jpg'),
(13, 'Kipas Angin Stand Fan 16\"', 'Elektronik', 20, 'feca189a4c4f74a2afa2ee57a2f491db.jpg'),
(14, 'Kipas Angin Karakter', 'Elektronik', 25, '5f166170f6248b5d626fc221bcd53180.jpg'),
(15, 'Magic com 1,2L (kecil)', 'Elektronik', 12, 'e2be5a9a1b7c2c0ab7f264462704ccfe.jpg'),
(16, 'Magic com 1,8L', 'Elektronik', 10, '6b4a24a740dcd67591295ac64da2be58.jpg'),
(17, 'Dispenser Panas, Normal', 'Elektronik', 8, '74fd5204abd0de8ebd9c1f826bc74a01.jpg'),
(18, 'Dispenser Panas, Dingin ', 'Elektronik', 10, '9d258c6c1d74d45b153ae85887dff209.jpg'),
(19, 'Colokan Gepeng ', 'Elektronik', 130, '5f8030e3cf0d0447f675ebae715bd6ce.jpg'),
(20, 'Colokan Bulat', 'Elektronik', 106, 'b5b5fc492c843d4d1029f6bc96fd75d9.jpg'),
(21, 'Piting Lampu Gantung', 'Elektronik', 60, 'a44e075f05486dc5e03c4db1d4f0a1ff.jpg'),
(22, 'Lampu 10 w', 'Elektronik', 127, '86009b853c86e27c6585d189302ade4d.jpg'),
(23, 'Lampu 20 w', 'Elektronik', 100, '72dbeff2df96afc3e19d18c605f6a636.jpg'),
(24, 'Lampu 30 w', 'Elektronik', 80, '9eb05007ca4d471fffe213adaf659e3a.jpg'),
(25, 'Rollan 3 Lubang, 3m', 'Elektronik', 14, '4b1f45c33a4d2fdb01f8e9b22f0538f8.jpg'),
(26, 'Rollan 2 Lubang, 3m', 'Elektronik', 12, '3861ec791cc7d626735bf65ee34cd531.jpg'),
(27, 'Rollan 4 Lubang, 5m', 'Elektronik', 16, '65174b32f819742a4446aacfb70395af.jpg'),
(28, 'Stop Kontak 2 Lubang', 'Elektronik', 30, '8fa61a42db8cac82d17f9e090e8eaa39.jpg'),
(29, 'Stop Kontak 4 Lubang', 'Elektronik', 33, 'd2ae6d7fc7fc8dd915921fc43b4516fe.jpeg'),
(30, 'Regulator selang', 'Elektronik', 8, '0d9185d59fb0cfb355675e62b147a31d.jpg'),
(31, 'Sendok Makan', 'Perabot', 19, 'c8c0809556f7f03e12387589a84b3450.jpg'),
(32, 'Sendok Kecil', 'Perabot', 26, '73586a3f4bd8a2c83c77f040c37d59d3.jpeg'),
(33, 'Garpu', 'Perabot', 19, '398ef493598b810bd137030efc1ad26d.jpg'),
(34, 'Gelas Pelastik', 'Perabot', 42, '37cefce454a9dcab9638ca02dbd30b7e.jpg'),
(35, 'Gelas Kopi Kacil', 'Perabot', 27, '5f98e9d653ae999efd4979b61a2c433a.jpg'),
(36, 'Gelas Kaca Besar', 'Perabot', 28, '214eba037e6b15345fdc1bfac06cd950.jpg'),
(37, 'Piring Plastik', 'Perabot', 35, 'bcc548fa5db0bc65b2918dfdd3f1f756.jpg'),
(38, 'Piring Melamin', 'Perabot', 45, '583bc0cb955f80d0372890b27770fefb.jpg'),
(39, 'Piring Keramik', 'Perabot', 48, 'e4bf4a984511f446c9925dd778155730.jpg'),
(40, 'Mangkok Keramik', 'Perabot', 41, 'a9eaa9fca3cbbabe6a7dfc1c7e9ca6e2.jpg'),
(47, 'Box CB 25', 'Perabot', 10, '5ee6037b4e448ccbdc4ea92d92ab66f7.jpeg'),
(48, 'Box CB 45', 'Perabot', 9, 'c2fc53713de93a5bcdadff3776bb34fe.jpeg'),
(49, 'Box CB 60', 'Perabot', 9, '4c84fe54ad819410cdc3fb3e2793c490.jpeg'),
(50, 'Box CB 82', 'Perabot', 8, 'b7a858dadf7e7b0f93cb02dd198a94bf.jpeg'),
(51, 'Box CB 95', 'Perabot', 6, '9ce0fbe5bc093bf3c1c7e34a8075cfcf.jpeg'),
(52, 'Box CB 130', 'Perabot', 4, '762e29bd292ea252ee65158786212490.jpeg'),
(53, 'Tempat Donat SL', 'Perabot', 59, '7cfbde7fd384ed82ad16a748ab37195c.jpeg'),
(54, 'Tempat Donat M', 'Perabot', 39, 'c0a5871a82e49899531a017df17b7da7.jpeg'),
(55, 'Tempat Donat L', 'Perabot', 26, '10f0f0a370d91e5a1f42353118a83c67.jpeg'),
(56, 'Sapu ijuk', 'Perabot', 68, 'a1b9b9aa26bbe4226c379b04cfe7dd86.png'),
(57, 'Sapu Lantai', 'Perabot', 54, '55dfbe4f7aec95c94b8764c679e35f24.jpg'),
(58, 'Sapu Lidi Gagang', 'Perabot', 32, '42513c38b152ac132c31fbc8984a099c.jpeg'),
(59, 'Sapu Lidi', 'Perabot', 24, 'cf0bf7184d6b401dcf10a898b7a733bf.jpg'),
(60, 'Sapu Lidi Kasur', 'Perabot', 36, 'a9bb606bad1cb836cbe279a212181ef1.jpg'),
(61, 'Pelan', 'Perabot', 62, '9bb2a2df39607c329a7201e84bdc4f7f.jpg'),
(62, 'Wiper Lantai', 'Perabot', 48, '394d1036fe18a6609c5441bfb20d467a.jpeg'),
(63, 'Gayung', 'Perabot', 125, '79589c9956ea2d30f6f82f1a89662bc9.jpg'),
(64, 'Sikat Lantai', 'Perabot', 36, '5dd412902244519381e5fccae8b7afbd.jpg'),
(65, 'Sikat WC', 'Perabot', 24, '643c123ad63c41d0d3e0880f06ac57a8.jpg'),
(66, 'Sikat Cuci Kayu', 'Perabot', 122, '74471645ffffe5d6cd0aced1f755d131.jpeg'),
(67, 'Sikat Cuci Pelastk', 'Perabot', 87, '3bd76760105888c0739875c01f1776a3.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `serpong_keluar`
--

CREATE TABLE `serpong_keluar` (
  `idkeluar` int(11) NOT NULL,
  `idbarang` int(11) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `penerima` varchar(50) NOT NULL,
  `qty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `serpong_keluar`
--

INSERT INTO `serpong_keluar` (`idkeluar`, `idbarang`, `tanggal`, `penerima`, `qty`) VALUES
(1, 5, '2025-06-03 15:35:41', 'Pembeli', 1),
(2, 28, '2025-06-03 15:50:49', 'Pembeli', 1),
(3, 28, '2025-06-03 16:06:15', 'Pembeli', 1),
(4, 7, '2025-06-03 16:07:21', 'Pembeli', 2),
(5, 24, '2025-06-03 17:16:10', 'pembeli', 12),
(6, 17, '2025-06-04 16:21:57', 'pembeli', 1),
(7, 23, '2025-06-04 16:22:24', 'pembeli', 6),
(8, 34, '2025-06-04 16:28:08', 'pembeli', 1),
(9, 32, '2025-06-04 16:42:49', 'pembeli', 1),
(10, 17, '2025-06-15 02:54:22', 'Pembeli', 1),
(11, 14, '2025-06-15 02:54:44', 'Pembeli', 3),
(12, 18, '2025-06-15 03:52:37', 'Pembeli', 1),
(13, 19, '2025-06-15 03:52:52', 'Pembeli', 1),
(14, 37, '2025-06-15 04:41:47', 'Pembeli', 1),
(15, 42, '2025-06-15 04:42:03', 'Pembeli', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `serpong_masuk`
--

CREATE TABLE `serpong_masuk` (
  `idmasuk` int(11) NOT NULL,
  `idbarang` int(11) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `keterangan` varchar(50) NOT NULL,
  `qty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `serpong_masuk`
--

INSERT INTO `serpong_masuk` (`idmasuk`, `idbarang`, `tanggal`, `keterangan`, `qty`) VALUES
(2, 47, '2025-06-15 09:28:36', 'Diterima dari Gudang Pusat (PP ID: 20)', 31),
(3, 48, '2025-06-15 09:28:40', 'Diterima dari Gudang Pusat (PP ID: 19)', 20),
(4, 49, '2025-06-15 09:28:43', 'Diterima dari Gudang Pusat (PP ID: 18)', 15),
(5, 50, '2025-06-15 09:28:46', 'Diterima dari Gudang Pusat (PP ID: 17)', 2),
(6, 51, '2025-06-15 09:28:50', 'Diterima dari Gudang Pusat (PP ID: 16)', 3),
(7, 52, '2025-06-15 09:28:53', 'Diterima dari Gudang Pusat (PP ID: 15)', 4),
(8, 53, '2025-06-15 09:28:56', 'Diterima dari Gudang Pusat (PP ID: 14)', 4),
(9, 54, '2025-06-15 09:29:00', 'Diterima dari Gudang Pusat (PP ID: 13)', 4),
(10, 55, '2025-06-15 09:29:03', 'Diterima dari Gudang Pusat (PP ID: 12)', 15);

-- --------------------------------------------------------

--
-- Struktur dari tabel `serpong_stock`
--

CREATE TABLE `serpong_stock` (
  `idbarang` int(11) NOT NULL,
  `namabarang` varchar(50) NOT NULL,
  `deskripsi` varchar(50) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `image` varchar(99) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `serpong_stock`
--

INSERT INTO `serpong_stock` (`idbarang`, `namabarang`, `deskripsi`, `stock`, `image`) VALUES
(5, 'Antena tv kecil', 'Elektronik', 10, 'b22def2dc668713fbc81cc70bba5c066.jpg'),
(6, 'Antena tv Panjang', 'Elektronik', 12, '7875ad3fd9ceac06da765d956b718566.jpg'),
(7, 'Colokan Bulat', 'Elektronik', 98, 'b5b5fc492c843d4d1029f6bc96fd75d9.jpg'),
(8, 'Colokan Gepeng ', 'Elektronik', 112, '5f8030e3cf0d0447f675ebae715bd6ce.jpg'),
(9, 'Dispenser Panas, Dingin ', 'Elektronik', 3, '9d258c6c1d74d45b153ae85887dff209.jpg'),
(10, 'Dispenser Panas, Normal', 'Elektronik', 5, '74fd5204abd0de8ebd9c1f826bc74a01.jpg'),
(11, 'Garpu', 'Perabot', 19, '398ef493598b810bd137030efc1ad26d.jpg'),
(12, 'Gelas Kaca Besar', 'Perabot', 19, '214eba037e6b15345fdc1bfac06cd950.jpg'),
(13, 'Gelas Kopi Kacil', 'Perabot', 30, '5f98e9d653ae999efd4979b61a2c433a.jpg'),
(14, 'Gelas Pelastik', 'Perabot', 38, '37cefce454a9dcab9638ca02dbd30b7e.jpg'),
(15, 'Kipas Angin Karakter', 'Elektronik', 15, '5f166170f6248b5d626fc221bcd53180.jpg'),
(16, 'Kipas Angin Stand Fan 16\"', 'Elektronik', 13, 'feca189a4c4f74a2afa2ee57a2f491db.jpg'),
(17, 'Kipas Angin Wall Fan 16\"', 'Elektronik', 14, '9042be155f0f6c038ccd28aaf82d20c7.jpg'),
(18, 'Lampu 10 w', 'Elektronik', 121, '86009b853c86e27c6585d189302ade4d.jpg'),
(19, 'Lampu 20 w', 'Elektronik', 68, '72dbeff2df96afc3e19d18c605f6a636.jpg'),
(20, 'Lampu 30 w', 'Elektronik', 22, '9eb05007ca4d471fffe213adaf659e3a.jpg'),
(21, 'Magic com 1,2L (kecil)', 'Elektronik', 5, 'e2be5a9a1b7c2c0ab7f264462704ccfe.jpg'),
(22, 'Magic com 1,8L', 'Elektronik', 3, '6b4a24a740dcd67591295ac64da2be58.jpg'),
(23, 'Mangkok Keramik', 'Perabot', 28, 'a9eaa9fca3cbbabe6a7dfc1c7e9ca6e2.jpg'),
(24, 'Piring Keramik', 'Perabot', 29, 'e4bf4a984511f446c9925dd778155730.jpg'),
(25, 'Piring Melamin', 'Perabot', 33, '583bc0cb955f80d0372890b27770fefb.jpg'),
(26, 'Piring Plastik', 'Perabot', 43, 'bcc548fa5db0bc65b2918dfdd3f1f756.jpg'),
(27, 'Piting Lampu Gantung', 'Elektronik', 45, 'a44e075f05486dc5e03c4db1d4f0a1ff.jpg'),
(28, 'Regulator selang', 'Elektronik', 2, '0d9185d59fb0cfb355675e62b147a31d.jpg'),
(29, 'Rollan 2 Lubang, 3m', 'Elektronik', 7, '3861ec791cc7d626735bf65ee34cd531.jpg'),
(30, 'Rollan 3 Lubang, 3m', 'Elektronik', 11, '4b1f45c33a4d2fdb01f8e9b22f0538f8.jpg'),
(31, 'Rollan 4 Lubang, 5m', 'Elektronik', 13, '65174b32f819742a4446aacfb70395af.jpg'),
(32, 'Sendok Kecil', 'Perabot', 21, '8364e9481e8838d025d88dd0fbac3505.jpg'),
(33, 'Sendok Makan', 'Perabot', 21, 'c8c0809556f7f03e12387589a84b3450.jpg'),
(34, 'Stop Kontak 2 Lubang', 'Elektronik', 22, '8fa61a42db8cac82d17f9e090e8eaa39.jpg'),
(35, 'Stop Kontak 4 Lubang', 'Elektronik', 32, '65c26fabb6cde1c80e2fd048909e8fb4.jpg'),
(36, 'Gayung', 'Perabot', 68, '79589c9956ea2d30f6f82f1a89662bc9.jpg'),
(37, 'Sapu ijuk', 'Perabot', 57, 'a1b9b9aa26bbe4226c379b04cfe7dd86.png'),
(38, 'Sapu Lantai', 'Perabot', 36, '55dfbe4f7aec95c94b8764c679e35f24.jpg'),
(39, 'Sapu Lidi', 'Perabot', 16, 'cf0bf7184d6b401dcf10a898b7a733bf.jpg'),
(40, 'Sapu Lidi Gagang', 'Perabot', 22, '42513c38b152ac132c31fbc8984a099c.jpeg'),
(41, 'Sapu Lidi Kasur', 'Perabot', 19, 'a9bb606bad1cb836cbe279a212181ef1.jpg'),
(42, 'Sikat Cuci Kayu', 'Perabot', 63, '74471645ffffe5d6cd0aced1f755d131.jpeg'),
(43, 'Sikat Cuci Pelastk', 'Perabot', 66, '3bd76760105888c0739875c01f1776a3.jpg'),
(44, 'Sikat Lantai', 'Perabot', 22, '5dd412902244519381e5fccae8b7afbd.jpg'),
(45, 'Sikat WC', 'Perabot', 15, '643c123ad63c41d0d3e0880f06ac57a8.jpg'),
(46, 'Wiper Lantai', 'Perabot', 33, '394d1036fe18a6609c5441bfb20d467a.jpeg'),
(47, 'Tempat Donat SL', 'Perabot', 31, '7cfbde7fd384ed82ad16a748ab37195c.jpeg'),
(48, 'Tempat Donat M', 'Perabot', 20, 'c0a5871a82e49899531a017df17b7da7.jpeg'),
(49, 'Tempat Donat L', 'Perabot', 15, '10f0f0a370d91e5a1f42353118a83c67.jpeg'),
(50, 'Box CB 130', 'Perabot', 2, '762e29bd292ea252ee65158786212490.jpeg'),
(51, 'Box CB 95', 'Perabot', 3, '9ce0fbe5bc093bf3c1c7e34a8075cfcf.jpeg'),
(52, 'Box CB 82', 'Perabot', 4, 'b7a858dadf7e7b0f93cb02dd198a94bf.jpeg'),
(53, 'Box CB 60', 'Perabot', 4, '4c84fe54ad819410cdc3fb3e2793c490.jpeg'),
(54, 'Box CB 45', 'Perabot', 4, 'c2fc53713de93a5bcdadff3776bb34fe.jpeg'),
(55, 'Box CB 25', 'Perabot', 15, '5ee6037b4e448ccbdc4ea92d92ab66f7.jpeg');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `login`
--
ALTER TABLE `login`
  ADD PRIMARY KEY (`iduser`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indeks untuk tabel `parahita_keluar`
--
ALTER TABLE `parahita_keluar`
  ADD PRIMARY KEY (`idkeluar`),
  ADD KEY `fk_parahita_keluar_barang` (`idbarang`);

--
-- Indeks untuk tabel `parahita_masuk`
--
ALTER TABLE `parahita_masuk`
  ADD PRIMARY KEY (`idmasuk`),
  ADD KEY `fk_parahita_masuk_barang` (`idbarang`);

--
-- Indeks untuk tabel `parahita_stock`
--
ALTER TABLE `parahita_stock`
  ADD PRIMARY KEY (`idbarang`);

--
-- Indeks untuk tabel `pp`
--
ALTER TABLE `pp`
  ADD PRIMARY KEY (`idpp`),
  ADD KEY `fk_pp_barang` (`idbarang`);

--
-- Indeks untuk tabel `pusatkeluar`
--
ALTER TABLE `pusatkeluar`
  ADD PRIMARY KEY (`idkeluar`),
  ADD KEY `fk_pusat_keluar_barang` (`idbarang`);

--
-- Indeks untuk tabel `pusatmasuk`
--
ALTER TABLE `pusatmasuk`
  ADD PRIMARY KEY (`idmasuk`),
  ADD KEY `fk_pusat_masuk_barang` (`idbarang`);

--
-- Indeks untuk tabel `pusatstock`
--
ALTER TABLE `pusatstock`
  ADD PRIMARY KEY (`idbarang`);

--
-- Indeks untuk tabel `serpong_keluar`
--
ALTER TABLE `serpong_keluar`
  ADD PRIMARY KEY (`idkeluar`),
  ADD KEY `fk_serpong_keluar_barang` (`idbarang`);

--
-- Indeks untuk tabel `serpong_masuk`
--
ALTER TABLE `serpong_masuk`
  ADD PRIMARY KEY (`idmasuk`),
  ADD KEY `fk_serpong_masuk_barang` (`idbarang`);

--
-- Indeks untuk tabel `serpong_stock`
--
ALTER TABLE `serpong_stock`
  ADD PRIMARY KEY (`idbarang`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `login`
--
ALTER TABLE `login`
  MODIFY `iduser` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `parahita_keluar`
--
ALTER TABLE `parahita_keluar`
  MODIFY `idkeluar` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT untuk tabel `parahita_masuk`
--
ALTER TABLE `parahita_masuk`
  MODIFY `idmasuk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `parahita_stock`
--
ALTER TABLE `parahita_stock`
  MODIFY `idbarang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT untuk tabel `pp`
--
ALTER TABLE `pp`
  MODIFY `idpp` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `pusatkeluar`
--
ALTER TABLE `pusatkeluar`
  MODIFY `idkeluar` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT untuk tabel `pusatmasuk`
--
ALTER TABLE `pusatmasuk`
  MODIFY `idmasuk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT untuk tabel `pusatstock`
--
ALTER TABLE `pusatstock`
  MODIFY `idbarang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT untuk tabel `serpong_keluar`
--
ALTER TABLE `serpong_keluar`
  MODIFY `idkeluar` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `serpong_masuk`
--
ALTER TABLE `serpong_masuk`
  MODIFY `idmasuk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `serpong_stock`
--
ALTER TABLE `serpong_stock`
  MODIFY `idbarang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `parahita_keluar`
--
ALTER TABLE `parahita_keluar`
  ADD CONSTRAINT `fk_parahita_keluar_barang` FOREIGN KEY (`idbarang`) REFERENCES `parahita_stock` (`idbarang`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `parahita_masuk`
--
ALTER TABLE `parahita_masuk`
  ADD CONSTRAINT `fk_parahita_masuk_barang` FOREIGN KEY (`idbarang`) REFERENCES `parahita_stock` (`idbarang`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pp`
--
ALTER TABLE `pp`
  ADD CONSTRAINT `fk_pp_barang` FOREIGN KEY (`idbarang`) REFERENCES `pusatstock` (`idbarang`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pusatkeluar`
--
ALTER TABLE `pusatkeluar`
  ADD CONSTRAINT `fk_pusat_keluar_barang` FOREIGN KEY (`idbarang`) REFERENCES `pusatstock` (`idbarang`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pusatmasuk`
--
ALTER TABLE `pusatmasuk`
  ADD CONSTRAINT `fk_pusat_masuk_barang` FOREIGN KEY (`idbarang`) REFERENCES `pusatstock` (`idbarang`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `serpong_keluar`
--
ALTER TABLE `serpong_keluar`
  ADD CONSTRAINT `fk_serpong_keluar_barang` FOREIGN KEY (`idbarang`) REFERENCES `serpong_stock` (`idbarang`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `serpong_masuk`
--
ALTER TABLE `serpong_masuk`
  ADD CONSTRAINT `fk_serpong_masuk_barang` FOREIGN KEY (`idbarang`) REFERENCES `serpong_stock` (`idbarang`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
