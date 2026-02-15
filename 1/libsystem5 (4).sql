-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 05, 2025 at 03:38 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `libsystem5`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_years`
--

CREATE TABLE `academic_years` (
  `id` int(11) NOT NULL,
  `year_start` int(4) NOT NULL,
  `year_end` int(4) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `academic_years`
--

INSERT INTO `academic_years` (`id`, `year_start`, `year_end`, `created_at`, `updated_at`) VALUES
(1, 2025, 2026, '2025-12-01 08:35:21', '2025-12-02 09:12:47'),
(2, 2026, 2027, '2025-12-02 08:31:37', '2025-12-02 09:13:01'),
(3, 2027, 2028, '2025-12-02 09:11:18', '2025-12-02 09:13:18'),
(4, 2028, 2029, '2025-12-02 09:41:20', '2025-12-02 09:41:20');

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `gmail` varchar(100) NOT NULL,
  `password` varchar(60) NOT NULL,
  `firstname` varchar(30) NOT NULL,
  `lastname` varchar(30) NOT NULL,
  `photo` varchar(200) NOT NULL,
  `created_on` date NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `gmail`, `password`, `firstname`, `lastname`, `photo`, `created_on`, `reset_token`, `reset_expires`) VALUES
(1, 'marijoysapditbsu@gmail.com', '$2y$10$ZxIeD7HTBdNQwuN.QtSTf.Kvoaew6cpIAT6g57Q52QWnOGvleth.y', 'mjoy', 'joy', '68eef50d00f5f.png', '2025-10-15', NULL, NULL),
(3, 'sheek@gmail.com', 'admin', 'sheekhayna', 'fuswelan', '68ede93986afb.png', '0000-00-00', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `archived_books`
--

CREATE TABLE `archived_books` (
  `archive_id` int(11) NOT NULL,
  `book_id` int(11) DEFAULT NULL,
  `isbn` varchar(20) NOT NULL,
  `call_no` varchar(50) DEFAULT NULL,
  `location` varchar(50) NOT NULL,
  `section` varchar(50) NOT NULL DEFAULT 'General',
  `type` varchar(50) NOT NULL DEFAULT 'Book',
  `num_copies` int(11) NOT NULL DEFAULT 1,
  `title` text NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `author` varchar(150) NOT NULL,
  `publisher` varchar(150) NOT NULL,
  `publish_date` varchar(10) NOT NULL,
  `date_added` datetime DEFAULT NULL,
  `status` int(1) NOT NULL,
  `pub_date` date DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `date_archived` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `archived_books`
--

INSERT INTO `archived_books` (`archive_id`, `book_id`, `isbn`, `call_no`, `location`, `section`, `type`, `num_copies`, `title`, `subject`, `author`, `publisher`, `publish_date`, `date_added`, `status`, `pub_date`, `category_id`, `subject_id`, `date_archived`) VALUES
(48, 22, 'restore', 'de', 'Library', 'General', 'Book', 1, 'res', '1', 'ewan', 'w', '0000-00-00', NULL, 0, NULL, NULL, NULL, '2025-11-30 12:08:38'),
(49, 21, 'del3', 'del3', 'Library', 'General', 'Book', 1, 'del3', 'del3', 'del3', '1111', '0000-00-00', NULL, 0, NULL, NULL, NULL, '2025-11-30 12:08:42'),
(52, 27, 'edit', 'ewdew23412', 'Library', 'General', 'Book', 3, 'bb', '1tfyv', '88', '1111', '2025', NULL, 0, NULL, NULL, NULL, '2025-12-03 09:40:58'),
(53, 26, '35324e56', '3333', 'Library', 'General', 'Book', 2, 'k', '', '42e3', '1111', '2025', NULL, 0, NULL, NULL, NULL, '2025-12-03 09:41:03'),
(54, 25, '35324e56', 'ewdew23412', 'Library', 'General', 'Book', 1, 'm', 'all all bat seed gu', 'ewan', 'Unknown', '2023', NULL, 0, NULL, NULL, NULL, '2025-12-03 09:41:07'),
(55, 23, 'publish', 'ewdew23412', 'Library', 'General', 'Book', 1, 'p', '', '42e3', 'Unknown', '0', NULL, 0, NULL, NULL, NULL, '2025-12-03 09:41:13');

-- --------------------------------------------------------

--
-- Table structure for table `archived_book_category_map`
--

CREATE TABLE `archived_book_category_map` (
  `id` int(11) NOT NULL,
  `archive_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `archived_book_category_map`
--

INSERT INTO `archived_book_category_map` (`id`, `archive_id`, `category_id`) VALUES
(40, 49, 56),
(43, 52, 43),
(44, 53, 55),
(45, 54, 56),
(46, 55, 55);

-- --------------------------------------------------------

--
-- Table structure for table `archived_book_copies`
--

CREATE TABLE `archived_book_copies` (
  `id` int(11) NOT NULL,
  `archive_id` int(11) NOT NULL,
  `copy_number` int(11) NOT NULL,
  `availability` enum('available','borrowed') DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `archived_book_copies`
--

INSERT INTO `archived_book_copies` (`id`, `archive_id`, `copy_number`, `availability`) VALUES
(13, 48, 1, 'available'),
(14, 49, 1, 'available'),
(21, 52, 1, 'available'),
(22, 52, 2, 'available'),
(23, 52, 3, 'available'),
(24, 53, 1, 'available'),
(25, 53, 2, 'available'),
(26, 54, 1, 'available'),
(27, 55, 1, 'available');

-- --------------------------------------------------------

--
-- Table structure for table `archived_category`
--

CREATE TABLE `archived_category` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `archived_course`
--

CREATE TABLE `archived_course` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `code` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `archived_pdf_books`
--

CREATE TABLE `archived_pdf_books` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `deleted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `archived_students`
--

CREATE TABLE `archived_students` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `firstname` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `archived_on` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `archived_subject`
--

CREATE TABLE `archived_subject` (
  `id` int(11) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `code` varchar(50) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `date_archived` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `archived_transactions`
--

CREATE TABLE `archived_transactions` (
  `archive_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `borrower_type` varchar(50) NOT NULL,
  `borrower_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `copy_id` int(11) DEFAULT NULL,
  `borrow_date` date NOT NULL,
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `status` varchar(50) NOT NULL,
  `academic_year_id` int(11) DEFAULT NULL,
  `semester` varchar(50) DEFAULT NULL,
  `created_on` timestamp NOT NULL DEFAULT current_timestamp(),
  `archived_on` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `isbn` varchar(20) NOT NULL,
  `call_no` varchar(50) DEFAULT NULL,
  `location` varchar(50) NOT NULL,
  `title` text NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `author` varchar(150) NOT NULL,
  `publisher` varchar(150) NOT NULL,
  `publish_date` int(5) DEFAULT NULL,
  `num_copies` int(11) NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp(),
  `section` varchar(455) NOT NULL,
  `type` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `isbn`, `call_no`, `location`, `title`, `subject`, `author`, `publisher`, `publish_date`, `num_copies`, `date_added`, `section`, `type`) VALUES
(15, '', '2', 'Library', 'Test_Edit1(change to Editted)', '', 'Test', 'Test', 0, 1, '2025-11-30 11:36:39', 'Filipiniana', 'Book'),
(28, '', 'Test', 'Library', 'Book Test', '', 'Test', 'Test', 2025, 15, '2025-12-03 09:42:19', 'General', 'Book'),
(29, '', 'Test_Delete', 'Library', 'Test_Delete', '', 'Test', 'Test', 2025, 1, '2025-12-03 09:43:24', 'General', 'Book'),
(30, '', 'G1', 'Library', 'Test_Good', '', 'Test', 'Test', 2024, 1, '2025-12-03 09:45:48', 'General', 'Book'),
(31, '', '1', 'Library', 'Test_Damaged', '', 'Test', 'Test', 2021, 1, '2025-12-03 09:46:44', 'General', 'Book'),
(32, '', '', 'Library', 'Test_Edit2(efgvfe)', '', 'Test', 'Edit', 2021, 1, '2025-12-03 09:49:35', 'General', 'Book'),
(33, '', '', 'Library', 'Test_Lost', '', 'Test', 'Test', 2021, 1, '2025-12-03 09:50:44', 'General', 'Book'),
(34, '', '3', 'Library', 'Test_Overdue', '', 'Test', 'Test', 2021, 1, '2025-12-03 09:51:42', 'General', 'Book'),
(36, '', '', 'Library', 'Test_Restore', '', 'Test', 'Test', 2022, 1, '2025-12-03 09:54:25', 'General', '');

-- --------------------------------------------------------

--
-- Table structure for table `books_main`
--

CREATE TABLE `books_main` (
  `id` int(11) NOT NULL,
  `isbn` varchar(20) NOT NULL,
  `call_no` varchar(50) DEFAULT NULL,
  `location` varchar(50) NOT NULL,
  `title` text NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `author` varchar(150) NOT NULL,
  `publisher` varchar(150) NOT NULL,
  `publish_date` varchar(10) NOT NULL,
  `copy_number` int(11) DEFAULT 1,
  `num_copies` int(11) NOT NULL,
  `date_added` datetime DEFAULT NULL,
  `status` int(1) NOT NULL,
  `pub_date` date DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `subject_id` int(11) DEFAULT NULL,
  `section` varchar(455) NOT NULL,
  `type` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `book_category_map`
--

CREATE TABLE `book_category_map` (
  `id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book_category_map`
--

INSERT INTO `book_category_map` (`id`, `book_id`, `category_id`) VALUES
(50, 29, 43),
(52, 31, 43),
(53, 30, 43),
(55, 32, 43),
(56, 33, 43),
(60, 15, 43),
(61, 36, 43),
(62, 28, 43),
(63, 34, 43);

-- --------------------------------------------------------

--
-- Table structure for table `book_classification_type`
--

CREATE TABLE `book_classification_type` (
  `id` int(11) NOT NULL,
  `title` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book_classification_type`
--

INSERT INTO `book_classification_type` (`id`, `title`) VALUES
(1, 'Filipiniana Books'),
(2, 'General Circulation Books');

-- --------------------------------------------------------

--
-- Table structure for table `book_copies`
--

CREATE TABLE `book_copies` (
  `id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `copy_number` int(11) NOT NULL,
  `availability` enum('available','borrowed','lost','repair','damaged') DEFAULT 'available',
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book_copies`
--

INSERT INTO `book_copies` (`id`, `book_id`, `copy_number`, `availability`, `date_created`) VALUES
(66, 36, 1, 'available', '2025-12-03 12:27:26'),
(67, 28, 1, 'available', '2025-12-03 12:27:33'),
(68, 28, 2, 'available', '2025-12-03 12:27:33'),
(69, 28, 3, 'available', '2025-12-03 12:27:33'),
(70, 28, 4, 'borrowed', '2025-12-03 12:27:33'),
(71, 28, 5, 'available', '2025-12-03 12:27:33'),
(72, 28, 6, 'available', '2025-12-03 12:27:33'),
(73, 28, 7, 'available', '2025-12-03 12:27:33'),
(74, 28, 8, 'available', '2025-12-03 12:27:33'),
(75, 28, 9, 'available', '2025-12-03 12:27:33'),
(76, 28, 10, 'available', '2025-12-03 12:27:33'),
(77, 28, 11, 'available', '2025-12-03 12:27:33'),
(78, 28, 12, 'available', '2025-12-03 12:27:33'),
(79, 28, 13, 'available', '2025-12-03 12:27:33'),
(80, 28, 14, 'available', '2025-12-03 12:27:33'),
(81, 28, 15, 'available', '2025-12-03 12:27:33'),
(82, 34, 1, 'available', '2025-12-03 12:27:46');

-- --------------------------------------------------------

--
-- Table structure for table `book_subject_map`
--

CREATE TABLE `book_subject_map` (
  `id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `borrow`
--

CREATE TABLE `borrow` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `book_id` int(11) NOT NULL,
  `date_borrow` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `status` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `borrow_transactions`
--

CREATE TABLE `borrow_transactions` (
  `id` int(11) NOT NULL,
  `borrower_type` enum('student','faculty') NOT NULL,
  `borrower_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `copy_id` int(11) DEFAULT NULL,
  `borrow_date` datetime NOT NULL,
  `due_date` datetime NOT NULL,
  `return_date` date DEFAULT NULL,
  `status` enum('borrowed','overdue','returned','damaged','repair','lost') DEFAULT 'borrowed',
  `created_on` timestamp NOT NULL DEFAULT current_timestamp(),
  `academic_year_id` int(11) NOT NULL,
  `semester` enum('1st','2nd','Short-Term') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrow_transactions`
--

INSERT INTO `borrow_transactions` (`id`, `borrower_type`, `borrower_id`, `book_id`, `copy_id`, `borrow_date`, `due_date`, `return_date`, `status`, `created_on`, `academic_year_id`, `semester`) VALUES
(40, 'student', 42, 28, 67, '2025-12-03 00:00:00', '2025-12-04 00:00:00', '2025-12-05', 'damaged', '2025-12-03 12:28:14', 1, ''),
(41, 'student', 42, 28, 68, '2025-12-03 00:00:00', '2025-12-04 00:00:00', '2025-12-05', 'returned', '2025-12-03 12:28:36', 1, ''),
(42, 'student', 42, 28, 69, '2025-12-03 00:00:00', '2025-12-04 00:00:00', '2025-12-05', 'returned', '2025-12-03 12:45:56', 1, ''),
(43, 'student', 42, 28, 70, '2025-12-03 00:00:00', '2025-12-03 00:00:00', NULL, 'lost', '2025-12-03 12:46:38', 1, ''),
(44, 'student', 42, 28, 67, '2025-12-03 21:16:52', '2025-12-04 00:00:00', '2025-12-05', 'returned', '2025-12-03 13:16:52', 1, ''),
(45, 'student', 44, 28, 71, '2025-12-03 21:18:33', '2025-12-04 00:00:00', '2025-12-05', 'returned', '2025-12-03 13:18:33', 2, '');

-- --------------------------------------------------------

--
-- Table structure for table `calibre_books`
--

CREATE TABLE `calibre_books` (
  `id` int(11) NOT NULL,
  `identifiers` varchar(255) DEFAULT NULL,
  `author` varchar(255) NOT NULL,
  `unnamed: 3` text NOT NULL,
  `title` varchar(255) NOT NULL,
  `published_date` datetime DEFAULT NULL,
  `format` varchar(50) DEFAULT NULL,
  `tags` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `external_link` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `file_path2` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `calibre_books`
--

INSERT INTO `calibre_books` (`id`, `identifiers`, `author`, `unnamed: 3`, `title`, `published_date`, `format`, `tags`, `file_path`, `external_link`, `created_at`, `file_path2`) VALUES
(1, 'isbn: Test', 'Ebook test', '', 'Ebook Test', '0000-00-00 00:00:00', '', '', '../e-books/1764755996_Library Catalog (1)_1760175920.pdf', 'http://localhost/libsystem5/1/libsystem/admin/calibre_books.php?edit=1', '2025-12-03 09:59:06', '');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id`, `name`) VALUES
(43, '001–099: General Works '),
(44, '100–199: Philosophy and Psychology'),
(45, '200–299: Religion'),
(46, '300–399: Social Sciences / Transport & Economics'),
(47, '320–329: Political Science'),
(48, '330–339: Economics '),
(49, '340–349: Law'),
(50, '350–359: Public Administration.'),
(51, '360–369: Social Services / Criminology / Law Enforcement / NSTP.'),
(52, '370–379: Education '),
(53, '390–399: Customs, Etiquette, Folklore.'),
(54, '400–499: Grammar and Languages '),
(55, '500–599: Natural Sciences and Mathematics '),
(56, '600–699: Technology (Applied Science) '),
(57, '700–799: Fine Arts and Recreation '),
(58, '800–899: Literature.'),
(59, '900–999: History and Geography.');

-- --------------------------------------------------------

--
-- Table structure for table `course`
--

CREATE TABLE `course` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `code` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `course`
--

INSERT INTO `course` (`id`, `title`, `code`) VALUES
(9, 'Bachelor of Science in information Technology', 'BSIT'),
(13, 'Bachelor of Industrial Technolgy', 'BIT'),
(14, 'Bachelor of Science in Criminology', 'BSCRIM'),
(15, 'Bachelor of Elementary Education', 'BEED'),
(16, 'Bachelor of Secondary Education', 'BSED'),
(17, 'Bachelor of Technical-Vocational Teacher Education', 'BTVTEd'),
(18, 'Bachelor of Technology and Livelihood Education', 'BTLEd'),
(19, 'Bachelor of Science in Entrepreneurship', 'BSENTREP'),
(20, 'Bachelor of Public Administration', 'BPA');

-- --------------------------------------------------------

--
-- Table structure for table `faculty`
--

CREATE TABLE `faculty` (
  `id` int(11) NOT NULL,
  `faculty_id` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `middlename` varchar(100) DEFAULT NULL,
  `lastname` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `created_on` date NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `archived` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `faculty`
--

INSERT INTO `faculty` (`id`, `faculty_id`, `password`, `firstname`, `middlename`, `lastname`, `phone`, `email`, `department`, `created_on`, `photo`, `archived`) VALUES
(2, '56A', '$2y$10$LDln6YTMS5y5GTmP7bxvg.11mls60U1PhX2wdjpuDvcaFEj//rN7i', 'sheekhayna', NULL, 'fuswelan', '09982345353', 'marijoysapditbsu@gmail.com', 'CAT', '2025-10-26', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pdf_books`
--

CREATE TABLE `pdf_books` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(50) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `returns`
--

CREATE TABLE `returns` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `date_return` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `returns`
--

INSERT INTO `returns` (`id`, `student_id`, `book_id`, `date_return`) VALUES
(7, 36, 67, '2025-10-10'),
(8, 36, 67, '2025-10-11'),
(9, 36, 68, '2025-10-17'),
(10, 37, 94, '2025-10-26');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `active_academic_year` int(11) DEFAULT NULL,
  `active_semester` varchar(20) DEFAULT NULL,
  `academic_year` varchar(20) NOT NULL,
  `semester` varchar(20) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `active_academic_year`, `active_semester`, `academic_year`, `semester`, `updated_at`) VALUES
(1, 2, '1st Semester', '1', '1st Semester', '2025-12-03 13:17:18');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `middlename` varchar(100) DEFAULT NULL,
  `lastname` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(50) NOT NULL,
  `course_id` int(11) NOT NULL,
  `created_on` date NOT NULL,
  `photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `student_id`, `password`, `firstname`, `middlename`, `lastname`, `phone`, `email`, `course_id`, `created_on`, `photo`) VALUES
(37, '2324', '$2y$10$RSERbgJ../AOqxLFVPeaDuNxO10saEGgFRh7CXx/zwbvT6ggCE3qe', 'sheekhayna', NULL, 'fuswelan', '09982345353', 'teksokbsu22@gmail.com', 9, '0000-00-00', '15-152034_ginger-cat-lazing-png-image-transparent-background-cat.png'),
(42, '4610', '', 'Marijoy', NULL, 'Sapdit', NULL, '', 9, '0000-00-00', NULL),
(44, '4616', '$2y$10$suceWU4UUDRuBg.CEi1/Ruf5BPefBVaR30qLHDlCCwK4oGNvqMdJu', 'Lecs Lou', 'T.', 'Apelado', '09982345353', 'lecslou@gmail.com', 14, '2025-11-19', NULL),
(45, '4861', '$2y$10$LReAHd419E2ThA.fUKRqFuz8ZkDMl/nJfBFIo/k64.D2dCQ4WGlve', 'Marc Ibert', 'P.', 'Osting', '09475323157', 'osting@gmail.com', 14, '2025-11-19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `subject`
--

CREATE TABLE `subject` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `date_created` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subject`
--

INSERT INTO `subject` (`id`, `name`, `date_created`) VALUES
(21, 'IT 121.1', '2025-12-03 13:13:47');

-- --------------------------------------------------------

--
-- Table structure for table `suggested_books`
--

CREATE TABLE `suggested_books` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) DEFAULT NULL,
  `isbn` varchar(50) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `suggested_by` varchar(100) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `faculty_id` int(11) DEFAULT NULL,
  `borrower_type` enum('Student','Faculty') NOT NULL DEFAULT 'Student',
  `book_id` int(11) NOT NULL,
  `copy_id` int(11) DEFAULT NULL,
  `transaction_type` enum('Borrow','Return','Reserve') NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `due_date` date DEFAULT NULL,
  `date_returned` date DEFAULT NULL,
  `overdue_days` int(11) NOT NULL DEFAULT 0,
  `fine` decimal(10,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `status` enum('Pending','Completed') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions_backup`
--

CREATE TABLE `transactions_backup` (
  `id` int(11) NOT NULL DEFAULT 0,
  `student_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `transaction_type` enum('Borrow','Return','Reserve') NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pending','Completed') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaction_settings`
--

CREATE TABLE `transaction_settings` (
  `id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `semester` enum('1st','2nd','Summer') NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaction_settings`
--

INSERT INTO `transaction_settings` (`id`, `academic_year_id`, `semester`, `updated_at`) VALUES
(1, 1, '1st', '2025-12-01 09:42:24');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_on` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `firstname`, `lastname`, `photo`, `created_on`) VALUES
(1, '1234', '1234', 'm', 'd', NULL, '2025-10-15 02:43:45');

-- --------------------------------------------------------

--
-- Table structure for table `user_logbook`
--

CREATE TABLE `user_logbook` (
  `id` int(11) NOT NULL,
  `user_id` varchar(50) DEFAULT NULL,
  `user_type` enum('guest','student','faculty','admin') NOT NULL,
  `firstname` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text NOT NULL,
  `login_time` datetime NOT NULL,
  `logout_time` datetime DEFAULT NULL,
  `session_duration` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_logbook`
--

INSERT INTO `user_logbook` (`id`, `user_id`, `user_type`, `firstname`, `lastname`, `ip_address`, `user_agent`, `login_time`, `logout_time`, `session_duration`) VALUES
(31, 'marijoysapditbsu@gmail.com', 'admin', 'mjoy', 'joy', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 10:25:25', NULL, NULL),
(32, 'marijoysapditbsu@gmail.com', 'admin', 'mjoy', 'joy', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-01 01:14:30', NULL, NULL),
(33, 'marijoysapditbsu@gmail.com', 'admin', 'mjoy', 'joy', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 03:47:37', NULL, NULL),
(34, 'marijoysapditbsu@gmail.com', 'admin', 'mjoy', 'joy', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 02:55:57', NULL, NULL),
(35, 'marijoysapditbsu@gmail.com', 'admin', 'mjoy', 'joy', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-05 14:00:41', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_years`
--
ALTER TABLE `academic_years`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `gmail` (`gmail`);

--
-- Indexes for table `archived_books`
--
ALTER TABLE `archived_books`
  ADD PRIMARY KEY (`archive_id`);

--
-- Indexes for table `archived_book_category_map`
--
ALTER TABLE `archived_book_category_map`
  ADD PRIMARY KEY (`id`),
  ADD KEY `archived_book_category_map_ibfk_1` (`archive_id`);

--
-- Indexes for table `archived_book_copies`
--
ALTER TABLE `archived_book_copies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `archive_id` (`archive_id`);

--
-- Indexes for table `archived_category`
--
ALTER TABLE `archived_category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `archived_course`
--
ALTER TABLE `archived_course`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `archived_pdf_books`
--
ALTER TABLE `archived_pdf_books`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `archived_students`
--
ALTER TABLE `archived_students`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `archived_subject`
--
ALTER TABLE `archived_subject`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `archived_transactions`
--
ALTER TABLE `archived_transactions`
  ADD PRIMARY KEY (`archive_id`),
  ADD KEY `idx_archived_transaction_id` (`id`),
  ADD KEY `idx_archived_borrower` (`borrower_type`,`borrower_id`),
  ADD KEY `idx_archived_book` (`book_id`),
  ADD KEY `idx_archived_status` (`status`),
  ADD KEY `idx_archived_date` (`archived_on`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_books_id_date` (`id`,`date_added`);

--
-- Indexes for table `books_main`
--
ALTER TABLE `books_main`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `book_category_map`
--
ALTER TABLE `book_category_map`
  ADD PRIMARY KEY (`id`),
  ADD KEY `book_id` (`book_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_book_category_map_book_id` (`book_id`),
  ADD KEY `idx_book_category_map_category_id` (`category_id`);

--
-- Indexes for table `book_classification_type`
--
ALTER TABLE `book_classification_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `book_copies`
--
ALTER TABLE `book_copies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `book_subject_map`
--
ALTER TABLE `book_subject_map`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_book_subject` (`book_id`,`subject_id`),
  ADD KEY `book_id` (`book_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `idx_book_subject_map_book_id` (`book_id`),
  ADD KEY `idx_book_subject_map_subject_id` (`subject_id`);

--
-- Indexes for table `borrow`
--
ALTER TABLE `borrow`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `borrow_transactions`
--
ALTER TABLE `borrow_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `book_id` (`book_id`),
  ADD KEY `idx_borrow_transactions_book_id` (`book_id`),
  ADD KEY `idx_borrow_transactions_status` (`status`),
  ADD KEY `idx_borrow_transactions_book_status` (`book_id`,`status`),
  ADD KEY `fk_borrow_ay` (`academic_year_id`),
  ADD KEY `borrow_transactions_ibfk_copy` (`copy_id`);

--
-- Indexes for table `calibre_books`
--
ALTER TABLE `calibre_books`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `course`
--
ALTER TABLE `course`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `faculty`
--
ALTER TABLE `faculty`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pdf_books`
--
ALTER TABLE `pdf_books`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `returns`
--
ALTER TABLE `returns`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subject`
--
ALTER TABLE `subject`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `suggested_books`
--
ALTER TABLE `suggested_books`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `academic_year_id` (`academic_year_id`),
  ADD KEY `copy_id_idx` (`copy_id`),
  ADD KEY `transactions_fk_faculty` (`faculty_id`);

--
-- Indexes for table `transaction_settings`
--
ALTER TABLE `transaction_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `academic_year_id` (`academic_year_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_logbook`
--
ALTER TABLE `user_logbook`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_login_time` (`login_time`),
  ADD KEY `idx_user_type` (`user_type`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academic_years`
--
ALTER TABLE `academic_years`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `archived_books`
--
ALTER TABLE `archived_books`
  MODIFY `archive_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `archived_book_category_map`
--
ALTER TABLE `archived_book_category_map`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `archived_book_copies`
--
ALTER TABLE `archived_book_copies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `archived_category`
--
ALTER TABLE `archived_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `archived_course`
--
ALTER TABLE `archived_course`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `archived_pdf_books`
--
ALTER TABLE `archived_pdf_books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `archived_students`
--
ALTER TABLE `archived_students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `archived_subject`
--
ALTER TABLE `archived_subject`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `archived_transactions`
--
ALTER TABLE `archived_transactions`
  MODIFY `archive_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `books_main`
--
ALTER TABLE `books_main`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1084;

--
-- AUTO_INCREMENT for table `book_category_map`
--
ALTER TABLE `book_category_map`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `book_classification_type`
--
ALTER TABLE `book_classification_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `book_copies`
--
ALTER TABLE `book_copies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `book_subject_map`
--
ALTER TABLE `book_subject_map`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `borrow`
--
ALTER TABLE `borrow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `borrow_transactions`
--
ALTER TABLE `borrow_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `calibre_books`
--
ALTER TABLE `calibre_books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `course`
--
ALTER TABLE `course`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `faculty`
--
ALTER TABLE `faculty`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `pdf_books`
--
ALTER TABLE `pdf_books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `subject`
--
ALTER TABLE `subject`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `suggested_books`
--
ALTER TABLE `suggested_books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transaction_settings`
--
ALTER TABLE `transaction_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_logbook`
--
ALTER TABLE `user_logbook`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `archived_book_category_map`
--
ALTER TABLE `archived_book_category_map`
  ADD CONSTRAINT `archived_book_category_map_ibfk_1` FOREIGN KEY (`archive_id`) REFERENCES `archived_books` (`archive_id`) ON DELETE CASCADE;

--
-- Constraints for table `archived_book_copies`
--
ALTER TABLE `archived_book_copies`
  ADD CONSTRAINT `archived_book_copies_ibfk_1` FOREIGN KEY (`archive_id`) REFERENCES `archived_books` (`archive_id`) ON DELETE CASCADE;

--
-- Constraints for table `books_main`
--
ALTER TABLE `books_main`
  ADD CONSTRAINT `books_main_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `books_main_ibfk_subject` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `book_category_map`
--
ALTER TABLE `book_category_map`
  ADD CONSTRAINT `book_category_map_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `book_category_map_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `book_copies`
--
ALTER TABLE `book_copies`
  ADD CONSTRAINT `book_copies_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `book_subject_map`
--
ALTER TABLE `book_subject_map`
  ADD CONSTRAINT `book_subject_map_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books_main` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `book_subject_map_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `borrow_transactions`
--
ALTER TABLE `borrow_transactions`
  ADD CONSTRAINT `borrow_transactions_ibfk_copy` FOREIGN KEY (`copy_id`) REFERENCES `book_copies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_borrow_ay` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_fk_copy` FOREIGN KEY (`copy_id`) REFERENCES `book_copies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `transactions_fk_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`);

--
-- Constraints for table `transaction_settings`
--
ALTER TABLE `transaction_settings`
  ADD CONSTRAINT `transaction_settings_ibfk_1` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
