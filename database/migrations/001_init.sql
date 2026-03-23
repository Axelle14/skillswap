-- ============================================================
--  SkillSwap — Master Database Migration
--  MySQL 8.0+  |  utf8mb4  |  InnoDB
--  Run: mysql -u root -p skillswap < 001_init.sql
-- ============================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;

-- ── Database ──────────────────────────────────────────────
CREATE DATABASE IF NOT EXISTS `skillswap`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `skillswap`;

-- ── Users ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
    `id`                INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `full_name`         VARCHAR(100)     NOT NULL,
    `email`             VARCHAR(191)     NOT NULL,
    `password_hash`     VARCHAR(255)     NOT NULL,
    `bio`               TEXT,
    `skills`            VARCHAR(500),
    `credits`           INT              NOT NULL DEFAULT 50,
    `role`              ENUM('member','admin') NOT NULL DEFAULT 'member',
    `subscription_plan` ENUM('free','premium','pro') NOT NULL DEFAULT 'free',
    `availability`      ENUM('available','limited','unavailable') NOT NULL DEFAULT 'available',
    `is_active`         TINYINT(1)       NOT NULL DEFAULT 1,
    `created_at`        DATETIME         NOT NULL,
    `updated_at`        DATETIME         NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_email` (`email`),
    KEY `idx_subscription` (`subscription_plan`),
    KEY `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Services ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `services` (
    `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `user_id`     INT UNSIGNED  NOT NULL,
    `title`       VARCHAR(150)  NOT NULL,
    `description` TEXT          NOT NULL,
    `category`    VARCHAR(50)   NOT NULL,
    `credits`     SMALLINT      NOT NULL DEFAULT 10,
    `is_active`   TINYINT(1)    NOT NULL DEFAULT 1,
    `created_at`  DATETIME      NOT NULL,
    `updated_at`  DATETIME      NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_user`     (`user_id`),
    KEY `idx_category` (`category`),
    KEY `idx_active`   (`is_active`),
    FULLTEXT KEY `ft_search` (`title`, `description`),
    CONSTRAINT `fk_services_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Swap Requests ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `swap_requests` (
    `id`               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `requester_id`     INT UNSIGNED  NOT NULL,
    `provider_id`      INT UNSIGNED  NOT NULL,
    `service_id`       INT UNSIGNED  NOT NULL,
    `credits_escrowed` SMALLINT      NOT NULL,
    `message`          TEXT,
    `status`           ENUM(
                           'requested',
                           'accepted',
                           'in_progress',
                           'completed',
                           'declined',
                           'disputed'
                       ) NOT NULL DEFAULT 'requested',
    `created_at`       DATETIME,
    `updated_at`       DATETIME,
    `completed_at`     DATETIME      NULL,
    PRIMARY KEY (`id`),
    KEY `idx_requester` (`requester_id`),
    KEY `idx_provider`  (`provider_id`),
    KEY `idx_service`   (`service_id`),
    KEY `idx_status`    (`status`),
    CONSTRAINT `fk_swap_requester`
        FOREIGN KEY (`requester_id`) REFERENCES `users`(`id`),
    CONSTRAINT `fk_swap_provider`
        FOREIGN KEY (`provider_id`)  REFERENCES `users`(`id`),
    CONSTRAINT `fk_swap_service`
        FOREIGN KEY (`service_id`)   REFERENCES `services`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Escrow Ledger (audit trail) ───────────────────────────
CREATE TABLE IF NOT EXISTS `escrow_ledger` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `swap_id`    INT UNSIGNED NOT NULL,
    `user_id`    INT UNSIGNED NOT NULL,
    `amount`     SMALLINT     NOT NULL,
    `type`       ENUM('locked','released','returned') NOT NULL,
    `created_at` DATETIME     NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_swap` (`swap_id`),
    KEY `idx_user` (`user_id`),
    CONSTRAINT `fk_ledger_swap` FOREIGN KEY (`swap_id`) REFERENCES `swap_requests`(`id`),
    CONSTRAINT `fk_ledger_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Messages ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `messages` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `swap_id`    INT UNSIGNED NOT NULL,
    `sender_id`  INT UNSIGNED NOT NULL,
    `body`       TEXT         NOT NULL,
    `is_read`    TINYINT(1)   NOT NULL DEFAULT 0,
    `created_at` DATETIME     NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_swap`   (`swap_id`),
    KEY `idx_sender` (`sender_id`),
    CONSTRAINT `fk_msg_swap`   FOREIGN KEY (`swap_id`)   REFERENCES `swap_requests`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_msg_sender` FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Reviews ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `reviews` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `swap_id`     INT UNSIGNED NOT NULL,
    `reviewer_id` INT UNSIGNED NOT NULL,
    `reviewee_id` INT UNSIGNED NOT NULL,
    `rating`      TINYINT      NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
    `comment`     TEXT,
    `created_at`  DATETIME     NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_review` (`swap_id`, `reviewer_id`),   -- one review per person per swap
    KEY `idx_reviewee` (`reviewee_id`),
    CONSTRAINT `fk_review_swap`     FOREIGN KEY (`swap_id`)     REFERENCES `swap_requests`(`id`),
    CONSTRAINT `fk_review_reviewer` FOREIGN KEY (`reviewer_id`) REFERENCES `users`(`id`),
    CONSTRAINT `fk_review_reviewee` FOREIGN KEY (`reviewee_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Disputes ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `disputes` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `swap_id`     INT UNSIGNED NOT NULL,
    `reporter_id` INT UNSIGNED NOT NULL,
    `reason`      TEXT         NOT NULL,
    `status`      ENUM('open','under_review','resolved','closed') NOT NULL DEFAULT 'open',
    `admin_notes` TEXT,
    `created_at`  DATETIME     NOT NULL,
    `resolved_at` DATETIME     NULL,
    PRIMARY KEY (`id`),
    KEY `idx_swap`   (`swap_id`),
    KEY `idx_status` (`status`),
    CONSTRAINT `fk_dispute_swap`     FOREIGN KEY (`swap_id`)     REFERENCES `swap_requests`(`id`),
    CONSTRAINT `fk_dispute_reporter` FOREIGN KEY (`reporter_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Subscriptions ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `subscriptions` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    INT UNSIGNED NOT NULL,
    `plan`       ENUM('free','premium','pro') NOT NULL,
    `started_at` DATETIME     NOT NULL,
    `expires_at` DATETIME     NULL,
    `is_active`  TINYINT(1)   NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    KEY `idx_user` (`user_id`),
    CONSTRAINT `fk_sub_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Rate Limiting ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `rate_limits` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `key_name`   VARCHAR(191) NOT NULL,
    `created_at` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_key_time` (`key_name`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Event: auto-clean rate_limits daily ───────────────────
CREATE EVENT IF NOT EXISTS `evt_clean_rate_limits`
ON SCHEDULE EVERY 1 DAY
DO DELETE FROM rate_limits WHERE created_at < UNIX_TIMESTAMP() - 86400;

SET foreign_key_checks = 1;

-- ── Seed: admin account ───────────────────────────────────
-- Password: Admin@12345  (CHANGE IN PRODUCTION)
INSERT IGNORE INTO `users`
    (full_name, email, password_hash, role, subscription_plan, credits, availability, created_at, updated_at)
VALUES (
    'Admin',
    'admin@skillswap.local',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    'pro',
    9999,
    'available',
    NOW(),
    NOW()
);
