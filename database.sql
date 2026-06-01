-- ============================================================
-- HUY TOAN STORE - Database Schema v2.0
-- Bao gồm đầy đủ các chức năng nâng cao
-- ============================================================

CREATE DATABASE IF NOT EXISTS `my_store`
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `my_store`;

-- ============================================================
-- Bảng: user (phân quyền, avatar, khóa, xác thực, remember me)
-- ============================================================
CREATE TABLE IF NOT EXISTS `user` (
  `id`             INT          AUTO_INCREMENT PRIMARY KEY,
  `fullname`       VARCHAR(100) NOT NULL,
  `email`          VARCHAR(150) NOT NULL UNIQUE,
  `phone`          VARCHAR(20)  DEFAULT '',
  `password`       VARCHAR(255) NOT NULL,
  `role`           ENUM('admin','user') DEFAULT 'user',
  `avatar`         VARCHAR(255) DEFAULT NULL          COMMENT 'Tên file ảnh đại diện trong /public/uploads/avatars/',
  `is_locked`      TINYINT(1)   DEFAULT 0             COMMENT '1=bị khóa, 0=hoạt động',
  `is_verified`    TINYINT(1)   DEFAULT 0             COMMENT '1=đã xác thực email',
  `verify_token`   VARCHAR(64)  DEFAULT NULL          COMMENT 'Token xác thực email',
  `remember_token` VARCHAR(64)  DEFAULT NULL          COMMENT 'Token Remember Me cookie',
  `reset_token`    VARCHAR(64)  DEFAULT NULL          COMMENT 'Token đặt lại mật khẩu',
  `reset_expires`  DATETIME     DEFAULT NULL          COMMENT 'Thời hạn reset token',
  `created_at`     DATETIME     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Bảng: banner
-- ============================================================
CREATE TABLE IF NOT EXISTS `banner` (
  `id`       INT          NOT NULL AUTO_INCREMENT,
  `image`    VARCHAR(255) NOT NULL,
  `title`    VARCHAR(150) DEFAULT '',
  `position` TINYINT      NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `banner` (`id`,`image`,`title`,`position`) VALUES
  (6,'banner2_1779075103.png','ffff',2),
  (8,'banner3_1779075142.jpg','rrrrr',3),
  (9,'banner1_1779075753.png','dddd',1);

-- ============================================================
-- Bảng: category
-- ============================================================
CREATE TABLE IF NOT EXISTS `category` (
  `id`          INT          NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(100) NOT NULL,
  `description` TEXT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `category` (`id`,`name`,`description`) VALUES
  (9,'Phụ kiện','Danh mục phụ kiện điện tử'),
  (10,'Thiết bị âm thanh','Danh mục loa, tai nghe, micro'),
  (11,'Điện thoại','Danh mục các loại điện thoại'),
  (12,'Laptop','Danh mục các loại laptop'),
  (13,'Máy tính bảng','Danh mục các loại máy tính bảng'),
  (16,'Loa','');

-- ============================================================
-- Bảng: product
-- ============================================================
CREATE TABLE IF NOT EXISTS `product` (
  `id`          INT           NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(100)  NOT NULL,
  `description` TEXT,
  `price`       DECIMAL(15,2) NOT NULL,
  `image`       VARCHAR(255)  DEFAULT NULL,
  `category_id` INT           DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `product_ibfk_1` FOREIGN KEY (`category_id`)
    REFERENCES `category`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Bảng: account (đề bài yêu cầu)
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
  `payment_method`  VARCHAR(20)   DEFAULT 'cod',
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
-- Tài khoản Admin mặc định
-- Email   : admin@store.com
-- Password: admin123
-- ============================================================
INSERT IGNORE INTO `user` (`fullname`,`email`,`phone`,`password`,`role`,`is_verified`,`is_locked`)
VALUES (
  'Administrator','admin@store.com','',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'admin',1,0
);

-- ============================================================
-- Nếu DB cũ chưa có các cột mới, chạy lệnh này:
-- ============================================================
-- ALTER TABLE `user`
--   ADD COLUMN `avatar`         VARCHAR(255) DEFAULT NULL,
--   ADD COLUMN `is_locked`      TINYINT(1)   DEFAULT 0,
--   ADD COLUMN `is_verified`    TINYINT(1)   DEFAULT 0,
--   ADD COLUMN `verify_token`   VARCHAR(64)  DEFAULT NULL,
--   ADD COLUMN `remember_token` VARCHAR(64)  DEFAULT NULL,
--   ADD COLUMN `reset_token`    VARCHAR(64)  DEFAULT NULL,
--   ADD COLUMN `reset_expires`  DATETIME     DEFAULT NULL;
