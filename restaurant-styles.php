<?php
try {
    include('config.php');

    $domain = isset($_GET['domain']) ? $_GET['domain'] : $_SERVER['SERVER_NAME'];

    $connection = new mysqli($mysqlServer, $mysqlUser, $mysqlPassword, $mysqlDatabase);
    try{
        $statement = $connection->stmt_init();
        try {
            if (!$statement->prepare('SELECT title_color, icon_color, button_color FROM restaurants WHERE `domain` = ?;')) {
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

            $statement->bind_result($titleColor, $iconColor, $buttonColor);
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

header('Content-Type: text/css');
?>

h1 {
    color: <?=$titleColor?>;
}

.icon {
    color: <?=$iconColor?>;
}

button {
    background: <?=$buttonColor?>;
    color: #fff;
}

button:hover {
    background: rgba(253, 138, 6, 1);
}

