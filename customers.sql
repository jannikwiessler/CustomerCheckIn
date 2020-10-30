CREATE TABLE `customers` (
    `id` BIGINT NOT NULL,
    `first_name` VARCHAR(255) NOT NULL,
    `last_name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `tel` VARCHAR(252) NOT NULL,
    `address` VARCHAR(255) NOT NULL,
    `zip_code` VARCHAR(255) NOT NULL,
    `city` VARCHAR(255) NOT NULL,
    `checkin_time` DATETIME NOT NULL,
    `checkout_time` DATETIME,
    UNIQUE KEY `customers_pk` (`id`) USING BTREE
);