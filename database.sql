-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for my_store
CREATE DATABASE IF NOT EXISTS `my_store`
  /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */
  /*!80016 DEFAULT ENCRYPTION='N' */;
USE `my_store`;

-- ============================================================
-- Bảng: banner
-- ============================================================
CREATE TABLE IF NOT EXISTS `banner` (
  `id`       int         NOT NULL AUTO_INCREMENT,
  `image`    varchar(255) NOT NULL,
  `title`    varchar(150) DEFAULT '',
  `position` tinyint     NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `banner` (`id`, `image`, `title`, `position`) VALUES
  (6, 'banner2_1779075103.png', 'ffff',  2),
  (8, 'banner3_1779075142.jpg', 'rrrrr', 3),
  (9, 'banner1_1779075753.png', 'dddd',  1);

-- ============================================================
-- Bảng: category
-- ============================================================
CREATE TABLE IF NOT EXISTS `category` (
  `id`          int          NOT NULL AUTO_INCREMENT,
  `name`        varchar(100) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `category` (`id`, `name`, `description`) VALUES
  ( 9, 'Phụ kiện',          'Danh mục phụ kiện điện tử'),
  (10, 'Thiết bị âm thanh', 'Danh mục loa, tai nghe, micro'),
  (11, 'Điện thoại',        'Danh mục các loại điện thoại'),
  (12, 'Laptop',            'Danh mục các loại laptop'),
  (13, 'Máy tính bảng',     'Danh mục các loại máy tính bảng'),
  (16, 'Loa',               '');

-- ============================================================
-- Bảng: product
-- ============================================================
CREATE TABLE IF NOT EXISTS `product` (
  `id`          int              NOT NULL AUTO_INCREMENT,
  `name`        varchar(100)     NOT NULL,
  `description` text,
  `price`       decimal(15,2)    NOT NULL,
  `image`       varchar(255)     DEFAULT NULL,
  `category_id` int              DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `product_ibfk_1` FOREIGN KEY (`category_id`)
    REFERENCES `category` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `product` (`id`, `name`, `description`, `price`, `image`, `category_id`) VALUES
  (5, 'iphone 17 pro max', 'tttttttttttt', 50000000.00, '1779076126_6a0a8c1ef2b58.png', 11);

-- ============================================================
-- Bảng: user  (có cột role để phân quyền admin/user)
-- ============================================================
CREATE TABLE IF NOT EXISTS `user` (
  `id`         INT          AUTO_INCREMENT PRIMARY KEY,
  `fullname`   VARCHAR(100) NOT NULL,
  `email`      VARCHAR(150) NOT NULL UNIQUE,
  `phone`      VARCHAR(20)  DEFAULT '',
  `password`   VARCHAR(255) NOT NULL,
  `role`       ENUM('admin','user') DEFAULT 'user',
  `created_at` DATETIME     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Bảng: account  (theo yêu cầu đề bài – đăng nhập bằng username)
-- ============================================================
CREATE TABLE IF NOT EXISTS `account` (
  `id`       INT          AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(255) NOT NULL UNIQUE,
  `fullname` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role`     ENUM('admin','user') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Bảng: order
-- ============================================================
CREATE TABLE IF NOT EXISTS `order` (
  `id`              INT           AUTO_INCREMENT PRIMARY KEY,
  `user_id`         INT           DEFAULT NULL,
  `fullname`        VARCHAR(100)  NOT NULL,
  `phone`           VARCHAR(20)   NOT NULL,
  `address`         TEXT          NOT NULL,
  `city`            VARCHAR(100)  NOT NULL,
  `note`            TEXT,
  `shipping_method` VARCHAR(20)   DEFAULT 'standard',
  `payment_method`  VARCHAR(20)   DEFAULT 'card',
  `discount`        DECIMAL(15,2) DEFAULT 0,
  `total`           DECIMAL(15,2) NOT NULL,
  `status`          VARCHAR(20)   DEFAULT 'pending',
  `created_at`      DATETIME      DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Bảng: order_item
-- ============================================================
CREATE TABLE IF NOT EXISTS `order_item` (
  `id`         INT           AUTO_INCREMENT PRIMARY KEY,
  `order_id`   INT           NOT NULL,
  `product_id` INT           NOT NULL,
  `name`       VARCHAR(200)  NOT NULL,
  `price`      DECIMAL(15,2) NOT NULL,
  `qty`        INT           NOT NULL,
  `image`      VARCHAR(255)  DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Tài khoản admin mặc định
-- Email   : admin@store.com
-- Password: admin123
-- (Hệ thống cũng tự tạo khi khởi động lần đầu nếu chưa có)
-- ============================================================
INSERT IGNORE INTO `user` (`fullname`, `email`, `phone`, `password`, `role`)
VALUES (
  'Administrator',
  'admin@store.com',
  '',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'admin'
);

-- ============================================================
-- Nếu bảng user đã tồn tại mà CHƯA có cột role, chạy lệnh này:
-- ALTER TABLE `user`
--   ADD COLUMN `role` ENUM('admin','user') DEFAULT 'user' AFTER `password`;
-- ============================================================

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
