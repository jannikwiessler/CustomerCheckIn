<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
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
<div class="banner">
    <h1>Login</h1>
</div>
<br/>
<form action="login.php" method="post">
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
