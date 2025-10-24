CREATE TABLE IF NOT EXISTS `orders` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `merchant_trade_no` VARCHAR(64) NOT NULL UNIQUE,
  `user_id` INT NULL,
  `amount` DECIMAL(18,8) NOT NULL,
  `currency` VARCHAR(10) NOT NULL DEFAULT 'USDT',
  `product_name` VARCHAR(255),
  `status` VARCHAR(50) NOT NULL DEFAULT 'PENDING',
  `binance_prepay_id` VARCHAR(255) NULL,
  `binance_qr_url` TEXT NULL,
  `meta` JSON NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (`merchant_trade_no`),
  INDEX (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;