<?php
    if(!isset($_GET['id'])){
        header('Location: index.html');
    }

    include('config.php');

    $connection = new mysqli($mysqlServer, $mysqlUser, $mysqlPassword, $mysqlDatabase);

    $statement = $connection->stmt_init();

    if($statement->prepare('SELECT first_name FROM customers WHERE id=?;')){
        $statement->bind_param('i', $_GET['id']);

        $statement->execute();

        $statement->bind_result($firstName);

        $statement->fetch();

        $statement->close();
    }

    $connection->close();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <title>Timeout Freiburg Check-In</title>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
    <link rel="stylesheet" href="mystylzz.css">

</head>
<body>
<div class="secondlayer">

    <div class="main-block">
        <div class="align-center">
            <img src="https://le-cdn.website-editor.net/238f4e528fb742ac817988bf89c8a157/dms3rep/multi/opt/logoTransparentNurKreis-715807bf-1920w.png"
                 id="logo"
                 alt="Timeout Freiburg"
            />
        </div>
        <br/>
        <br/>

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