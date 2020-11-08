<?php
include('config.php');

$connection = new mysqli($mysqlServer, $mysqlUser, $mysqlPassword, $mysqlDatabase);

$connection->query('DELETE FROM customers WHERE checkin_time < DATE_SUB(NOW(), INTERVAL 31 DAY)');

if($connection->errno){
    echo $connection->error;
} else {
    echo 'Erfolg';
}
$connection->close();
