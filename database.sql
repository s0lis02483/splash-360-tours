-- FILE: /database.sql
-- Splash360 Tours Database Schema
-- Multi-tenant SaaS platform for 360° virtual tours

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ============================================================
-- TENANTS TABLE
-- ============================================================

CREATE TABLE `tenants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `api_key` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `api_key` (`api_key`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- USERS TABLE
-- ============================================================

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('platform_admin','tenant_admin','user') DEFAULT 'user',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `tenant_id` (`tenant_id`),
  KEY `role` (`role`),
  KEY `status` (`status`),
  CONSTRAINT `users_tenant_fk` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- PLANS TABLE
-- ============================================================

CREATE TABLE `plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `price` decimal(10,2) DEFAULT 0.00,
  `billing_period` enum('monthly','yearly','lifetime') DEFAULT 'monthly',
  `max_properties` int(11) DEFAULT 10,
  `max_tours` int(11) DEFAULT 10,
  `max_scenes` int(11) DEFAULT 50,
  `max_storage_size` int(11) DEFAULT 1024 COMMENT 'In MB',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TENANT SUBSCRIPTIONS TABLE
-- ============================================================

CREATE TABLE `tenant_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `status` enum('active','expired','cancelled') DEFAULT 'active',
  `started_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `plan_id` (`plan_id`),
  KEY `status` (`status`),
  CONSTRAINT `subscriptions_tenant_fk` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subscriptions_plan_fk` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- INVOICES TABLE
-- ============================================================

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `plan_id` int(11) DEFAULT NULL,
  `invoice_number` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','cancelled') DEFAULT 'pending',
  `due_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `tenant_id` (`tenant_id`),
  KEY `plan_id` (`plan_id`),
  KEY `status` (`status`),
  CONSTRAINT `invoices_tenant_fk` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoices_plan_fk` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- PAYMENTS TABLE
-- ============================================================

CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT 'dummy',
  `transaction_id` varchar(255) DEFAULT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `status` (`status`),
  CONSTRAINT `payments_invoice_fk` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_tenant_fk` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- PROPERTIES TABLE
-- ============================================================

CREATE TABLE `properties` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `type` enum('apartment','house','villa','office','land','commercial','other') DEFAULT 'apartment',
  `status` enum('available','sold','rented','pending') DEFAULT 'available',
  `price` decimal(12,2) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text,
  `main_image` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `type` (`type`),
  KEY `status` (`status`),
  CONSTRAINT `properties_tenant_fk` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TOURS TABLE
-- ============================================================

CREATE TABLE `tours` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text,
  `status` enum('draft','published') DEFAULT 'draft',
  `is_public` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `tenant_id` (`tenant_id`),
  KEY `property_id` (`property_id`),
  KEY `status` (`status`),
  KEY `is_public` (`is_public`),
  KEY `is_featured` (`is_featured`),
  CONSTRAINT `tours_tenant_fk` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tours_property_fk` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SCENES TABLE
-- ============================================================

CREATE TABLE `scenes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tour_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `initial_yaw` float DEFAULT 0,
  `initial_pitch` float DEFAULT 0,
  `order_index` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tour_id` (`tour_id`),
  KEY `order_index` (`order_index`),
  CONSTRAINT `scenes_tour_fk` FOREIGN KEY (`tour_id`) REFERENCES `tours` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- HOTSPOTS TABLE
-- ============================================================

CREATE TABLE `hotspots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `scene_id` int(11) NOT NULL,
  `type` enum('navigation','info','link') NOT NULL,
  `yaw` float NOT NULL,
  `pitch` float NOT NULL,
  `label` varchar(255) DEFAULT NULL,
  `description` text,
  `target_scene_id` int(11) DEFAULT NULL,
  `external_url` varchar(500) DEFAULT NULL,
  `icon_type` enum('arrow','info','link','custom') DEFAULT 'arrow',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `scene_id` (`scene_id`),
  KEY `target_scene_id` (`target_scene_id`),
  KEY `type` (`type`),
  CONSTRAINT `hotspots_scene_fk` FOREIGN KEY (`scene_id`) REFERENCES `scenes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hotspots_target_scene_fk` FOREIGN KEY (`target_scene_id`) REFERENCES `scenes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TOUR VIEWS TABLE (Analytics)
-- ============================================================

CREATE TABLE `tour_views` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tour_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `viewed_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tour_id` (`tour_id`),
  KEY `viewed_at` (`viewed_at`),
  CONSTRAINT `tour_views_tour_fk` FOREIGN KEY (`tour_id`) REFERENCES `tours` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEED DATA - PLANS
-- ============================================================

INSERT INTO `plans` (`name`, `description`, `price`, `billing_period`, `max_properties`, `max_tours`, `max_scenes`, `max_storage_size`, `status`) VALUES
('Free', 'Free plan for testing', 0.00, 'lifetime', 5, 5, 25, 100, 'active'),
('Starter', 'Perfect for small agencies', 29.00, 'monthly', 20, 20, 100, 500, 'active'),
('Professional', 'For growing businesses', 79.00, 'monthly', 100, 100, 500, 2000, 'active'),
('Enterprise', 'Unlimited access', 199.00, 'monthly', -1, -1, -1, 10000, 'active');

-- ============================================================
-- SEED DATA - PLATFORM ADMIN
-- ============================================================

INSERT INTO `users` (`tenant_id`, `name`, `email`, `password`, `role`, `status`) VALUES
(NULL, 'Platform Admin', 'admin@splash360.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'platform_admin', 'active');
-- Password: password

-- ============================================================
-- SEED DATA - TENANTS (Real Estate Agencies)
-- ============================================================

INSERT INTO `tenants` (`name`, `email`, `status`, `api_key`) VALUES
('Luxury Realty Group', 'info@luxuryrealty.com', 'active', 'sk_a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6'),
('Coastal Properties Inc', 'hello@coastalprops.com', 'active', 'sk_z9y8x7w6v5u4t3s2r1q0p9o8n7m6l5k4j3i2h1g0f9e8d7c6b5a4');

-- ============================================================
-- SEED DATA - SUBSCRIPTIONS
-- ============================================================

INSERT INTO `tenant_subscriptions` (`tenant_id`, `plan_id`, `status`, `started_at`, `expires_at`) VALUES
(1, 3, 'active', '2025-01-01 00:00:00', '2025-12-31 23:59:59'),
(2, 2, 'active', '2025-01-15 00:00:00', '2025-12-15 23:59:59');

-- ============================================================
-- SEED DATA - USERS FOR TENANTS
-- ============================================================

INSERT INTO `users` (`tenant_id`, `name`, `email`, `password`, `role`, `status`) VALUES
-- Luxury Realty Group users
(1, 'John Mitchell', 'john@luxuryrealty.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tenant_admin', 'active'),
(1, 'Sarah Johnson', 'sarah@luxuryrealty.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'active'),
(1, 'Michael Brown', 'michael@luxuryrealty.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'active'),
-- Coastal Properties Inc users
(2, 'Emma Davis', 'emma@coastalprops.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tenant_admin', 'active'),
(2, 'James Wilson', 'james@coastalprops.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'active');

-- ============================================================
-- SEED DATA - PROPERTIES FOR TENANT 1 (Luxury Realty Group)
-- ============================================================

INSERT INTO `properties` (`tenant_id`, `title`, `reference`, `type`, `status`, `price`, `location`, `description`) VALUES
(1, 'Modern Downtown Penthouse', 'LRG-001', 'apartment', 'available', 2500000.00, 'Downtown Manhattan, NY', 'Stunning 3-bedroom penthouse with panoramic city views, high-end finishes, and private terrace.'),
(1, 'Luxury Beachfront Villa', 'LRG-002', 'villa', 'available', 5800000.00, 'Malibu, CA', 'Exquisite 5-bedroom beachfront villa with infinity pool, private beach access, and modern architecture.'),
(1, 'Historic Brownstone', 'LRG-003', 'house', 'available', 3200000.00, 'Brooklyn Heights, NY', 'Beautifully restored 4-bedroom brownstone with original details and modern amenities.'),
(1, 'Corporate Office Space', 'LRG-004', 'office', 'available', 1800000.00, 'Midtown Manhattan, NY', 'Premium office space with 360° views, modern infrastructure, and prime location.');

-- ============================================================
-- SEED DATA - PROPERTIES FOR TENANT 2 (Coastal Properties Inc)
-- ============================================================

INSERT INTO `properties` (`tenant_id`, `title`, `reference`, `type`, `status`, `price`, `location`, `description`) VALUES
(2, 'Ocean View Condo', 'CP-001', 'apartment', 'available', 890000.00, 'Miami Beach, FL', 'Beautiful 2-bedroom condo with direct ocean views and resort-style amenities.'),
(2, 'Waterfront Estate', 'CP-002', 'villa', 'available', 4200000.00, 'Newport Beach, CA', 'Spectacular waterfront estate with private dock, pool, and entertaining spaces.'),
(2, 'Beach Cottage', 'CP-003', 'house', 'sold', 650000.00, 'Outer Banks, NC', 'Charming 3-bedroom beach cottage steps from the ocean.');

-- ============================================================
-- SEED DATA - TOURS FOR PROPERTIES
-- ============================================================

INSERT INTO `tours` (`tenant_id`, `property_id`, `title`, `slug`, `description`, `status`, `is_public`, `is_featured`) VALUES
-- Tours for Luxury Realty Group properties
(1, 1, 'Penthouse Virtual Tour', 'modern-downtown-penthouse-tour', 'Explore every corner of this stunning penthouse', 'published', 1, 1),
(1, 2, 'Villa 360 Experience', 'luxury-beachfront-villa-tour', 'Immerse yourself in luxury beachfront living', 'published', 1, 1),
(1, 3, 'Brownstone Walkthrough', 'historic-brownstone-tour', 'Step back in time with modern comfort', 'published', 1, 0),
(1, 4, 'Office Space Tour', 'corporate-office-space-tour', 'See your future workspace', 'draft', 0, 0),
-- Tours for Coastal Properties Inc properties
(2, 5, 'Condo Ocean Views', 'ocean-view-condo-tour', 'Wake up to breathtaking ocean views every day', 'published', 1, 1),
(2, 6, 'Waterfront Luxury', 'waterfront-estate-tour', 'Experience waterfront living at its finest', 'published', 1, 0),
(2, 7, 'Beach Cottage Tour', 'beach-cottage-tour', 'Your perfect beach getaway', 'published', 1, 0);

-- ============================================================
-- SEED DATA - SCENES FOR TOURS
-- ============================================================

-- Penthouse Tour Scenes (Tour ID 1)
INSERT INTO `scenes` (`tour_id`, `name`, `image_path`, `initial_yaw`, `initial_pitch`, `order_index`) VALUES
(1, 'Living Room', 'penthouse_living.jpg', 0, 0, 1),
(1, 'Master Bedroom', 'penthouse_bedroom.jpg', 90, 0, 2),
(1, 'Kitchen', 'penthouse_kitchen.jpg', 180, 0, 3),
(1, 'Terrace', 'penthouse_terrace.jpg', 270, -10, 4);

-- Villa Tour Scenes (Tour ID 2)
INSERT INTO `scenes` (`tour_id`, `name`, `image_path`, `initial_yaw`, `initial_pitch`, `order_index`) VALUES
(2, 'Grand Entrance', 'villa_entrance.jpg', 0, 0, 1),
(2, 'Living Area', 'villa_living.jpg', 45, 0, 2),
(2, 'Master Suite', 'villa_master.jpg', 90, 0, 3),
(2, 'Pool Deck', 'villa_pool.jpg', 135, -15, 4),
(2, 'Beach View', 'villa_beach.jpg', 180, -5, 5);

-- Brownstone Tour Scenes (Tour ID 3)
INSERT INTO `scenes` (`tour_id`, `name`, `image_path`, `initial_yaw`, `initial_pitch`, `order_index`) VALUES
(3, 'Foyer', 'brownstone_foyer.jpg', 0, 0, 1),
(3, 'Parlor', 'brownstone_parlor.jpg', 90, 0, 2),
(3, 'Dining Room', 'brownstone_dining.jpg', 180, 0, 3);

-- Condo Tour Scenes (Tour ID 5)
INSERT INTO `scenes` (`tour_id`, `name`, `image_path`, `initial_yaw`, `initial_pitch`, `order_index`) VALUES
(5, 'Living Room', 'condo_living.jpg', 0, 0, 1),
(5, 'Bedroom', 'condo_bedroom.jpg', 90, 0, 2),
(5, 'Balcony', 'condo_balcony.jpg', 180, -10, 3);

-- Waterfront Estate Tour Scenes (Tour ID 6)
INSERT INTO `scenes` (`tour_id`, `name`, `image_path`, `initial_yaw`, `initial_pitch`, `order_index`) VALUES
(6, 'Main Living', 'estate_living.jpg', 0, 0, 1),
(6, 'Dock Area', 'estate_dock.jpg', 90, -5, 2),
(6, 'Pool Area', 'estate_pool.jpg', 180, 0, 3);

-- Beach Cottage Tour Scenes (Tour ID 7)
INSERT INTO `scenes` (`tour_id`, `name`, `image_path`, `initial_yaw`, `initial_pitch`, `order_index`) VALUES
(7, 'Living Space', 'cottage_living.jpg', 0, 0, 1),
(7, 'Deck', 'cottage_deck.jpg', 90, -10, 2);

-- ============================================================
-- SEED DATA - HOTSPOTS
-- ============================================================

-- Hotspots for Penthouse Living Room (Scene 1)
INSERT INTO `hotspots` (`scene_id`, `type`, `yaw`, `pitch`, `label`, `description`, `target_scene_id`, `icon_type`) VALUES
(1, 'navigation', 90, 0, 'Go to Bedroom', 'View the luxurious master bedroom', 2, 'arrow'),
(1, 'navigation', 180, 0, 'Go to Kitchen', 'Check out the gourmet kitchen', 3, 'arrow'),
(1, 'navigation', 270, 0, 'Go to Terrace', 'Step outside to the private terrace', 4, 'arrow'),
(1, 'info', 45, -15, 'Smart Home Features', 'This penthouse includes state-of-the-art smart home technology', NULL, 'info');

-- Hotspots for Penthouse Bedroom (Scene 2)
INSERT INTO `hotspots` (`scene_id`, `type`, `yaw`, `pitch`, `label`, `description`, `target_scene_id`, `icon_type`) VALUES
(2, 'navigation', 180, 0, 'Back to Living Room', NULL, 1, 'arrow'),
(2, 'info', 90, 0, 'Walk-in Closet', 'Custom built-in closet system with LED lighting', NULL, 'info');

-- Hotspots for Villa Entrance (Scene 5)
INSERT INTO `hotspots` (`scene_id`, `type`, `yaw`, `pitch`, `label`, `description`, `target_scene_id`, `icon_type`) VALUES
(5, 'navigation', 45, 0, 'Living Area', 'Enter the main living space', 6, 'arrow'),
(5, 'info', 0, -20, 'Chandelier', 'Custom Italian chandelier', NULL, 'info');

-- Hotspots for Villa Living (Scene 6)
INSERT INTO `hotspots` (`scene_id`, `type`, `yaw`, `pitch`, `label`, `description`, `target_scene_id`, `icon_type`) VALUES
(6, 'navigation', 90, 0, 'Master Suite', 'View the master bedroom', 7, 'arrow'),
(6, 'navigation', 180, 0, 'Pool Deck', 'Go to the pool area', 8, 'arrow'),
(6, 'navigation', 270, 0, 'Back to Entrance', NULL, 5, 'arrow');

-- Hotspots for Condo (Scene 10)
INSERT INTO `hotspots` (`scene_id`, `type`, `yaw`, `pitch`, `label`, `description`, `target_scene_id`, `icon_type`) VALUES
(10, 'navigation', 90, 0, 'Bedroom', NULL, 11, 'arrow'),
(10, 'navigation', 180, 0, 'Balcony', 'Step outside', 12, 'arrow'),
(10, 'link', 270, 0, 'Building Amenities', 'View all building amenities', NULL, 'link');

-- Set external URL for the link hotspot
UPDATE `hotspots` SET `external_url` = 'https://example.com/amenities' WHERE `id` = 11;

-- ============================================================
-- SEED DATA - TOUR VIEWS (Analytics)
-- ============================================================

INSERT INTO `tour_views` (`tour_id`, `ip_address`, `user_agent`, `viewed_at`) VALUES
-- Recent views for various tours
(1, '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '2025-01-20 10:30:00'),
(1, '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', '2025-01-20 14:15:00'),
(1, '192.168.1.102', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_7_1)', '2025-01-21 09:20:00'),
(2, '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '2025-01-20 11:00:00'),
(2, '192.168.1.104', 'Mozilla/5.0 (iPad; CPU OS 14_7_1)', '2025-01-21 15:30:00'),
(3, '192.168.1.105', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '2025-01-21 16:45:00'),
(5, '192.168.1.106', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', '2025-01-20 13:00:00'),
(5, '192.168.1.107', 'Mozilla/5.0 (Android 11)', '2025-01-21 10:10:00'),
(6, '192.168.1.108', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '2025-01-21 17:00:00');

-- ============================================================
-- SESSIONS TABLE (database-backed sessions for Vercel)
-- ============================================================

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(128) NOT NULL,
  `data` text NOT NULL,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- END OF DATABASE SCHEMA AND SEED DATA
-- ============================================================
