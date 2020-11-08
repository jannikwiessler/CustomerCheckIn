CREATE TABLE `restaurants` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `domain` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(255) NOT NULL,
    `last_name` VARCHAR(255) NOT NULL,
    `restaurant_name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `tel` VARCHAR(252) NOT NULL,
    `address` VARCHAR(255) NOT NULL,
    `zip_code` VARCHAR(255) NOT NULL,
    `city` VARCHAR(255) NOT NULL,
    `website` VARCHAR(255) NOT NULL,
    `logo_url` VARCHAR(255) NOT NULL,
    `title_color` VARCHAR(7) NOT NULL,
    `icon_color` VARCHAR(7) NOT NULL,
    `button_color` VARCHAR(7) NOT NULL,
    UNIQUE KEY `restaurants_pk` (`id`) USING BTREE
);