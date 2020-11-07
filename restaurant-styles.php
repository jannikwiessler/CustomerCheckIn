<?php
try {
    include('config.php');

    $domain = $_SERVER['SERVER_NAME'];

    $connection = new mysqli($mysqlServer, $mysqlUser, $mysqlPassword, $mysqlDatabase);
    try{
        $statement = $connection->stmt_init();
        try {
            if (!$statement->prepare('SELECT background_color, highlight_color FROM restaurants WHERE domain = ?;')) {
                throw new Exception($statement->error);
            }

            $statement->bind_param('s', $domain);
            $statement->execute();

            if ($statement->errno) {
                throw new Exception($statement->error);
            }

            if ($statement->num_rows == 0) {
                header('Location: https://online-checkin-freiburg.de/register.php?domain=' . urlencode($domain));
                throw new Exception($statement->error);
            }

            $statement->bind_result($backgroundColor, $formBackgroundColor, $textColor, $highlightColor);
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

body {
    background-image: url("https://cdn.pixabay.com/photo/2016/03/05/19/02/hamburger-1238246_960_720.jpg");
}

.secondlayer {
    background-color: <?=$backgroundColor?>;
}

h1 {
    color: <?=$highlightColor?>;
}

label.radio:before {
    background: #1c87c9;
}

input[type=text], input[type=password] {
    border: solid 1px #cbc9c9;
    box-shadow: 1px 2px 5px rgba(0, 0, 0, .09);
    background: #fff;
}

.icon {
    color: <?=$highlightColor?>;
}

button {
    background: <?=$highlightColor?>;
    color: #fff;
}

button:hover {
    background: rgba(253, 138, 6, 1);
}

