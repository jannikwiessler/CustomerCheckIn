<?php
    if(!isset($_GET['id'])){
        header('Location: index.php');
    }

    try {
        include('config.php');

        $domain = $_SERVER['SERVER_NAME'];

        $connection = new mysqli($mysqlServer, $mysqlUser, $mysqlPassword, $mysqlDatabase);

        try{
            $statement = $connection->stmt_init();
            try {
                if (!$statement->prepare('SELECT restaurant_name, logo_url FROM restaurants WHERE `domain` = ?;')) {
                    throw new Exception($statement->error);
                }

                $statement->bind_param('s', $domain);
                $statement->execute();

                if ($statement->errno) {
                    throw new Exception($statement->error);
                }

                $statement->store_result();

                if ($statement->num_rows == 0) {
                    header('Location: https://mirathra.de/admin/registration/?domain=' . urlencode($domain));
                    throw new Exception("Domain nicht vorhanden.");
                }

                $statement->bind_result($restaurantName, $logoUrl);
                $statement->fetch();
            } finally {
                $statement->close();
            }

            $statement = $connection->stmt_init();
            try{
                if (!$statement->prepare('SELECT first_name FROM customers WHERE id=?;')) {
                    throw new Exception($statement->error);
                }
                $statement->bind_param('i', $_GET['id']);

                $statement->execute();

                if ($statement->errno) {
                    throw new Exception($statement->error);
                }

                $statement->bind_result($firstName);

                $statement->fetch();
            } finally {
                $statement->close();
            }

        } finally {
            $connection->close();
        }
    } catch (Exception $ex){
        echo $ex->getMessage();
        exit();
    }

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <title><?= $restaurantName ?></title>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
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

        <hr/>

        <form action="checkout-submit.php" method="post">

            <div class="btn-block">
                <p>Herzlich Willkommen, <?=$firstName?> ! <br/>Du hast dich erfolgreich eingecheckt. Bitte lass' diesen Tab ge&ouml;ffnet und checke dich beim Gehen wieder aus.</p>
                <input type="hidden" name="id" value="<?=$_GET['id']?>" />
                <button type="submit">Check-Out</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>