-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 13, 2026 at 04:02 AM
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
-- Database: `greenday`
--

-- --------------------------------------------------------

--
-- Table structure for table `albums`
--

CREATE TABLE `albums` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `release_year` year(4) NOT NULL,
  `cover_image` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `albums`
--

INSERT INTO `albums` (`id`, `title`, `release_year`, `cover_image`, `description`, `created_at`) VALUES
(1, 'Dookie', '1994', 'Greenday/assets/images/albums/dookie.jpg', 'Dookie adalah album studio ketiga Green Day yang dirilis pada tahun 1994 di bawah label Reprise Records. Album terobosan ini mempopulerkan kembali musik punk rock ke arus utama global dengan deretan hits legendaris seperti Basket Case, Longview, dan When I Come Around, serta berhasil memenangkan Grammy Award untuk Best Alternative Music Album.', '2026-11-13 01:57:16'),
(2, 'American Idiot', '2004', 'Greenday/assets/images/albums/americanIdiot.jpg', 'American Idiot adalah mahakarya rock opera punk rock multi-platinum tahun 2004 yang mengangkat kritik tajam terhadap perang, media massa, dan keputusasaan generasi muda pasca 9/11 di Amerika Serikat. Meraih predikat Best Rock Album di Grammy Awards dan melahirkan lagu seperti Boulevard of Broken Dreams.', '2026-11-13 01:57:16'),
(3, 'Nimrod', '1997', 'Greenday/assets/images/albums/nimrod.jpg', 'Nimrod adalah album studio kelima Green Day yang dirilis pada tahun 1997. Menampilkan eksperimentasi musik yang luas melintasi punk rock, ska punk, surf rock, hingga ballad akustik legendaris dunia Good Riddance (Time of Your Life).', '2026-11-13 01:57:16'),
(4, 'Insomniac', '1995', 'Greenday/assets/images/albums/insomniac.jpg', 'Insomniac adalah album studio keempat Green Day yang dirilis pada tahun 1995. Membawa nuansa punk yang jauh lebih berat, gelap, dan sarat kemarahan setelah ledakan sukses Dookie, dengan single populer Geek Stink Breath, Stuck with Me, Brain Stew, dan Walking Contradiction.', '2026-11-13 01:57:16'),
(5, '21st Century Breakdown', '2009', 'Greenday/assets/images/albums/21st-century-breakdown.jpg', '21st Century Breakdown adalah album rock opera ambisius kedua Green Day yang dirilis pada tahun 2009. Menampilkan kisah naratif pasangan muda Christian dan Gloria di tengah krisis sosial modern, menghadirkan hits spektakuler 21 Guns dan Know Your Enemy.', '2026-11-13 01:57:16'),
(6, 'Warning', '2000', 'Greenday/assets/images/albums/warning.png', 'Warning adalah album studio keenam Green Day yang dirilis pada tahun 2000. Album ini menampilkan eksplorasi pop punk akustik dan folk punk dengan lirik kritik sosial dan optimisme personal yang kuat lewat lagu Minority, Warning, Waiting, dan Macy\'s Day Parade.', '2026-11-13 01:57:16'),
(7, 'Revolution Radio', '2016', 'Greenday/assets/images/albums/revolution-radio.jpeg', 'Revolution Radio adalah album studio ke-12 Green Day yang dirilis pada tahun 2016. Album ini menandai kembalinya trio punk ke aransemen distorsi gitar bertenaga tinggi dengan pesan kuat tentang kekacauan politik dan kekerasan senjata lewat lagu Bang Bang dan Still Breathing.', '2026-11-13 01:57:16'),
(8, 'Saviors', '2024', 'Greenday/assets/images/albums/saviors.jpg', 'Saviors adalah album studio ke-14 Green Day yang dirilis pada Januari 2024. Menjadi reuni bersama produser Rob Cavallo serta perayaan 30 tahun Dookie dan 20 tahun American Idiot dengan hits The American Dream Is Killing Me, Dilemma, Look Ma, No Brains!, dan Bobby Sox.', '2026-11-13 01:57:16');

-- --------------------------------------------------------

--
-- Table structure for table `band_members`
--

CREATE TABLE `band_members` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `role` varchar(100) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `birth_date` date DEFAULT NULL,
  `join_year` year(4) NOT NULL,
  `biography` text NOT NULL,
  `is_touring_member` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `band_members`
--

INSERT INTO `band_members` (`id`, `name`, `role`, `photo`, `birth_date`, `join_year`, `biography`, `is_touring_member`) VALUES
(1, 'Billie Joe Armstrong', 'Lead Vocalist & Guitarist', 'Greenday/assets/images/billie.jpg', '1972-02-17', '1987', 'Billie Joe Armstrong adalah vokalis utama, penulis lagu utama, dan gitaris legendaris Green Day. Mendirikan band bersama Mike Dirnt pada tahun 1987 di Rodeo, California. Dikenal dengan karakter vokal penuh energi, melodi punk yang kuat, serta menjadi salah satu ikon terdepan dalam kebangkitan punk rock dunia.', 0),
(2, 'Mike Dirnt', 'Bassist & Backing Vocalist', 'Greenday/assets/images/mike.jpeg', '1972-05-04', '1987', 'Michael Ryan Pritchard (Mike Dirnt) adalah bassist dan salah satu pendiri Green Day. Gaya permainan bass-nya yang bertenaga, sangat melodik, serta harmoni vokalnya menjadi pilar penting dalam aransemen musik dan penampilan panggung Green Day.', 0),
(3, 'Tré Cool', 'Drummer & Percussionist', 'Greenday/assets/images/cool.jpg', '1972-12-09', '1990', 'Frank Edwin Wright III (Tré Cool) bergabung dengan Green Day pada tahun 1990 menggantikan John Kiffmeyer. Kecepatan tempo, humor eksentrik, dan ketukan drum yang eksplosif menyempurnakan trio legendaris Green Day hingga kini.', 0),
(4, 'Jason White', 'Touring Lead Guitarist', 'Greenday/assets/images/hero_2.jpg', '1973-11-11', '1999', 'Jason White adalah gitaris tur setia yang telah mendampingi Green Day di ratusan konser stadion dunia sejak tahun 1999. Memberikan dimensi ritme dan solo gitar ekstra yang padat pada setiap konser live Green Day.', 1);

-- --------------------------------------------------------

--
-- Table structure for table `community_posts`
--

CREATE TABLE `community_posts` (
  `id` int(11) NOT NULL,
  `author_name` varchar(100) NOT NULL,
  `author_email` varchar(150) DEFAULT NULL,
  `message` text NOT NULL,
  `likes` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read','replied') NOT NULL DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `summary` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `published_at` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `title`, `slug`, `summary`, `content`, `image_url`, `published_at`, `created_at`) VALUES
(1, 'Green Day Announces World Tour 2025!', 'green-day-announces-world-tour-2025', 'Green Day officially announced their 2025 world tour covering Asia, Australia, Europe and America.', 'Green Day has officially announced the dates for The Saviors World Tour 2025! Celebrating 30 years of Dookie and 20 years of American Idiot alongside their new hit album Saviors. The stadium tour will span across Southeast Asia including Jakarta and Singapore, Japan, Australia, Europe, and North America.', 'Greenday/assets/news/tour_2025.jpg', '2025-01-15', '2026-11-13 01:57:16'),
(2, 'New Single \"One More Time\" Released', 'new-single-one-more-time-released', 'Green Day\'s latest single \"One More Time\" is now available on all digital platforms.', 'Green Day surprises punk rock enthusiasts worldwide with the sudden release of their special bonus single \'One More Time\'. Featuring hard-hitting melodic hooks and nostalgic overdrive riffs, the track has quickly risen to the top of rock charts globally.', 'Greenday/assets/news/one_more_time.jpg', '2025-02-10', '2026-11-13 01:57:16'),
(3, 'Saviors Reaches 1 Billion Streams!', 'saviors-reaches-1-billion-streams', 'The album \"Saviors\" has surpassed one billion streams worldwide.', 'Just months after its global release, Green Day\'s 14th studio album \'Saviors\' has achieved an astounding landmark of surpassing 1 billion streams across Spotify, Apple Music, and other major streaming platforms. Singles like Dilemma and The American Dream Is Killing Me spearheaded this historic record.', 'Greenday/assets/news/saviors_billion.jpg', '2025-03-01', '2026-11-13 01:57:16'),
(4, 'New Music Video for \"Dilemma\" Out Now!', 'new-music-video-for-dilemma-out-now', 'Green Day has released a brand new energetic and emotional music video for \"Dilemma\".', 'Green Day has dropped an electrifying black-and-white to color music video for \'Dilemma\', the deeply personal and emotional fan-favorite track from the Saviors album. Directed with raw kinetic energy, the video showcases Billie Joe Armstrong confronting personal battles and triumph through punk rock.', 'Greenday/assets/news/dilemma_mv.jpg', '2025-03-20', '2026-11-13 01:57:16'),
(5, 'Green Day Rocks Live at the World Stage', 'green-day-rocks-live-at-the-world-stage', 'Experience the raw energy of Green Day\'s live performance as they play to a sold-out stadium.', 'Over 80,000 screaming fans packed the arena as Green Day delivered an explosive 2.5-hour career-spanning setlist. Performing all of Dookie and American Idiot in their entirety alongside brand new Saviors hits, the band proved once again why they remain the unrivaled kings of live stadium punk rock.', 'Greenday/assets/news/green_day_live.jpg', '2025-04-05', '2026-11-13 01:57:16');

-- --------------------------------------------------------

--
-- Table structure for table `songs`
--

CREATE TABLE `songs` (
  `id` int(11) NOT NULL,
  `album_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `track_number` int(11) NOT NULL,
  `duration` varchar(10) NOT NULL,
  `audio_file` varchar(255) NOT NULL,
  `lyrics` longtext DEFAULT NULL,
  `play_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `songs`
--
-- (Data dikosongkan agar ukuran repository ringan untuk di-upload ke GitHub)

-- --------------------------------------------------------

--
-- Table structure for table `ticket_bookings`
--

CREATE TABLE `ticket_bookings` (
  `id` int(11) NOT NULL,
  `booking_code` varchar(50) NOT NULL,
  `tour_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `buyer_name` varchar(100) NOT NULL,
  `buyer_email` varchar(150) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `total_price` decimal(10,2) NOT NULL,
  `payment_status` enum('pending','paid','cancelled') NOT NULL DEFAULT 'paid',
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_categories`
--

CREATE TABLE `ticket_categories` (
  `id` int(11) NOT NULL,
  `tour_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `total_quota` int(11) NOT NULL,
  `available_quota` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ticket_categories`
--

INSERT INTO `ticket_categories` (`id`, `tour_id`, `category_name`, `description`, `price`, `total_quota`, `available_quota`) VALUES
(1, 1, 'VIP Rock Pit', 'Akses barisan terdepan panggung + Soundcheck pass eksklusif + Official Laminate pass', 2250000.00, 250, 184),
(2, 1, 'Cat 1 Standing', 'Area festival berdiri tepat di depan panggung utama dengan akses masuk lebih awal', 1500000.00, 800, 622),
(3, 1, 'Cat 2 Seated', 'Tribune bernomor duduk dengan sudut pandang panggung luas dan nyaman', 1125000.00, 1500, 1240),
(4, 2, 'VIP Rock Pit', 'Akses barisan terdepan panggung + Soundcheck pass eksklusif + Official Laminate pass', 2700000.00, 250, 184),
(5, 2, 'Cat 1 Standing', 'Area festival berdiri tepat di depan panggung utama dengan akses masuk lebih awal', 1800000.00, 800, 620),
(6, 2, 'Cat 2 Seated', 'Tribune bernomor duduk dengan sudut pandang panggung luas dan nyaman', 1350000.00, 1500, 1240),
(7, 3, 'VIP Rock Pit', 'Akses barisan terdepan panggung + Soundcheck pass eksklusif + Official Laminate pass', 3000000.00, 250, 182),
(8, 3, 'Cat 1 Standing', 'Area festival berdiri tepat di depan panggung utama dengan akses masuk lebih awal', 2000000.00, 800, 620),
(9, 3, 'Cat 2 Seated', 'Tribune bernomor duduk dengan sudut pandang panggung luas dan nyaman', 1500000.00, 1500, 1240),
(10, 4, 'VIP Rock Pit', 'Akses barisan terdepan panggung + Soundcheck pass eksklusif + Official Laminate pass', 3300000.00, 250, 180),
(11, 4, 'Cat 1 Standing', 'Area festival berdiri tepat di depan panggung utama dengan akses masuk lebih awal', 2200000.00, 800, 620),
(12, 4, 'Cat 2 Seated', 'Tribune bernomor duduk dengan sudut pandang panggung luas dan nyaman', 1650000.00, 1500, 1240),
(13, 5, 'VIP Rock Pit', 'Akses barisan terdepan panggung + Soundcheck pass eksklusif + Official Laminate pass', 3750000.00, 250, 184),
(14, 5, 'Cat 1 Standing', 'Area festival berdiri tepat di depan panggung utama dengan akses masuk lebih awal', 2500000.00, 800, 620),
(15, 5, 'Cat 2 Seated', 'Tribune bernomor duduk dengan sudut pandang panggung luas dan nyaman', 1875000.00, 1500, 1240);

-- --------------------------------------------------------

--
-- Table structure for table `tours`
--

CREATE TABLE `tours` (
  `id` int(11) NOT NULL,
  `tour_name` varchar(150) NOT NULL DEFAULT 'The Saviors World Tour',
  `event_date` date NOT NULL,
  `city` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL,
  `venue` varchar(150) NOT NULL,
  `base_price` decimal(10,2) NOT NULL DEFAULT 1500000.00,
  `status` enum('available','sold-out','completed') NOT NULL DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tours`
--

INSERT INTO `tours` (`id`, `tour_name`, `event_date`, `city`, `country`, `venue`, `base_price`, `status`, `created_at`) VALUES
(1, 'The Saviors World Tour 2025', '2025-10-12', 'Jakarta', 'Indonesia', 'Gelora Bung Karno Stadium', 1500000.00, 'available', '2026-11-13 01:57:16'),
(2, 'The Saviors World Tour 2025', '2025-10-15', 'Singapore', 'Singapore', 'National Stadium', 1800000.00, 'available', '2026-11-13 01:57:16'),
(3, 'The Saviors World Tour 2025', '2025-10-20', 'Tokyo', 'Japan', 'Tokyo Dome', 2000000.00, 'available', '2026-11-13 01:57:16'),
(4, 'The Saviors World Tour 2025', '2025-10-25', 'Sydney', 'Australia', 'Accor Stadium', 2200000.00, 'available', '2026-11-13 01:57:16'),
(5, 'The Saviors World Tour 2025', '2025-11-10', 'London', 'United Kingdom', 'Wembley Stadium', 2500000.00, 'available', '2026-11-13 01:57:16');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('superadmin','admin','editor') NOT NULL DEFAULT 'admin',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `role`, `last_login`, `created_at`) VALUES
(1, 'aimcleverley', 'super@greenday.com', '$2y$10$6EkUnA/m/Pbytzg6FZmSzuuMQmSfuwibf1c/ob0nTWMmTBNeJAgFO', 'Green Day Administrator', 'superadmin', '2026-11-13 02:56:44', '2026-11-13 01:57:16'),
(4, 'admin_tour', 'admin@greenday.com', '$2y$10$zSBL9.3RY7rRYjsUm02PEu7kYSdpjp47ULZAg1X9mCqQOzevVhQWq', 'Tour & Concert Admin', 'admin', '2026-11-13 02:29:54', '2026-11-13 02:13:03'),
(5, 'editor_content', 'editor@greenday.com', '$2y$10$gG2/CsBFIA26.1qdGTE8AOTCQPNinkFwLRVmL1mVue2rCZDpqsYGO', 'Content & Media Editor', 'editor', NULL, '2026-11-13 02:13:03');

-- --------------------------------------------------------

--
-- Table structure for table `videos`
--

CREATE TABLE `videos` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `category_label` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `video_source` varchar(255) NOT NULL,
  `poster_image` varchar(255) NOT NULL,
  `duration` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `videos`
--
-- (Data dikosongkan agar ukuran repository ringan untuk di-upload ke GitHub)

--
-- Indexes for dumped tables
--

--
-- Indexes for table `albums`
--
ALTER TABLE `albums`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `band_members`
--
ALTER TABLE `band_members`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `community_posts`
--
ALTER TABLE `community_posts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `songs`
--
ALTER TABLE `songs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_songs_album` (`album_id`);

--
-- Indexes for table `ticket_bookings`
--
ALTER TABLE `ticket_bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_code` (`booking_code`),
  ADD KEY `fk_bookings_tour` (`tour_id`),
  ADD KEY `fk_bookings_category` (`category_id`);

--
-- Indexes for table `ticket_categories`
--
ALTER TABLE `ticket_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_categories_tour` (`tour_id`);

--
-- Indexes for table `tours`
--
ALTER TABLE `tours`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `albums`
--
ALTER TABLE `albums`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `band_members`
--
ALTER TABLE `band_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `community_posts`
--
ALTER TABLE `community_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `songs`
--
ALTER TABLE `songs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `ticket_bookings`
--
ALTER TABLE `ticket_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `ticket_categories`
--
ALTER TABLE `ticket_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `tours`
--
ALTER TABLE `tours`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `videos`
--
ALTER TABLE `videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `songs`
--
ALTER TABLE `songs`
  ADD CONSTRAINT `fk_songs_album` FOREIGN KEY (`album_id`) REFERENCES `albums` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `ticket_bookings`
--
ALTER TABLE `ticket_bookings`
  ADD CONSTRAINT `fk_bookings_category` FOREIGN KEY (`category_id`) REFERENCES `ticket_categories` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bookings_tour` FOREIGN KEY (`tour_id`) REFERENCES `tours` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `ticket_categories`
--
ALTER TABLE `ticket_categories`
  ADD CONSTRAINT `fk_categories_tour` FOREIGN KEY (`tour_id`) REFERENCES `tours` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
