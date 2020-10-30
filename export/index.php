<?php
include('../config.php');

header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Content-type: text/csv');
header('Content-Disposition: attachment; filename="export.csv"');

$connection = new mysqli($mysqlServer, $mysqlUser, $mysqlPassword, $mysqlDatabase);

$result = $connection->query('SELECT * FROM customers');

$fp = fopen('php://output', 'w');

while($val = $result->fetch_array(MYSQLI_ASSOC)){
    fputcsv($fp, $val);
}

$result->close();

$connection->close();

fclose($fp);
