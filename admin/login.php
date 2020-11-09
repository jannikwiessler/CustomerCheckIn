<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        session_start();
        include('../config.php');

        $connection = new mysqli($mysqlServer, $mysqlUser, $mysqlPassword, $mysqlDatabase);
        try {
            $statement = $connection->stmt_init();
            try {
                if (!$statement->prepare('SELECT id, password FROM restaurants WHERE `email` = ?;')) {
                    throw new Exception($statement->error);
                }

                $statement->bind_param('s', $_POST['email']);
                $statement->execute();

                if ($statement->errno) {
                    throw new Exception($statement->error);
                }

                $statement->store_result();

                if ($statement->num_rows == 0) {
                    $loginError = "Benutzername oder Passwort sind falsch.";
                }

                $statement->bind_result($restaurantId, $hashedPassword);
                $statement->fetch();

                if (!password_verify($_POST['password'], $hashedPassword)) {
                    $loginError = "Benutzername oder Passwort sind falsch.";
                }

                if (!isset($loginError)) {
                    $_SESSION['restaurantId'] = $restaurantId;
                    header('Location: /admin/logedin/');
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
}
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <title>Online-Check-In Login</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css"
          integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
    <link rel="stylesheet" href="administration.css">

</head>

<body>
<div class="testbox">
    <form action="login.php" method="post">
        <nav>
            <ul>
                <li><a href="/admin/registration/">Registrieren</a></li>
                <li><a href="/admin/login.php">Login</a></li>
            </ul>
        </nav>
        <div class="banner">
            <h1>Login</h1>
        </div>
        <br/>
        <div class="colums">
            <div class="item">
                <label for="email">Email-Adresse (Username)<span>*</span></label>
                <input id="email" type="text" name="email" value="<?= $_POST['email'] ?>" required/>
            </div>
            <div class="item">
                <label for="password">Passwort<span></span></label>
                <input id="password" type="password" name="password"/>
            </div>
        </div>

        <?php
        if (isset($loginError)) {
            echo '<p style="color: red;">' . $loginError . '</p>';
        }
        ?>

        <div class="btn-block">
            <button type="submit">Login</button>
        </div>
    </form>
</div>
</body>

</html>