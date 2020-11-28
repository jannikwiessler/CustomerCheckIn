<?php
session_start();

if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle)
    {
        return 0 === \strncmp($haystack, $needle, \strlen($needle));
    }
}

if (!function_exists('str_ends_with')) {
    function str_ends_with($haystack, $needle)
    {
        return '' === $needle || ('' !== $haystack && 0 === \substr_compare($haystack, $needle, -\strlen($needle)));
    }
}

try {
    include('../config.php');

    if (str_starts_with($_SERVER['REQUEST_URI'], '/admin/logedin/')) {
        if (!isset($_SESSION['restaurantId'])) {
            header('Location: /admin/login.php');
            exit;
        }

        $restaurantId = $_SESSION['restaurantId'];

        $connection = new mysqli($mysqlServer, $mysqlUser, $mysqlPassword, $mysqlDatabase);
        try {
            $statement = $connection->stmt_init();
            try {
                if (!$statement->prepare('SELECT restaurant_name, `domain`, logo_url, title_color, icon_color, button_color FROM restaurants WHERE id = ?;')) {
                    throw new Exception($statement->error);
                }

                $statement->bind_param('i', $restaurantId);
                $statement->execute();

                if ($statement->errno) {
                    throw new Exception($statement->error);
                }

                $statement->store_result();

                if ($statement->num_rows == 0) {
                    header('Location: /admin/registration/');
                }

                $statement->bind_result($restaurantName, $domain, $logoUrl, $titleColor, $iconColor, $buttonColor);
                $statement->fetch();

                $logoUrl = '../../' . $logoUrl;
            } finally {
                $statement->close();
            }
        } finally {
            $connection->close();
        }

        if (isset($fileError)) {
            $titleColor = $_POST['title-color'];
            $iconColor = $_POST['icon-color'];
            $buttonColor = $_POST['button-color'];
        }
    }
} catch (Exception $ex) {
    echo $ex->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <title>Online-Check-In</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css"
          integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
    <link rel="stylesheet" href="/admin/administration.css">
</head>

<body>
<div class="ui-outer-container">
    <div class="ui-inner-container">
        <nav>
            <?php
            include($_GET['nav']);
            ?>
        </nav>
        <div class="ui-content">
            <?php
            $content = $_GET['content'];
            if (str_ends_with($content, '/')) {
                $content .= 'index.php';
            }
            if (is_dir($content)) {
                $content .= '/index.php';
            }

            include($content);
            ?>
        </div>
    </div>
</div>
</body>
</html>