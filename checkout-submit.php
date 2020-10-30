<?php
if ( $_SERVER['REQUEST_METHOD'] != 'POST' ) {
    header('Location: index.html');
} else {
    include('config.php');

    $connection = mysqli_connect($mysqlServer, $mysqlUser, $mysqlPassword, $mysqlDatabase);

    $statement = $connection->prepare('UPDATE customers SET checkout_time=NOW() WHERE id=?;');

    $statement->bind_param('s', $_POST['id']);

    $statement->execute();

    header("refresh:4;http://timeout-freiburg.de");  //10 sind die sek
}
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
        <h1>Check-Out</h1>
        <br/>

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
