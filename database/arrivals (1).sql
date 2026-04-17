-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 17, 2026 at 04:08 PM
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
-- Database: `port_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `arrivals`
--

CREATE TABLE `arrivals` (
  `id` int(11) NOT NULL,
  `ship_name` varchar(100) NOT NULL,
  `imo_number` varchar(20) DEFAULT NULL,
  `cargo_type` varchar(50) DEFAULT NULL,
  `port` varchar(100) DEFAULT NULL,
  `arrival_time` datetime DEFAULT NULL,
  `departure_date` datetime DEFAULT NULL,
  `port_charges` decimal(10,2) NOT NULL DEFAULT 0.00,
  `pilotage` decimal(10,2) NOT NULL DEFAULT 0.00,
  `towage` decimal(10,2) NOT NULL DEFAULT 0.00,
  `berth_dues` decimal(10,2) NOT NULL DEFAULT 0.00,
  `services` decimal(10,2) NOT NULL DEFAULT 0.00,
  `garbage` decimal(10,2) NOT NULL DEFAULT 0.00,
  `crew_changes` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cargo_ops` decimal(10,2) NOT NULL DEFAULT 0.00,
  `customs_docs` decimal(10,2) NOT NULL DEFAULT 0.00,
  `agency_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) DEFAULT 'ΑΝΑΜΕΝΟΜΕΝΟ',
  `actual_arrival` datetime DEFAULT NULL,
  `actual_departure` datetime DEFAULT NULL,
  `internal_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `arrivals`
--

INSERT INTO `arrivals` (`id`, `ship_name`, `imo_number`, `cargo_type`, `port`, `arrival_time`, `departure_date`, `port_charges`, `pilotage`, `towage`, `berth_dues`, `services`, `garbage`, `crew_changes`, `cargo_ops`, `customs_docs`, `agency_fee`, `status`, `actual_arrival`, `actual_departure`, `internal_notes`) VALUES
(1, 'PIGASOS', '202520000', 'Containers', 'πειραιας1', '2026-04-16 22:00:00', NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'ΑΝΑΧΩΡΗΣΕ', '2026-04-16 20:19:31', '2026-04-17 16:50:40', 'ηγηη\n\n'),
(2, 'PIGASOS', '20252000', 'Πετρέλαιο', 'πειραιας', '2026-04-16 22:02:00', '2026-04-18 06:00:00', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'ΣΤΟ ΛΙΜΑΝΙ', '2026-04-17 16:52:01', NULL, 'TSIOUI'),
(3, 'POSIDONAS', '33333333', 'Containers', NULL, '2026-04-17 10:00:00', NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'ΣΤΟ ΛΙΜΑΝΙ', '2026-04-17 16:52:10', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `arrivals`
--
ALTER TABLE `arrivals`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `arrivals`
--
ALTER TABLE `arrivals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
