<?php

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: index.php');
    exit();
}

try {
    include('config.php');

    $domain = $_SERVER['SERVER_NAME'];

    $connection = new mysqli($mysqlServer, $mysqlUser, $mysqlPassword, $mysqlDatabase);
    try {
        $statement = $connection->stmt_init();
        try {
            if (!$statement->prepare('SELECT id, restaurant_name, logo_url FROM restaurants WHERE domain = ?;')) {
                throw new Exception($statement->error);
            }

            $statement->bind_param('s', $domain);
            $statement->execute();

            if ($statement->errno) {
                throw new Exception($statement->error);
            }

            $statement->store_result();

            if ($statement->num_rows == 0) {
                header('Location: https://online-checkin-freiburg.de/registration.php?domain=' . urlencode($domain));
                throw new Exception($statement->error);
            }

            $statement->bind_result($restaurantId, $restaurantName, $logoUrl);
            $statement->fetch();
        } finally {
            $statement->close();
        }

        $statement = $connection->stmt_init();
        try {
            if (!$statement->prepare('SELECT id FROM customers WHERE id=?;')) {
                throw new Exception($statement->error);
            }
            do {
                if (function_exists('random_int')) {
                    $id = random_int(10 ** 10, PHP_INT_MAX);
                } else {
                    $id = mt_rand(10 ** 10, PHP_INT_MAX);
                }
                $statement->bind_param('i', $id);

                $statement->execute();

                if ($statement->errno) {
                    throw new Exception($statement->error);
                }

                $statement->store_result();
            } while ($statement->num_rows > 0);
        } finally {
            $statement->close();
        }

        $statement = $connection->stmt_init();
        try {
            if (!$statement->prepare('INSERT INTO customers (id, restaurant_id, first_name,last_name,email,tel,address,zip_code,city,checkin_time) VALUES (?,?,?,?,?,?,?,?,?,NOW());')) {
                throw new Exception($statement->error);
            }
            $statement->bind_param('iisssssss', $id, $restaurantId, $_POST['firstname'], $_POST['lastname'], $_POST['email'], $_POST['tel'], $_POST['street'], $_POST['zipcode'], $_POST['city']);

            $statement->execute();

            if ($statement->errno) {
                throw new Exception($statement->error);
            } else {
                header('Location: checkout.php?id=' . $id);
            }
        } finally {
            $statement->close();
        }
    } finally {
        $connection->close();
    }
} catch (Exception $ex) {
    echo $ex->getMessage();
    exit();
}

