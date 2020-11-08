<?php
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: index.php');
    exit;
}
try {
    include('config.php');

    $domain = $_SERVER['SERVER_NAME'];

    $connection = new mysqli($mysqlServer, $mysqlUser, $mysqlPassword, $mysqlDatabase);
    try {
        $statement = $connection->stmt_init();
        try {
            if (!$statement->prepare('SELECT restaurant_name, logo_url, website FROM restaurants WHERE domain = ?;')) {
                throw new Exception($statement->error);
            }

            $statement->bind_param('s', $domain);
            $statement->execute();

            if ($statement->errno) {
                throw new Exception($statement->error);
            }

            $statement->store_result();

            if ($statement->num_rows == 0) {
                header('Location: https://online-checkin-freiburg.de/admin/registration.php?domain=' . urlencode($domain));
                throw new Exception($statement->error);
            }

            $statement->bind_result($restaurantName, $logoUrl, $website);
            $statement->fetch();
        } finally {
            $statement->close();
        }

        $statement = $connection->stmt_init();
        try {
            if (!$statement->prepare('UPDATE customers SET checkout_time=NOW() WHERE id=?;')) {
                throw new Exception($statement->error);
            }

            $statement->bind_param('s', $_POST['id']);
            $statement->execute();

            if ($statement->errno) {
                throw new Exception($statement->error);
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

header("refresh:4;" . $website);  //4 sind die sek
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <title><?= $restaurantName ?></title>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css"
          integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
    <link rel="stylesheet" href="mystylzz.css">
    <link rel="stylesheet" href="restaurant-styles.php">
</head>
<body>
<div class="secondlayer">

    <div class="main-block">
        <div class="align-center">
            <img src="<?= $logoUrl ?>" id="logo" alt="<?= $restaurantName ?>"/>
        </div>
        <br/>
        <h1>Check-Out</h1>

        <hr/>

        <form action="checkout-submit.php" method="post">
            <div class="btn-block">
                <p>Vielen Dank! Schönen Tag noch.</p>
                <p>Du wirst jetzt zu unserer Homepage weitergeleitet.</p>
            </div>
        </form>
    </div>
</div>
</body>
</html>
