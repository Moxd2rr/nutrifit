-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : dim. 06 juil. 2025 à 16:26
-- Version du serveur : 10.4.28-MariaDB
-- Version de PHP : 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `nutrifit_db`
--

DELIMITER $$
--
-- Procédures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `CheckProductStock` (IN `p_product_id` INT)   BEGIN
    SELECT id, name, stock, status
    FROM products
    WHERE id = p_product_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CreateOrder` (IN `p_user_id` INT, IN `p_total` DECIMAL(10,2), IN `p_shipping_address` TEXT, IN `p_billing_address` TEXT, IN `p_payment_method` VARCHAR(50))   BEGIN
    DECLARE v_order_number VARCHAR(50);
    SET v_order_number = CONCAT('ORD-', DATE_FORMAT(NOW(), '%Y%m%d'), '-', LPAD(p_user_id, 4, '0'), '-', LPAD(FLOOR(RAND() * 10000), 4, '0'));
    
    INSERT INTO orders (user_id, order_number, total, shipping_address, billing_address, payment_method)
    VALUES (p_user_id, v_order_number, p_total, p_shipping_address, p_billing_address, p_payment_method);
    
    SELECT LAST_INSERT_ID() as order_id, v_order_number as order_number;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateProductStock` (IN `p_product_id` INT, IN `p_quantity` INT)   BEGIN
    UPDATE products 
    SET stock = stock - p_quantity,
        updated_at = NOW()
    WHERE id = p_product_id AND stock >= p_quantity;
    
    SELECT ROW_COUNT() as updated_rows;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `active_subscriptions`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `active_subscriptions` (
`id` int(11)
,`start_date` date
,`end_date` date
,`status` enum('active','expired','cancelled')
,`user_name` varchar(100)
,`user_email` varchar(150)
,`program_title` varchar(150)
);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `available_products`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `available_products` (
`id` int(11)
,`name` varchar(150)
,`category` varchar(50)
,`description` text
,`price` decimal(10,2)
,`stock` int(11)
,`image_url` varchar(255)
);

-- --------------------------------------------------------

--
-- Structure de la table `completed_workouts`
--

CREATE TABLE `completed_workouts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `workout_id` int(11) NOT NULL,
  `completed_date` datetime NOT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `exercises`
--

CREATE TABLE `exercises` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `muscle_group` varchar(100) DEFAULT NULL,
  `equipment` varchar(100) DEFAULT NULL,
  `difficulty` enum('beginner','intermediate','advanced') DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `meals`
--

CREATE TABLE `meals` (
  `id` int(11) NOT NULL,
  `nutrition_plan_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `meal_type` enum('breakfast','lunch','dinner','snack') DEFAULT NULL,
  `calories` int(11) DEFAULT NULL,
  `protein_grams` int(11) DEFAULT NULL,
  `carbs_grams` int(11) DEFAULT NULL,
  `fat_grams` int(11) DEFAULT NULL,
  `day_of_week` int(11) DEFAULT NULL,
  `week_number` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `type` enum('workout_reminder','nutrition_reminder','progress_update','system','order_update') NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `nutrition_plans`
--

CREATE TABLE `nutrition_plans` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `calories_per_day` int(11) DEFAULT NULL,
  `protein_grams` int(11) DEFAULT NULL,
  `carbs_grams` int(11) DEFAULT NULL,
  `fat_grams` int(11) DEFAULT NULL,
  `difficulty` enum('beginner','intermediate','advanced') DEFAULT NULL,
  `duration_weeks` int(11) DEFAULT 4,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `nutrition_plans`
--

INSERT INTO `nutrition_plans` (`id`, `name`, `description`, `calories_per_day`, `protein_grams`, `carbs_grams`, `fat_grams`, `difficulty`, `duration_weeks`, `status`, `created_at`) VALUES
(1, 'Plan Équilibré Débutant', 'Plan nutritionnel équilibré pour débutants', 2000, 150, 200, 70, 'beginner', 4, 'active', '2025-06-29 18:55:34'),
(2, 'Plan Musculation', 'Plan riche en protéines pour prise de muscle', 2500, 200, 250, 80, 'intermediate', 8, 'active', '2025-06-29 18:55:34'),
(3, 'Plan Perte de Poids', 'Plan hypocalorique pour perdre du poids', 1600, 140, 150, 55, 'intermediate', 12, 'active', '2025-06-29 18:55:34'),
(4, 'Plan Végétarien', 'Plan nutritionnel végétarien équilibré', 1800, 120, 220, 65, 'beginner', 6, 'active', '2025-06-29 18:55:34'),
(5, 'Plan Équilibré Débutant', 'Plan nutritionnel équilibré pour débutants', 2000, 150, 200, 70, 'beginner', 4, 'active', '2025-06-29 18:57:58'),
(6, 'Plan Musculation', 'Plan riche en protéines pour prise de muscle', 2500, 200, 250, 80, 'intermediate', 8, 'active', '2025-06-29 18:57:58'),
(7, 'Plan Perte de Poids', 'Plan hypocalorique pour perdre du poids', 1600, 140, 150, 55, 'intermediate', 12, 'active', '2025-06-29 18:57:58'),
(8, 'Plan Végétarien', 'Plan nutritionnel végétarien équilibré', 1800, 120, 220, 65, 'beginner', 6, 'active', '2025-06-29 18:57:58');

-- --------------------------------------------------------

--
-- Structure de la table `nutrition_subscriptions`
--

CREATE TABLE `nutrition_subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nutrition_plan_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','expired','cancelled') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(50) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `shipping_address` text DEFAULT NULL,
  `billing_address` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déclencheurs `orders`
--
DELIMITER $$
CREATE TRIGGER `order_notification` AFTER INSERT ON `orders` FOR EACH ROW BEGIN
    INSERT INTO notifications (user_id, title, message, type)
    VALUES (NEW.user_id, 'Nouvelle commande', CONCAT('Votre commande #', NEW.order_number, ' a été créée avec succès.'), 'order_update');
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `order_details`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `order_details` (
`id` int(11)
,`order_number` varchar(50)
,`total` decimal(10,2)
,`status` enum('pending','processing','shipped','delivered','cancelled')
,`created_at` datetime
,`customer_name` varchar(100)
,`customer_email` varchar(150)
);

-- --------------------------------------------------------

--
-- Structure de la table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `platform_subscriptions`
--

CREATE TABLE `platform_subscriptions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration_months` int(11) DEFAULT 1,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `platform_subscriptions`
--

INSERT INTO `platform_subscriptions` (`id`, `name`, `description`, `price`, `duration_months`, `features`, `status`, `created_at`) VALUES
(1, 'Basique', 'Accès aux plans nutritionnels et suivi de progression', 9.00, 1, '[\"nutrition_plans\", \"progress_tracking\"]', 'active', '2025-06-29 18:55:34'),
(2, 'Premium', 'Accès complet avec coaching personnalisé et assistance prioritaire', 19.00, 1, '[\"nutrition_plans\", \"progress_tracking\", \"personal_coaching\", \"priority_support\"]', 'active', '2025-06-29 18:55:34'),
(3, 'Pro', 'Suivi avancé par un expert avec analyse détaillée des résultats', 29.00, 1, '[\"nutrition_plans\", \"progress_tracking\", \"personal_coaching\", \"priority_support\", \"expert_analysis\"]', 'active', '2025-06-29 18:55:34'),
(4, 'Basique', 'Accès aux plans nutritionnels et suivi de progression', 9.00, 1, '[\"nutrition_plans\", \"progress_tracking\"]', 'active', '2025-06-29 18:57:58'),
(5, 'Premium', 'Accès complet avec coaching personnalisé et assistance prioritaire', 19.00, 1, '[\"nutrition_plans\", \"progress_tracking\", \"personal_coaching\", \"priority_support\"]', 'active', '2025-06-29 18:57:58'),
(6, 'Pro', 'Suivi avancé par un expert avec analyse détaillée des résultats', 29.00, 1, '[\"nutrition_plans\", \"progress_tracking\", \"personal_coaching\", \"priority_support\", \"expert_analysis\"]', 'active', '2025-06-29 18:57:58');

-- --------------------------------------------------------

--
-- Structure de la table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `category` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `image_url` varchar(255) DEFAULT NULL,
  `status` enum('available','unavailable') DEFAULT 'available',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `products`
--

INSERT INTO `products` (`id`, `name`, `category`, `description`, `price`, `stock`, `image_url`, `status`, `created_at`, `updated_at`) VALUES
(1, 'T-shirt d\'entraînement', 'vetement', 'T-shirt léger et respirant pour le sport, idéal pour toutes vos séances.', 19.99, 150, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 18:55:34', '2025-06-29 19:48:35'),
(2, 'Legging de sport', 'vetement', 'Legging confortable et élastique pour vos entraînements.', 29.99, 100, 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=400&h=400&fit=crop', 'available', '2025-06-29 18:55:34', '2025-06-29 19:48:35'),
(3, 'Short de sport', 'vetement', 'Short léger et confortable pour les activités sportives.', 24.99, 80, 'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?w=400&h=400&fit=crop', 'available', '2025-06-29 18:55:34', '2025-06-29 19:48:35'),
(4, 'Shaker 700ml', 'equipement', 'Shaker pratique avec grille anti-grumeaux pour vos boissons protéinées.', 9.99, 200, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 18:55:34', '2025-06-29 20:37:34'),
(5, 'Tapis de yoga', 'equipement', 'Tapis de fitness antidérapant, parfait pour le yoga et les exercices au sol.', 29.99, 75, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 18:55:34', '2025-06-29 19:48:35'),
(6, 'Kettlebell 10kg', 'equipement', 'Kettlebell en fonte avec revêtement anti-dérapant pour entraînement fonctionnel.', 49.99, 50, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 18:55:34', '2025-06-29 20:37:34'),
(7, 'Corde à sauter', 'equipement', 'Corde à sauter ajustable pour cardio et endurance.', 14.99, 120, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 18:55:34', '2025-06-29 19:48:35'),
(8, 'Haltères 5kg', 'equipement', 'Paire d\'haltères en fonte pour musculation.', 39.99, 60, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 18:55:34', '2025-06-29 19:48:35'),
(9, 'Whey Protein Premium', 'supplement', 'Protéine de lactosérum haute qualité pour prise de muscle et récupération.', 59.99, 80, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 18:55:34', '2025-06-29 19:48:35'),
(10, 'BCAA Fruits Rouges', 'supplement', 'Acides aminés ramifiés saveur fruits rouges pour récupération musculaire.', 29.99, 100, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 18:55:34', '2025-06-29 19:48:35'),
(11, 'Créatine Monohydrate', 'supplement', 'Créatine pure pour amélioration des performances et force.', 19.99, 90, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 18:55:34', '2025-06-29 19:48:35'),
(12, 'Gourde 1L', 'accessoires', 'Gourde isotherme pour maintenir vos boissons fraîches.', 12.99, 150, 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=400&h=400&fit=crop', 'available', '2025-06-29 18:55:34', '2025-06-29 20:37:34'),
(13, 'Bandes élastiques', 'accessoires', 'Set de 5 bandes élastiques de résistance variable.', 15.99, 100, 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=400&h=400&fit=crop', 'available', '2025-06-29 18:55:34', '2025-06-29 20:37:34'),
(14, 'Gants de fitness', 'accessoires', 'Gants respirants pour protéger vos mains pendant l\'entraînement.', 18.99, 70, 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=400&h=400&fit=crop', 'available', '2025-06-29 18:55:34', '2025-06-29 20:37:34'),
(15, 'Barre protéinée Chocolat', 'alimentation', 'Barre riche en protéines et faible en sucres, parfaite pour la récupération.', 2.50, 300, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 18:55:34', '2025-06-29 19:48:35'),
(16, 'Barre protéinée Vanille', 'alimentation', 'Barre protéinée saveur vanille, idéale en collation saine.', 2.50, 250, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 18:55:34', '2025-06-29 19:48:35'),
(17, 'Mix de fruits secs', 'alimentation', 'Mélange de fruits secs et oléagineux pour l\'énergie.', 8.99, 120, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 18:55:34', '2025-06-29 19:48:35'),
(18, 'T-shirt d\'entraînement', 'vetement', 'T-shirt léger et respirant pour le sport, idéal pour toutes vos séances.', 19.99, 150, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 18:57:58', '2025-06-29 19:48:35'),
(19, 'Legging de sport', 'vetement', 'Legging confortable et élastique pour vos entraînements.', 29.99, 100, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 18:57:58', '2025-06-29 19:48:35'),
(20, 'Short de sport', 'vetement', 'Short léger et confortable pour les activités sportives.', 24.99, 80, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 18:57:58', '2025-06-29 19:48:35'),
(21, 'Shaker 700ml', 'equipement', 'Shaker pratique avec grille anti-grumeaux pour vos boissons protéinées.', 9.99, 200, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 18:57:58', '2025-06-29 19:48:35'),
(22, 'Tapis de yoga', 'equipement', 'Tapis de fitness antidérapant, parfait pour le yoga et les exercices au sol.', 29.99, 75, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 18:57:58', '2025-06-29 19:48:35'),
(23, 'Kettlebell 10kg', 'equipement', 'Kettlebell en fonte avec revêtement anti-dérapant pour entraînement fonctionnel.', 49.99, 50, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 18:57:58', '2025-06-29 19:48:35'),
(24, 'Corde à sauter', 'equipement', 'Corde à sauter ajustable pour cardio et endurance.', 14.99, 120, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 18:57:58', '2025-06-29 19:48:35'),
(25, 'Haltères 5kg', 'equipement', 'Paire d\'haltères en fonte pour musculation.', 39.99, 60, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 18:57:58', '2025-06-29 19:48:35'),
(26, 'Whey Protein Premium', 'supplement', 'Protéine de lactosérum haute qualité pour prise de muscle et récupération.', 59.99, 80, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 18:57:58', '2025-06-29 19:48:35'),
(27, 'BCAA Fruits Rouges', 'supplement', 'Acides aminés ramifiés saveur fruits rouges pour récupération musculaire.', 29.99, 100, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 18:57:58', '2025-06-29 19:48:35'),
(28, 'Créatine Monohydrate', 'supplement', 'Créatine pure pour amélioration des performances et force.', 19.99, 90, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 18:57:58', '2025-06-29 19:48:35'),
(29, 'Gourde 1L', 'accessoires', 'Gourde isotherme pour maintenir vos boissons fraîches.', 12.99, 150, 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=400&h=400&fit=crop', 'available', '2025-06-29 18:57:58', '2025-06-29 20:37:34'),
(30, 'Bandes élastiques', 'accessoires', 'Set de 5 bandes élastiques de résistance variable.', 15.99, 100, 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=400&h=400&fit=crop', 'available', '2025-06-29 18:57:58', '2025-06-29 20:37:34'),
(31, 'Gants de fitness', 'accessoires', 'Gants respirants pour protéger vos mains pendant l\'entraînement.', 18.99, 70, 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=400&h=400&fit=crop', 'available', '2025-06-29 18:57:58', '2025-06-29 20:37:34'),
(32, 'Barre protéinée Chocolat', 'alimentation', 'Barre riche en protéines et faible en sucres, parfaite pour la récupération.', 2.50, 300, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 18:57:58', '2025-06-29 19:48:35'),
(33, 'Barre protéinée Vanille', 'alimentation', 'Barre protéinée saveur vanille, idéale en collation saine.', 2.50, 250, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 18:57:58', '2025-06-29 19:48:35'),
(34, 'Mix de fruits secs', 'alimentation', 'Mélange de fruits secs et oléagineux pour l\'énergie.', 8.99, 120, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 18:57:58', '2025-06-29 19:48:35'),
(35, 'Whey Protein Premium', 'proteine', 'Protéine de lactosérum de haute qualité, 25g de protéines par portion. Idéal pour la récupération musculaire et la construction de masse.', 29.99, 50, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 20:37:33', '2025-06-29 20:37:33'),
(36, 'BCAA Complex', 'proteine', 'Acides aminés branchés essentiels pour la récupération musculaire et la prévention du catabolisme.', 24.99, 30, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 20:37:33', '2025-06-29 20:37:33'),
(37, 'Caseine Micellaire', 'proteine', 'Protéine à libération lente, parfaite pour la nuit et la récupération prolongée.', 34.99, 25, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 20:37:33', '2025-06-29 20:37:33'),
(38, 'Multivitamines Sport', 'vitamines', 'Complexe vitaminique complet spécialement formulé pour les sportifs actifs.', 19.99, 40, 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=400&h=400&fit=crop', 'available', '2025-06-29 20:37:33', '2025-06-29 20:37:33'),
(39, 'Vitamine D3', 'vitamines', 'Vitamine D3 naturelle pour renforcer les os et le système immunitaire.', 14.99, 60, 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=400&h=400&fit=crop', 'available', '2025-06-29 20:37:33', '2025-06-29 20:37:33'),
(40, 'Magnésium Sport', 'vitamines', 'Magnésium hautement biodisponible pour la relaxation musculaire et la récupération.', 16.99, 35, 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=400&h=400&fit=crop', 'available', '2025-06-29 20:37:33', '2025-06-29 20:37:33'),
(41, 'Haltères Ajustables', 'equipement', 'Paire d\'haltères ajustables de 2 à 20kg, idéales pour l\'entraînement à domicile.', 89.99, 15, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 20:37:33', '2025-06-29 20:37:33'),
(42, 'Tapis de Yoga Premium', 'equipement', 'Tapis de yoga antidérapant et écologique, épaisseur 6mm pour un confort optimal.', 39.99, 25, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 20:37:33', '2025-06-29 20:37:33'),
(43, 'Corde à Sauter Pro', 'equipement', 'Corde à sauter professionnelle avec roulements à billes et longueur ajustable.', 24.99, 30, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 20:37:33', '2025-06-29 20:37:33'),
(44, 'Bandes Élastiques Set', 'equipement', 'Set de 5 bandes élastiques de résistance variable pour un entraînement complet.', 19.99, 45, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 20:37:33', '2025-06-29 20:37:33'),
(45, 'Barres Protéinées', 'nutrition', 'Pack de 12 barres protéinées, 20g de protéines par barre, saveur chocolat.', 29.99, 40, 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=400&h=400&fit=crop', 'available', '2025-06-29 20:37:33', '2025-06-29 20:37:34'),
(46, 'Shaker Premium', 'nutrition', 'Shaker 600ml avec filtre anti-grumeaux et design ergonomique.', 14.99, 50, 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=400&h=400&fit=crop', 'available', '2025-06-29 20:37:33', '2025-06-29 20:37:34'),
(47, 'Gel Énergétique', 'nutrition', 'Gels énergétiques isotoniques pour l\'endurance, pack de 6 unités.', 18.99, 35, 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=400&h=400&fit=crop', 'available', '2025-06-29 20:37:33', '2025-06-29 20:37:34'),
(48, 'T-shirt Technique', 'vetements', 'T-shirt respirant et anti-transpiration pour l\'entraînement intensif.', 34.99, 30, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 20:37:33', '2025-06-29 20:37:33'),
(49, 'Leggings Fitness', 'vetements', 'Leggings haute compression avec poche zippée, parfaits pour le yoga et la course.', 49.99, 25, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 20:37:33', '2025-06-29 20:37:33'),
(50, 'Chaussures Running', 'vetements', 'Chaussures de running légères et amortissantes pour tous types de terrains.', 79.99, 20, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 20:37:33', '2025-06-29 20:37:33'),
(51, 'Gourde Isotherme', 'accessoires', 'Gourde isotherme 750ml, maintient la température 24h, design moderne.', 24.99, 40, 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=400&h=400&fit=crop', 'available', '2025-06-29 20:37:33', '2025-06-29 20:37:34'),
(52, 'Montre Sport GPS', 'accessoires', 'Montre GPS avec suivi cardiaque et analyse des performances sportives.', 199.99, 10, 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=400&h=400&fit=crop', 'available', '2025-06-29 20:37:33', '2025-06-29 20:37:34'),
(53, 'Sac de Sport', 'accessoires', 'Sac de sport 30L avec compartiments organisés et chaussures séparées.', 44.99, 20, 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=400&h=400&fit=crop', 'available', '2025-06-29 20:37:33', '2025-06-29 20:37:34'),
(54, 'Rouleau de Massage', 'recuperation', 'Rouleau de massage en mousse haute densité pour la récupération musculaire.', 29.99, 30, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 20:37:33', '2025-06-29 20:37:33'),
(55, 'Balle de Tennis', 'recuperation', 'Set de 2 balles de tennis pour l\'auto-massage et la libération myofasciale.', 8.99, 50, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 20:37:33', '2025-06-29 20:37:33'),
(56, 'Gants de Compression', 'recuperation', 'Gants de compression pour améliorer la circulation et la récupération.', 19.99, 25, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop', 'available', '2025-06-29 20:37:33', '2025-06-29 20:37:33'),
(57, 'Pack Débutant Fitness', 'packs', 'Pack complet pour débuter le fitness : haltères, tapis, shaker et guide nutrition.', 99.99, 15, 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=400&h=400&fit=crop', 'available', '2025-06-29 20:37:33', '2025-06-29 20:37:34'),
(58, 'Pack Nutrition Pro', 'packs', 'Pack nutrition complet : whey, BCAA, multivitamines et barres protéinées.', 89.99, 12, 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=400&h=400&fit=crop', 'available', '2025-06-29 20:37:33', '2025-06-29 20:37:34'),
(59, 'Pack Récupération', 'packs', 'Pack récupération : rouleau, balles, gants et gel de massage.', 49.99, 18, 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=400&h=400&fit=crop', 'available', '2025-06-29 20:37:33', '2025-06-29 20:37:34');

--
-- Déclencheurs `products`
--
DELIMITER $$
CREATE TRIGGER `update_product_status` AFTER UPDATE ON `products` FOR EACH ROW BEGIN
    IF NEW.stock = 0 AND OLD.stock > 0 THEN
        UPDATE products SET status = 'unavailable' WHERE id = NEW.id;
    ELSEIF NEW.stock > 0 AND OLD.stock = 0 THEN
        UPDATE products SET status = 'available' WHERE id = NEW.id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `programs`
--

CREATE TABLE `programs` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `duration_weeks` int(11) DEFAULT 4,
  `difficulty` enum('beginner','intermediate','advanced') DEFAULT NULL,
  `category` enum('strength','cardio','flexibility','weight_loss','muscle_gain','general_fitness') DEFAULT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `programs`
--

INSERT INTO `programs` (`id`, `title`, `description`, `duration_weeks`, `difficulty`, `category`, `price`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Programme Débutant Complet', 'Programme d\'initiation au fitness pour débutants', 8, 'beginner', 'general_fitness', 29.99, 'active', NULL, '2025-06-29 18:55:34', '2025-06-29 18:55:34'),
(2, 'Musculation Avancée', 'Programme intensif de musculation pour niveaux avancés', 12, 'advanced', 'muscle_gain', 49.99, 'active', NULL, '2025-06-29 18:55:34', '2025-06-29 18:55:34'),
(3, 'Cardio Training', 'Programme cardio pour améliorer l\'endurance', 6, 'intermediate', 'cardio', 24.99, 'active', NULL, '2025-06-29 18:55:34', '2025-06-29 18:55:34'),
(4, 'Perte de Poids', 'Programme combiné cardio et musculation pour perdre du poids', 10, 'intermediate', 'weight_loss', 39.99, 'active', NULL, '2025-06-29 18:55:34', '2025-06-29 18:55:34'),
(5, 'Yoga et Flexibilité', 'Programme de yoga pour améliorer la flexibilité', 4, 'beginner', 'flexibility', 19.99, 'active', NULL, '2025-06-29 18:55:34', '2025-06-29 18:55:34'),
(6, 'Programme Débutant Complet', 'Programme d\'initiation au fitness pour débutants', 8, 'beginner', 'general_fitness', 29.99, 'active', NULL, '2025-06-29 18:57:58', '2025-06-29 18:57:58'),
(7, 'Musculation Avancée', 'Programme intensif de musculation pour niveaux avancés', 12, 'advanced', 'muscle_gain', 49.99, 'active', NULL, '2025-06-29 18:57:58', '2025-06-29 18:57:58'),
(8, 'Cardio Training', 'Programme cardio pour améliorer l\'endurance', 6, 'intermediate', 'cardio', 24.99, 'active', NULL, '2025-06-29 18:57:58', '2025-06-29 18:57:58'),
(9, 'Perte de Poids', 'Programme combiné cardio et musculation pour perdre du poids', 10, 'intermediate', 'weight_loss', 39.99, 'active', NULL, '2025-06-29 18:57:58', '2025-06-29 18:57:58'),
(10, 'Yoga et Flexibilité', 'Programme de yoga pour améliorer la flexibilité', 4, 'beginner', 'flexibility', 19.99, 'active', NULL, '2025-06-29 18:57:58', '2025-06-29 18:57:58');

-- --------------------------------------------------------

--
-- Structure de la table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','expired','cancelled') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `image_url` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `testimonials`
--

INSERT INTO `testimonials` (`id`, `user_id`, `name`, `content`, `rating`, `image_url`, `status`, `created_at`) VALUES
(1, NULL, 'Sophie L.', 'Grâce à NutriFit, j\'ai enfin trouvé l\'équilibre entre alimentation et sport !', 5, 'https://randomuser.me/api/portraits/women/44.jpg', 'active', '2025-06-29 18:55:34'),
(2, NULL, 'Marc D.', 'L\'accompagnement personnalisé m\'a permis d\'atteindre mes objectifs plus rapidement.', 5, 'https://randomuser.me/api/portraits/men/32.jpg', 'active', '2025-06-29 18:55:34'),
(3, NULL, 'Julie P.', 'Une application intuitive et des résultats visibles !', 4, 'https://randomuser.me/api/portraits/women/68.jpg', 'active', '2025-06-29 18:55:34'),
(4, NULL, 'Thomas B.', 'Excellent programme de musculation, je vois déjà des résultats après 3 semaines.', 5, 'https://randomuser.me/api/portraits/men/45.jpg', 'active', '2025-06-29 18:55:34'),
(5, NULL, 'Emma R.', 'Les plans nutritionnels sont parfaits et faciles à suivre.', 4, 'https://randomuser.me/api/portraits/women/23.jpg', 'active', '2025-06-29 18:55:34'),
(6, NULL, 'Sophie L.', 'Grâce à NutriFit, j\'ai enfin trouvé l\'équilibre entre alimentation et sport !', 5, 'https://randomuser.me/api/portraits/women/44.jpg', 'active', '2025-06-29 18:57:58'),
(7, NULL, 'Marc D.', 'L\'accompagnement personnalisé m\'a permis d\'atteindre mes objectifs plus rapidement.', 5, 'https://randomuser.me/api/portraits/men/32.jpg', 'active', '2025-06-29 18:57:58'),
(8, NULL, 'Julie P.', 'Une application intuitive et des résultats visibles !', 4, 'https://randomuser.me/api/portraits/women/68.jpg', 'active', '2025-06-29 18:57:58'),
(9, NULL, 'Thomas B.', 'Excellent programme de musculation, je vois déjà des résultats après 3 semaines.', 5, 'https://randomuser.me/api/portraits/men/45.jpg', 'active', '2025-06-29 18:57:58'),
(10, NULL, 'Emma R.', 'Les plans nutritionnels sont parfaits et faciles à suivre.', 4, 'https://randomuser.me/api/portraits/women/23.jpg', 'active', '2025-06-29 18:57:58');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','coach','user') DEFAULT 'user',
  `status` enum('active','inactive','banned') DEFAULT 'active',
  `phone` varchar(20) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `fitness_level` enum('beginner','intermediate','advanced') DEFAULT NULL,
  `goals` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `status`, `phone`, `birth_date`, `gender`, `height`, `weight`, `fitness_level`, `goals`, `created_at`, `updated_at`) VALUES
(1, 'Administrateur', 'admin@nutrifit.com', '40bd001563085fc35165329ea1ff5c5ecbdbbeef', 'admin', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-29 19:04:19', '2025-06-29 19:04:19'),
(2, 'darkdevil', 'moolby2006@gmail.com', '$2y$10$qeTK/uXBGjusZ3Q6D2DBbOprzDHL/Qa5DfMCn.WwKZ.QzF686RMXu', 'user', 'active', 'j)^h)-àj', '0002-02-22', NULL, NULL, NULL, 'beginner', '', '2025-06-29 19:55:03', '2025-06-29 19:55:03'),
(3, 'MAIGA MOHAMED', 'moolby223@yahoo.com', '$2y$10$G2dUSYElsQQrvfcEDhR2/uLur0T7KNSL1Atw/wptv0sf0YUE30VeK', 'user', 'active', '91030292', '2025-01-02', NULL, NULL, NULL, 'beginner', '', '2025-07-01 21:49:45', '2025-07-01 21:49:45'),
(4, 'MAIGA MOHAMED', 'admin@admin.com', '$2y$10$vWW/mUpA3cKnpP96BhC3MO2cdeS/N4XJAyTF9rcij4AfiU3LPZbZ6', 'admin', 'active', '91030292', '2025-01-31', NULL, NULL, NULL, 'intermediate', 'll', '2025-07-03 23:08:10', '2025-07-03 23:08:56'),
(5, 'Alice Smith', 'alice@example.com', '$2y$10$VagEuNf.EgSOe1eWWpzZf./M4OtX1vgWVohIQs.ja/N4FTvNdQMxK', 'user', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-04 10:04:41', '2025-07-04 10:04:41'),
(6, 'Bob Johnson', 'bob@example.com', '$2y$10$I/PzfBtEvxfFP3CDq9OVb.akYImXbS1jObF56E3QQtoHbMTyVcZ.K', 'coach', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-04 10:04:42', '2025-07-04 10:04:42'),
(7, 'Charlie Brown', 'charlie@example.com', '$2y$10$ejr8Slf9P1k3GniENWBXKegZQWgvGcb/US8RLFByuWiKfLQQ56aVC', 'user', 'inactive', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-04 10:04:42', '2025-07-04 10:04:42'),
(8, 'Diana Prince', 'diana@example.com', '$2y$10$uPUFEaWboleiE4mj2oLFlO8zQbM52nQkUuuuqSWUVC01uGJe5Q4di', 'admin', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-04 10:04:42', '2025-07-04 10:04:42'),
(9, 'Eve Adams', 'eve@example.com', '$2y$10$fbFKEv54dSApR3OuaKMCGelNbk2LqrylvQjqGKSJP/ZVHiLfdb78i', 'user', 'banned', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-04 10:04:42', '2025-07-04 10:04:42'),
(10, 'darkdevil', 'mohamedmag543@gmail.com', '$2y$10$irBYA/p5x33n5FVd5pk6zOGxF2eCthiwGpMnDmYkjhMfrQTA4yiiW', 'user', 'active', '91030292', '2006-01-04', NULL, NULL, NULL, 'advanced', 'ikkkk', '2025-07-06 14:01:39', '2025-07-06 14:01:39');

-- --------------------------------------------------------

--
-- Structure de la table `user_platform_subscriptions`
--

CREATE TABLE `user_platform_subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `platform_subscription_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','expired','cancelled') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user_progress`
--

CREATE TABLE `user_progress` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date_recorded` date NOT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `body_fat_percentage` decimal(4,2) DEFAULT NULL,
  `muscle_mass` decimal(5,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `workouts`
--

CREATE TABLE `workouts` (
  `id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `day_of_week` int(11) DEFAULT NULL,
  `week_number` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `workout_exercises`
--

CREATE TABLE `workout_exercises` (
  `id` int(11) NOT NULL,
  `workout_id` int(11) NOT NULL,
  `exercise_id` int(11) NOT NULL,
  `sets` int(11) DEFAULT 3,
  `reps` int(11) DEFAULT 10,
  `duration_seconds` int(11) DEFAULT NULL,
  `rest_seconds` int(11) DEFAULT 60,
  `order_index` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la vue `active_subscriptions`
--
DROP TABLE IF EXISTS `active_subscriptions`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `active_subscriptions`  AS SELECT `s`.`id` AS `id`, `s`.`start_date` AS `start_date`, `s`.`end_date` AS `end_date`, `s`.`status` AS `status`, `u`.`name` AS `user_name`, `u`.`email` AS `user_email`, `p`.`title` AS `program_title` FROM ((`subscriptions` `s` join `users` `u` on(`s`.`user_id` = `u`.`id`)) join `programs` `p` on(`s`.`program_id` = `p`.`id`)) WHERE `s`.`status` = 'active' ;

-- --------------------------------------------------------

--
-- Structure de la vue `available_products`
--
DROP TABLE IF EXISTS `available_products`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `available_products`  AS SELECT `products`.`id` AS `id`, `products`.`name` AS `name`, `products`.`category` AS `category`, `products`.`description` AS `description`, `products`.`price` AS `price`, `products`.`stock` AS `stock`, `products`.`image_url` AS `image_url` FROM `products` WHERE `products`.`status` = 'available' AND `products`.`stock` > 0 ;

-- --------------------------------------------------------

--
-- Structure de la vue `order_details`
--
DROP TABLE IF EXISTS `order_details`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `order_details`  AS SELECT `o`.`id` AS `id`, `o`.`order_number` AS `order_number`, `o`.`total` AS `total`, `o`.`status` AS `status`, `o`.`created_at` AS `created_at`, `u`.`name` AS `customer_name`, `u`.`email` AS `customer_email` FROM (`orders` `o` join `users` `u` on(`o`.`user_id` = `u`.`id`)) ;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `completed_workouts`
--
ALTER TABLE `completed_workouts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `workout_id` (`workout_id`);

--
-- Index pour la table `exercises`
--
ALTER TABLE `exercises`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `meals`
--
ALTER TABLE `meals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nutrition_plan_id` (`nutrition_plan_id`);

--
-- Index pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user_id` (`user_id`),
  ADD KEY `idx_notifications_is_read` (`is_read`);

--
-- Index pour la table `nutrition_plans`
--
ALTER TABLE `nutrition_plans`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `nutrition_subscriptions`
--
ALTER TABLE `nutrition_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `nutrition_plan_id` (`nutrition_plan_id`);

--
-- Index pour la table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `idx_orders_user_id` (`user_id`),
  ADD KEY `idx_orders_status` (`status`);

--
-- Index pour la table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Index pour la table `platform_subscriptions`
--
ALTER TABLE `platform_subscriptions`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_products_category` (`category`),
  ADD KEY `idx_products_status` (`status`);

--
-- Index pour la table `programs`
--
ALTER TABLE `programs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Index pour la table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `program_id` (`program_id`),
  ADD KEY `idx_subscriptions_user_id` (`user_id`),
  ADD KEY `idx_subscriptions_status` (`status`);

--
-- Index pour la table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_email` (`email`),
  ADD KEY `idx_users_status` (`status`);

--
-- Index pour la table `user_platform_subscriptions`
--
ALTER TABLE `user_platform_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `platform_subscription_id` (`platform_subscription_id`);

--
-- Index pour la table `user_progress`
--
ALTER TABLE `user_progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `workouts`
--
ALTER TABLE `workouts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `program_id` (`program_id`);

--
-- Index pour la table `workout_exercises`
--
ALTER TABLE `workout_exercises`
  ADD PRIMARY KEY (`id`),
  ADD KEY `workout_id` (`workout_id`),
  ADD KEY `exercise_id` (`exercise_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `completed_workouts`
--
ALTER TABLE `completed_workouts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `exercises`
--
ALTER TABLE `exercises`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `meals`
--
ALTER TABLE `meals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `nutrition_plans`
--
ALTER TABLE `nutrition_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `nutrition_subscriptions`
--
ALTER TABLE `nutrition_subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `platform_subscriptions`
--
ALTER TABLE `platform_subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT pour la table `programs`
--
ALTER TABLE `programs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `user_platform_subscriptions`
--
ALTER TABLE `user_platform_subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `user_progress`
--
ALTER TABLE `user_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `workouts`
--
ALTER TABLE `workouts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `workout_exercises`
--
ALTER TABLE `workout_exercises`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `completed_workouts`
--
ALTER TABLE `completed_workouts`
  ADD CONSTRAINT `completed_workouts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `completed_workouts_ibfk_2` FOREIGN KEY (`workout_id`) REFERENCES `workouts` (`id`);

--
-- Contraintes pour la table `meals`
--
ALTER TABLE `meals`
  ADD CONSTRAINT `meals_ibfk_1` FOREIGN KEY (`nutrition_plan_id`) REFERENCES `nutrition_plans` (`id`);

--
-- Contraintes pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `nutrition_subscriptions`
--
ALTER TABLE `nutrition_subscriptions`
  ADD CONSTRAINT `nutrition_subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `nutrition_subscriptions_ibfk_2` FOREIGN KEY (`nutrition_plan_id`) REFERENCES `nutrition_plans` (`id`);

--
-- Contraintes pour la table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Contraintes pour la table `programs`
--
ALTER TABLE `programs`
  ADD CONSTRAINT `programs_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `subscriptions_ibfk_2` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`);

--
-- Contraintes pour la table `testimonials`
--
ALTER TABLE `testimonials`
  ADD CONSTRAINT `testimonials_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `user_platform_subscriptions`
--
ALTER TABLE `user_platform_subscriptions`
  ADD CONSTRAINT `user_platform_subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_platform_subscriptions_ibfk_2` FOREIGN KEY (`platform_subscription_id`) REFERENCES `platform_subscriptions` (`id`);

--
-- Contraintes pour la table `user_progress`
--
ALTER TABLE `user_progress`
  ADD CONSTRAINT `user_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `workouts`
--
ALTER TABLE `workouts`
  ADD CONSTRAINT `workouts_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`);

--
-- Contraintes pour la table `workout_exercises`
--
ALTER TABLE `workout_exercises`
  ADD CONSTRAINT `workout_exercises_ibfk_1` FOREIGN KEY (`workout_id`) REFERENCES `workouts` (`id`),
  ADD CONSTRAINT `workout_exercises_ibfk_2` FOREIGN KEY (`exercise_id`) REFERENCES `exercises` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
