CREATE TABLE `customers` (
    `id` INT NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `restaurant_name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `tel` VARCHAR(252) NOT NULL,
    `address` VARCHAR(255) NOT NULL,
    `zip_code` VARCHAR(255) NOT NULL,
    `city` VARCHAR(255) NOT NULL,
    `logo_url` VARCHAR (255) NOT NULL,
    `highlight_color` VARCHAR(7) NOT NULL,
    `highlight_color2` VARCHAR(7) NOT NULL,
    UNIQUE KEY `restaurants_pk` (`id`) USING BTREE
);