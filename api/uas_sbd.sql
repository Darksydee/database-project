-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 02, 2025 at 09:54 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `uas_sbd`
--

-- --------------------------------------------------------

--
-- Table structure for table `bazaar`
--

CREATE TABLE `bazaar` (
  `BazaarID` int(11) NOT NULL,
  `BazaarName` varchar(100) DEFAULT NULL,
  `BazaarDate` date DEFAULT NULL,
  `LocationID` int(11) DEFAULT NULL,
  `BoothPrice` decimal(10,2) DEFAULT NULL,
  `TotalBoothsSold` int(11) DEFAULT NULL,
  `OrganizerID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bazaar`
--

INSERT INTO `bazaar` (`BazaarID`, `BazaarName`, `BazaarDate`, `LocationID`, `BoothPrice`, `TotalBoothsSold`, `OrganizerID`) VALUES
(1, 'Bazar Kreatif', '2025-06-04', 1, 500000.00, 50, 1),
(2, 'Bazar Ramadhan', '2025-05-25', 2, 750000.00, 75, 2);

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `FeedbackID` int(11) NOT NULL,
  `BazaarID` int(11) DEFAULT NULL,
  `VisitorName` varchar(100) DEFAULT NULL,
  `Comments` text DEFAULT NULL,
  `Rating` int(11) DEFAULT NULL CHECK (`Rating` between 1 and 5),
  `CreatedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`FeedbackID`, `BazaarID`, `VisitorName`, `Comments`, `Rating`, `CreatedAt`) VALUES
(1, 1, 'John Doe', 'Event sangat menarik!', 5, '2025-06-02 03:01:50');

-- --------------------------------------------------------

--
-- Table structure for table `location`
--

CREATE TABLE `location` (
  `LocationID` int(11) NOT NULL,
  `LocationName` varchar(100) DEFAULT NULL,
  `LocationAddress` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `location`
--

INSERT INTO `location` (`LocationID`, `LocationName`, `LocationAddress`) VALUES
(1, 'Pradita University', 'Jl. Scientia Square Park No. 1'),
(2, 'Summarecon Mall Serpong', 'Jl. Boulevard Gading Serpong');

-- --------------------------------------------------------

--
-- Table structure for table `organizer`
--

CREATE TABLE `organizer` (
  `OrganizerID` int(11) NOT NULL,
  `OrganizerName` varchar(100) DEFAULT NULL,
  `OrganizerContact` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `organizer`
--

INSERT INTO `organizer` (`OrganizerID`, `OrganizerName`, `OrganizerContact`) VALUES
(1, 'PT. Bazar Organizer', '08123456789'),
(2, 'Event Pro Indonesia', '082211334455');

-- --------------------------------------------------------

--
-- Table structure for table `paymentmethod`
--

CREATE TABLE `paymentmethod` (
  `PaymentMethodID` int(11) NOT NULL,
  `MethodName` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `paymentmethod`
--

INSERT INTO `paymentmethod` (`PaymentMethodID`, `MethodName`) VALUES
(1, 'Cash'),
(2, 'E-Wallet'),
(3, 'Credit Card');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `ProductID` int(11) NOT NULL,
  `TenantID` int(11) DEFAULT NULL,
  `Name` varchar(100) DEFAULT NULL,
  `Harga` decimal(10,2) DEFAULT NULL,
  `Stok` int(11) DEFAULT NULL,
  `Deskripsi` text DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`ProductID`, `TenantID`, `Name`, `Harga`, `Stok`, `Deskripsi`, `gambar`) VALUES
(1, 1, 'Kopi Arabika Gayo', 25000.00, 100, 'Kopi arabika premium dari Aceh', 'prd_683dd6b3cc0d5.jpg'),
(2, 2, 'Tas Rajut Handmade', 75000.00, 48, 'Tas rajut buatan tangan, motif etnik', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `producttransaction`
--

CREATE TABLE `producttransaction` (
  `ProductTransactionID` int(11) NOT NULL,
  `ProductID` int(11) DEFAULT NULL,
  `UserID` int(11) DEFAULT NULL,
  `Quantity` int(11) DEFAULT NULL,
  `TotalAmount` decimal(10,2) DEFAULT NULL,
  `PaymentMethodID` int(11) DEFAULT NULL,
  `PaymentStatus` varchar(20) DEFAULT 'Pending',
  `TransactionDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `producttransaction`
--

INSERT INTO `producttransaction` (`ProductTransactionID`, `ProductID`, `UserID`, `Quantity`, `TotalAmount`, `PaymentMethodID`, `PaymentStatus`, `TransactionDate`) VALUES
(1, 2, 5, 1, 75000.00, 1, 'Completed', '2025-06-02 02:04:38'),
(2, 1, 5, 1, 25000.00, 1, 'Completed', '2025-06-02 02:11:09'),
(3, 2, 5, 1, 75000.00, 1, 'Completed', '2025-06-02 02:49:02'),
(4, 2, 5, 1, 75000.00, 3, 'Completed', '2025-06-03 02:24:03');

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE `schedule` (
  `JadwalID` int(11) NOT NULL,
  `BazaarID` int(11) DEFAULT NULL,
  `NamaEvent` varchar(100) DEFAULT NULL,
  `WaktuMulai` datetime DEFAULT NULL,
  `WaktuSelesai` datetime DEFAULT NULL,
  `LokasiID` int(11) DEFAULT NULL,
  `Deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule`
--

INSERT INTO `schedule` (`JadwalID`, `BazaarID`, `NamaEvent`, `WaktuMulai`, `WaktuSelesai`, `LokasiID`, `Deskripsi`) VALUES
(1, 1, 'Live Music Performance', '2025-06-04 18:00:00', '2025-06-04 20:00:00', 1, 'Penampilan musik oleh band indie lokal'),
(2, 2, 'Talkshow UMKM', '2025-05-25 14:00:00', '2025-05-25 16:00:00', 2, 'Talkshow inspiratif bersama pelaku UMKM sukses');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `StaffID` int(11) NOT NULL,
  `OrganizerID` int(11) DEFAULT NULL,
  `Nama` varchar(100) DEFAULT NULL,
  `Posisi` varchar(50) DEFAULT NULL,
  `Kontak` varchar(20) DEFAULT NULL,
  `ShiftTime` varchar(50) DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`StaffID`, `OrganizerID`, `Nama`, `Posisi`, `Kontak`, `ShiftTime`, `gambar`) VALUES
(3, 1, 'Fabio Quartararo', 'MotoGP', '080800', '13.00-20.00', 'staff_683df56501d36.jpg'),
(4, 1, 'Denny Caknan', 'Staff', '909090', '18.00 - 20.00', 'staff_683df5b8d6d70.jpg'),
(5, 1, 'Valentino Rossi', 'Coach VR46 Team', '9090900', '08.00 - 12.00', 'staff_683df73f7007a.jpeg');

-- --------------------------------------------------------

--
-- Table structure for table `tenant`
--

CREATE TABLE `tenant` (
  `TenantID` int(11) NOT NULL,
  `BazaarID` int(11) DEFAULT NULL,
  `Nama` varchar(100) DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `Kategori` varchar(50) DEFAULT NULL,
  `Contact` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tenant`
--

INSERT INTO `tenant` (`TenantID`, `BazaarID`, `Nama`, `Description`, `Kategori`, `Contact`) VALUES
(1, 1, 'Warung Kopi Nusantara', 'Menjual berbagai jenis kopi lokal', 'Makanan & Minuman', '081234567890'),
(2, 2, 'Kerajinan Tangan Kita', 'Produk handmade dari pengrajin lokal', 'Kerajinan', '089876543210');

-- --------------------------------------------------------

--
-- Table structure for table `ticket`
--

CREATE TABLE `ticket` (
  `TicketID` int(11) NOT NULL,
  `BazaarID` int(11) DEFAULT NULL,
  `TicketType` varchar(50) DEFAULT NULL,
  `TicketPrice` decimal(10,2) DEFAULT NULL,
  `TotalTicketsSold` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ticket`
--

INSERT INTO `ticket` (`TicketID`, `BazaarID`, `TicketType`, `TicketPrice`, `TotalTicketsSold`) VALUES
(1, 1, 'Tiket Reguler', 20000.00, 100),
(2, 1, 'Tiket VIP', 50000.00, 34),
(3, 2, 'Tiket Reguler', 35000.00, 75),
(4, 2, 'Tiket VIP', 100000.00, 16),
(7, 2, 'Tiket SUPER VIP', 1500000.00, 100);

-- --------------------------------------------------------

--
-- Table structure for table `transaction`
--

CREATE TABLE `transaction` (
  `TransactionID` int(11) NOT NULL,
  `TicketID` int(11) DEFAULT NULL,
  `UserID` int(11) DEFAULT NULL,
  `TransactionDate` datetime DEFAULT NULL,
  `Quantity` int(11) DEFAULT NULL,
  `TotalAmount` decimal(10,2) DEFAULT NULL,
  `PaymentMethodID` int(11) DEFAULT NULL,
  `PaymentStatus` varchar(20) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaction`
--

INSERT INTO `transaction` (`TransactionID`, `TicketID`, `UserID`, `TransactionDate`, `Quantity`, `TotalAmount`, `PaymentMethodID`, `PaymentStatus`) VALUES
(8, 2, 5, '2025-06-02 02:37:36', 1, 50000.00, 1, 'Completed'),
(9, 2, 5, '2025-06-02 13:08:48', 1, 50000.00, 2, 'Completed'),
(10, 4, 5, '2025-06-03 02:23:36', 1, 100000.00, 1, 'Completed');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `UserID` int(11) NOT NULL,
  `Nama` varchar(100) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `NoHandphone` varchar(20) DEFAULT NULL,
  `CreatedAt` datetime DEFAULT current_timestamp(),
  `UpdatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `IsActive` tinyint(1) DEFAULT 1,
  `Role` varchar(20) DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`UserID`, `Nama`, `Email`, `Password`, `NoHandphone`, `CreatedAt`, `UpdatedAt`, `IsActive`, `Role`) VALUES
(1, 'Alice Nuraini', 'alice@example.com', 'hashedpassword123', '081234567890', '2025-06-01 17:16:37', '2025-06-01 17:16:37', 1, 'user'),
(2, 'Budi Prasetyo', 'budi@example.com', 'hashedpassword456', '082233445566', '2025-06-01 17:16:37', '2025-06-01 17:16:37', 1, 'user'),
(5, 'test', 'test@test.com', 'test', '09899', '2025-06-02 01:44:38', '2025-06-03 02:24:37', 1, 'user'),
(8, 'admin', 'admin@admin.com', 'test', '0808', '2025-06-02 13:32:46', '2025-06-02 15:04:42', 1, 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bazaar`
--
ALTER TABLE `bazaar`
  ADD PRIMARY KEY (`BazaarID`),
  ADD KEY `LocationID` (`LocationID`),
  ADD KEY `OrganizerID` (`OrganizerID`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`FeedbackID`),
  ADD KEY `BazaarID` (`BazaarID`);

--
-- Indexes for table `location`
--
ALTER TABLE `location`
  ADD PRIMARY KEY (`LocationID`);

--
-- Indexes for table `organizer`
--
ALTER TABLE `organizer`
  ADD PRIMARY KEY (`OrganizerID`);

--
-- Indexes for table `paymentmethod`
--
ALTER TABLE `paymentmethod`
  ADD PRIMARY KEY (`PaymentMethodID`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`ProductID`),
  ADD KEY `TenantID` (`TenantID`);

--
-- Indexes for table `producttransaction`
--
ALTER TABLE `producttransaction`
  ADD PRIMARY KEY (`ProductTransactionID`),
  ADD KEY `ProductID` (`ProductID`),
  ADD KEY `UserID` (`UserID`),
  ADD KEY `PaymentMethodID` (`PaymentMethodID`);

--
-- Indexes for table `schedule`
--
ALTER TABLE `schedule`
  ADD PRIMARY KEY (`JadwalID`),
  ADD KEY `BazaarID` (`BazaarID`),
  ADD KEY `LokasiID` (`LokasiID`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`StaffID`),
  ADD KEY `OrganizerID` (`OrganizerID`);

--
-- Indexes for table `tenant`
--
ALTER TABLE `tenant`
  ADD PRIMARY KEY (`TenantID`),
  ADD KEY `BazaarID` (`BazaarID`);

--
-- Indexes for table `ticket`
--
ALTER TABLE `ticket`
  ADD PRIMARY KEY (`TicketID`),
  ADD KEY `BazaarID` (`BazaarID`);

--
-- Indexes for table `transaction`
--
ALTER TABLE `transaction`
  ADD PRIMARY KEY (`TransactionID`),
  ADD KEY `TicketID` (`TicketID`),
  ADD KEY `PaymentMethodID` (`PaymentMethodID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bazaar`
--
ALTER TABLE `bazaar`
  MODIFY `BazaarID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `FeedbackID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `location`
--
ALTER TABLE `location`
  MODIFY `LocationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `organizer`
--
ALTER TABLE `organizer`
  MODIFY `OrganizerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `paymentmethod`
--
ALTER TABLE `paymentmethod`
  MODIFY `PaymentMethodID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `ProductID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `producttransaction`
--
ALTER TABLE `producttransaction`
  MODIFY `ProductTransactionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `schedule`
--
ALTER TABLE `schedule`
  MODIFY `JadwalID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `StaffID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tenant`
--
ALTER TABLE `tenant`
  MODIFY `TenantID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ticket`
--
ALTER TABLE `ticket`
  MODIFY `TicketID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `transaction`
--
ALTER TABLE `transaction`
  MODIFY `TransactionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bazaar`
--
ALTER TABLE `bazaar`
  ADD CONSTRAINT `bazaar_ibfk_1` FOREIGN KEY (`LocationID`) REFERENCES `location` (`LocationID`),
  ADD CONSTRAINT `bazaar_ibfk_2` FOREIGN KEY (`OrganizerID`) REFERENCES `organizer` (`OrganizerID`);

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`BazaarID`) REFERENCES `bazaar` (`BazaarID`);

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`TenantID`) REFERENCES `tenant` (`TenantID`);

--
-- Constraints for table `producttransaction`
--
ALTER TABLE `producttransaction`
  ADD CONSTRAINT `producttransaction_ibfk_1` FOREIGN KEY (`ProductID`) REFERENCES `product` (`ProductID`),
  ADD CONSTRAINT `producttransaction_ibfk_2` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`),
  ADD CONSTRAINT `producttransaction_ibfk_3` FOREIGN KEY (`PaymentMethodID`) REFERENCES `paymentmethod` (`PaymentMethodID`);

--
-- Constraints for table `schedule`
--
ALTER TABLE `schedule`
  ADD CONSTRAINT `schedule_ibfk_1` FOREIGN KEY (`BazaarID`) REFERENCES `bazaar` (`BazaarID`),
  ADD CONSTRAINT `schedule_ibfk_2` FOREIGN KEY (`LokasiID`) REFERENCES `location` (`LocationID`);

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`OrganizerID`) REFERENCES `organizer` (`OrganizerID`);

--
-- Constraints for table `tenant`
--
ALTER TABLE `tenant`
  ADD CONSTRAINT `tenant_ibfk_1` FOREIGN KEY (`BazaarID`) REFERENCES `bazaar` (`BazaarID`);

--
-- Constraints for table `ticket`
--
ALTER TABLE `ticket`
  ADD CONSTRAINT `ticket_ibfk_1` FOREIGN KEY (`BazaarID`) REFERENCES `bazaar` (`BazaarID`);

--
-- Constraints for table `transaction`
--
ALTER TABLE `transaction`
  ADD CONSTRAINT `transaction_ibfk_1` FOREIGN KEY (`TicketID`) REFERENCES `ticket` (`TicketID`),
  ADD CONSTRAINT `transaction_ibfk_2` FOREIGN KEY (`PaymentMethodID`) REFERENCES `paymentmethod` (`PaymentMethodID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
