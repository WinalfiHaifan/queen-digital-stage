-- Queen Digital Stage. Run in the target database with utf8mb4.

-- Source: Rancangan Akhir Basis Data, sheets 2 through 8.

CREATE TABLE IF NOT EXISTS `admin_users` (
    `admin_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(254) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CHECK (is_active IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `band_members` (
    `member_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `member_name` VARCHAR(100) NOT NULL,
    `birth_date` DATE NULL DEFAULT NULL,
    `role` VARCHAR(100) NOT NULL,
    `biography` TEXT NULL DEFAULT NULL,
    `photo_url` VARCHAR(500) NULL DEFAULT NULL,
    `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `albums` (
    `album_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `album_title` VARCHAR(150) NOT NULL,
    `release_year` YEAR NULL DEFAULT NULL,
    `genre` VARCHAR(100) NULL DEFAULT NULL,
    `cover_url` VARCHAR(500) NULL DEFAULT NULL,
    `description` TEXT NULL DEFAULT NULL,
    `spotify_url` VARCHAR(500) NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `songs` (
    `song_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `album_id` INT UNSIGNED NULL DEFAULT NULL,
    `song_title` VARCHAR(150) NOT NULL,
    `release_year` YEAR NULL DEFAULT NULL,
    `genre` VARCHAR(100) NULL DEFAULT NULL,
    `songwriter_credit` VARCHAR(255) NULL DEFAULT NULL,
    `summary` VARCHAR(500) NULL DEFAULT NULL,
    `story` TEXT NULL DEFAULT NULL,
    `spotify_url` VARCHAR(500) NULL DEFAULT NULL,
    `youtube_video_id` VARCHAR(20) NULL DEFAULT NULL,
    `cover_url` VARCHAR(500) NULL DEFAULT NULL,
    `is_featured_song` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_songs_album (album_id),
    CONSTRAINT fk_songs_album FOREIGN KEY (album_id) REFERENCES albums (album_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CHECK (is_featured_song IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `timeline_events` (
    `event_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `event_title` VARCHAR(200) NOT NULL,
    `event_year` YEAR NULL DEFAULT NULL,
    `event_type` ENUM('history', 'award') NOT NULL DEFAULT 'history',
    `description` TEXT NOT NULL,
    `image_url` VARCHAR(500) NULL DEFAULT NULL,
    `source_url` VARCHAR(500) NULL DEFAULT NULL,
    `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `media_items` (
    `media_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `media_title` VARCHAR(200) NOT NULL,
    `media_type` ENUM('image', 'video') NOT NULL,
    `category` ENUM('band', 'member', 'live', 'album', 'archive') NOT NULL,
    `media_url` VARCHAR(500) NOT NULL,
    `thumbnail_url` VARCHAR(500) NULL DEFAULT NULL,
    `description` TEXT NULL DEFAULT NULL,
    `media_year` YEAR NULL DEFAULT NULL,
    `source_url` VARCHAR(500) NULL DEFAULT NULL,
    `album_id` INT UNSIGNED NULL DEFAULT NULL,
    `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_media_album (album_id),
    CONSTRAINT fk_media_album FOREIGN KEY (album_id) REFERENCES albums (album_id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_categories` (
    `category_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `category_name` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `products` (
    `product_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT UNSIGNED NOT NULL,
    `product_name` VARCHAR(150) NOT NULL,
    `description` TEXT NULL DEFAULT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `currency` CHAR(3) NOT NULL DEFAULT 'USD',
    `stock` INT UNSIGNED NOT NULL DEFAULT 0,
    `weight_gram` DECIMAL(10,2) NOT NULL,
    `image_url` VARCHAR(500) NULL DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_products_category (category_id),
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES product_categories (category_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CHECK (price >= 0),
    CHECK (stock >= 0),
    CHECK (weight_gram >= 0),
    CHECK (is_active IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fan_zone_submissions` (
    `submission_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `favorite_topic` VARCHAR(150) NOT NULL,
    `message` TEXT NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CHECK (is_active IN (0, 1)),
    KEY idx_fan_public (is_active, created_at, submission_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
