<?php
try {
    include('config.php');

    $domain = isset($_GET['domain']) ? $_GET['domain'] : $_SERVER['SERVER_NAME'];

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
    <title><?= htmlentities($restaurantName) ?></title>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css"
          integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
    <link rel="stylesheet" href="mystylzz.css">
    <link rel="stylesheet"
          href="restaurant-styles.php<?= isset($_GET['domain']) ? '?domain=' . urlencode($_GET['domain']) : '' ?>">
</head>
<body>
<div class="secondlayer">
    <div class="main-block">
        <div class="align-center">
            <img src="<?= $logoUrl ?>" id="logo" alt="<?= htmlentities($restaurantName) ?>"/>
        </div>
        <br/>
        <h1>Check-In</h1>

        <form action="submit.php" method="post">
            <hr/>

            <!-- Name -->
            <label class="icon" for="firstname"><i class="fas fa-user fa-lg"></i></label>
            <input type="text" name="firstname" id="firstname" placeholder="Name" required/>

            <br/>
            <!-- Vorname -->
            <label class="icon" for="lastname"></label>
            <input type="text" name="lastname" id="lastname" placeholder="Nachname" required/>
            <br/>
            <!-- Straße -->
            <label class="icon" for="street"><i class="fas fa-map-marker-alt fa-lg"></i></label>
            <input type="text" name="street" id="street" placeholder="Straße und Hausnummer" required/>
            <br/>
            <!-- Plz -->
            <label class="icon" for="zipcode"></label>
            <input type="text" name="zipcode" id="zipcode" placeholder="Postleitzahl" required/>
            <br/>
            <!-- Stadt -->
            <label class="icon" for="city"></label>
            <input type="text" name="city" id="city" placeholder="Stadt" required/>
            <br/>
            <!-- EMail -->
            <label class="icon" for="email"><i class="fas fa-envelope fa-lg"></i></label>
            <input type="text" name="email" id="email" placeholder="Email" required/>
            <br/>
            <!-- Telefon -->
            <label class="icon" for="tel"><i class="fas fa-mobile-alt fa-lg"></i></label>
            <input type="text" name="tel" id="tel" placeholder="Mobile oder Festnetz" required/>

            <hr>

            <div class="btn-block">
                <p>Um bei Bedarf eine l&uuml;ckenlose Kontaktnachverfolgung sicherstellen zu k&ouml;nnen, sind wir
                    verpflichtet, die Kontaktdaten unserer G&auml;ste für 30 Tage zu speichern. Nach Ablauf von 30 Tagen
                    werden die Kontaktdaten automatisch gel&ouml;scht.</p>
                <button type="submit">Check-In</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>