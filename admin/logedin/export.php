<?php
try {
    session_start();

    if (!isset($_SESSION['restaurantId'])) {
        header('Location: /admin/login.php');
        exit;
    }

    include('../../config.php');

    $restaurantId = $_SESSION['restaurantId'];

    $connection = new mysqli($mysqlServer, $mysqlUser, $mysqlPassword, $mysqlDatabase);
    try {
        $statement = $connection->stmt_init();
        try {
            if (!$statement->prepare('SELECT restaurants.restaurant_name, customers.first_name, customers.last_name, customers.checkin_time, customers.checkout_time, customers.email, customers.tel, customers.address, customers.zip_code, customers.city FROM restaurants RIGHT JOIN customers ON customers.restaurant_id = restaurants.id WHERE restaurants.id = ? AND customers.checkout_time > ? AND customers.checkin_time < ? ORDER BY customers.checkin_time;')) {
                throw new Exception($statement->error);
            }

            $statement->bind_param('iss', $restaurantId, $_POST['from'], $_POST['to']);
            $statement->execute();

            if ($statement->errno) {
                throw new Exception($statement->error);
            }

            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Content-type: text/csv');
            header('Content-Disposition: attachment; filename="export.csv"');

            $val = [];
            $statement->bind_result($val['Restaurant'], $val['Vorname'], $val['Nachname'], $val['Check-In'], $val['Check-Out'], $val['E-Mail'], $val['Tel'], $val['Adresse'], $val['PLZ'], $val['Stadt']);

            $fp = fopen('php://output', 'w');
            try {
                while ($statement->fetch()) {
                    fputcsv($fp, $val);
                }
            } finally {
                fclose($fp);
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
