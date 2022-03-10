-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 30, 2022 at 02:29 AM
-- Server version: 10.4.19-MariaDB
-- PHP Version: 7.4.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tempat`
--

-- --------------------------------------------------------

--
-- Table structure for table `kampus`
--

CREATE TABLE `kampus` (
  `id_kampus` int(11) NOT NULL,
  `namaKampus` varchar(30) NOT NULL,
  `alamatKampus` varchar(255) NOT NULL,
  `id_kota` int(11) NOT NULL,
  `id_kecamatan` int(11) NOT NULL,
  `id_kelurahan` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `kampus`
--

INSERT INTO `kampus` (`id_kampus`, `namaKampus`, `alamatKampus`, `id_kota`, `id_kecamatan`, `id_kelurahan`) VALUES
(5, 'Politeknik Pos Indonesia', 'Jl. Sarimanah teuing nu belah mana', 1, 1, 1),
(6, 'Universigat Gadjah Mada', 'Bulaksumur', 5, 8, 9),
(12, 'Universigat Airlangga', 'Jl.Airlangga', 6, 6, 8),
(13, 'Universitar Hasanudin', 'Jl.Perintis Kemerdekaan', 8, 9, 10),
(14, 'Universitar Andalas', 'Jl.Andalas', 9, 10, 11),
(15, 'Universitar Padjajaran', 'Jl.Bandung Sumedang', 10, 12, 12),
(16, 'Universitar Diponegoro', 'Jl.Prof Sudarto', 11, 13, 13),
(17, 'Universitar Sriwijaya', 'Jl.Masjid Al Gazali', 12, 14, 14),
(18, 'Universitar Lambung Mangkurat', 'Jl.Brigjen H Hasan', 13, 15, 15),
(19, 'Universitar Pendidikan Indones', 'Jl.Dr.Setiabudi', 1, 1, 16),
(20, 'Universitar Negeri Surabaya', 'Jl.Lidah Weta', 6, 16, 17),
(21, 'Insitu Teknologi Bandung', 'Jl.Ganesa', 1, 17, 18);

-- --------------------------------------------------------

--
-- Table structure for table `kecamatan`
--

CREATE TABLE `kecamatan` (
  `id_kecamatan` int(11) NOT NULL,
  `kode_pos` int(10) NOT NULL,
  `namaKecamatan` varchar(30) NOT NULL,
  `id_kota` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `kecamatan`
--

INSERT INTO `kecamatan` (`id_kecamatan`, `kode_pos`, `namaKecamatan`, `id_kota`) VALUES
(1, 40151, 'Sukasari', 1),
(2, 55211, 'Danurejan', 2),
(4, 40255, 'Regol', 1),
(6, 4100, 'Gubeng', 6),
(8, 42000, 'Depok', 5),
(9, 43000, 'Tamalanrea', 8),
(10, 44000, 'Pauh', 9),
(12, 45000, 'Jatinangor', 10),
(13, 46000, 'Tembalang', 11),
(14, 47000, 'Ilir Bar.I', 12),
(15, 48000, 'Banjarmasin Utara', 13),
(16, 41100, 'Lakarsantri', 6),
(17, 41200, 'Coblong', 1);

-- --------------------------------------------------------

--
-- Table structure for table `kelurahan`
--

CREATE TABLE `kelurahan` (
  `id_kelurahan` int(11) NOT NULL,
  `namaKelurahan` varchar(30) NOT NULL,
  `id_kecamatan` int(11) NOT NULL,
  `id_kota` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `kelurahan`
--

INSERT INTO `kelurahan` (`id_kelurahan`, `namaKelurahan`, `id_kecamatan`, `id_kota`) VALUES
(1, 'Sarijadi', 1, 1),
(2, 'Bausasran', 2, 2),
(4, 'Ciseureuh', 4, 1),
(8, 'Airlangga', 6, 6),
(9, 'Caturtunggal', 8, 5),
(10, 'Tamalanrea Indah', 9, 8),
(11, 'Limau Manis', 10, 9),
(12, 'Hegarmanah', 12, 10),
(13, 'Tembakau', 13, 11),
(14, 'Bukit Lama', 14, 12),
(15, 'Pangeran', 15, 13),
(16, 'Isola', 1, 1),
(17, 'Lidah Wetan', 16, 6),
(18, 'Siliwangi', 17, 1);

-- --------------------------------------------------------

--
-- Table structure for table `kota`
--

CREATE TABLE `kota` (
  `id_kota` int(11) NOT NULL,
  `namaKota` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `kota`
--

INSERT INTO `kota` (`id_kota`, `namaKota`) VALUES
(1, 'Bandung'),
(2, 'Yogyakarta'),
(5, 'Sleman'),
(6, 'Surabaya'),
(8, 'Makasar'),
(9, 'Padang'),
(10, 'Sumedang'),
(11, 'Semarang'),
(12, 'Palembang'),
(13, 'Banjarmasin');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id_user` int(11) NOT NULL,
  `username` varchar(30) NOT NULL,
  `email` varchar(30) NOT NULL,
  `password` varchar(30) NOT NULL,
  `api_key` varchar(30) NOT NULL,
  `hit` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id_user`, `username`, `email`, `password`, `api_key`, `hit`) VALUES
(1, 'ucup', 'ucup@gmail.com', '123', '123', 241),
(2, 'faisal', 'faisal@gmail.com', '123', '', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `kampus`
--
ALTER TABLE `kampus`
  ADD PRIMARY KEY (`id_kampus`),
  ADD KEY `id_kota_fk3` (`id_kota`),
  ADD KEY `id_kecamatan_fk3` (`id_kecamatan`),
  ADD KEY `id_kelurahan_fk3` (`id_kelurahan`);

--
-- Indexes for table `kecamatan`
--
ALTER TABLE `kecamatan`
  ADD PRIMARY KEY (`id_kecamatan`),
  ADD KEY `id_kota_fk` (`id_kota`);

--
-- Indexes for table `kelurahan`
--
ALTER TABLE `kelurahan`
  ADD PRIMARY KEY (`id_kelurahan`),
  ADD KEY `id_kecamatan_fk1` (`id_kecamatan`),
  ADD KEY `id_kota_fk1` (`id_kota`);

--
-- Indexes for table `kota`
--
ALTER TABLE `kota`
  ADD PRIMARY KEY (`id_kota`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `kampus`
--
ALTER TABLE `kampus`
  MODIFY `id_kampus` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `kecamatan`
--
ALTER TABLE `kecamatan`
  MODIFY `id_kecamatan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `kelurahan`
--
ALTER TABLE `kelurahan`
  MODIFY `id_kelurahan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `kota`
--
ALTER TABLE `kota`
  MODIFY `id_kota` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `kampus`
--
ALTER TABLE `kampus`
  ADD CONSTRAINT `id_kecamatan_fk3` FOREIGN KEY (`id_kecamatan`) REFERENCES `kecamatan` (`id_kecamatan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `id_kelurahan_fk3` FOREIGN KEY (`id_kelurahan`) REFERENCES `kelurahan` (`id_kelurahan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `id_kota_fk3` FOREIGN KEY (`id_kota`) REFERENCES `kota` (`id_kota`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `kecamatan`
--
ALTER TABLE `kecamatan`
  ADD CONSTRAINT `id_kota_fk` FOREIGN KEY (`id_kota`) REFERENCES `kota` (`id_kota`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `kelurahan`
--
ALTER TABLE `kelurahan`
  ADD CONSTRAINT `id_kecamatan_fk1` FOREIGN KEY (`id_kecamatan`) REFERENCES `kecamatan` (`id_kecamatan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `id_kota_fk1` FOREIGN KEY (`id_kota`) REFERENCES `kota` (`id_kota`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
