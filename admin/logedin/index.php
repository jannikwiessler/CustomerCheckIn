<?php
session_start();
include('../../config.php');

try {
    if (!isset($_SESSION['restaurantId'])) {
        header('Location: /admin/registration/');
        exit;
    }

    $restaurantId = $_SESSION['restaurantId'];

    $connection = new mysqli($mysqlServer, $mysqlUser, $mysqlPassword, $mysqlDatabase);
    try {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $statement = $connection->stmt_init();
            try {
                if (!$statement->prepare('UPDATE restaurants SET title_color=?, icon_color=?, button_color=? WHERE id = ?;')) {
                    throw new Exception($statement->error);
                }

                $statement->bind_param('sssi', $_POST['title-color'], $_POST['icon-color'], $_POST['button-color'], $restaurantId);

                $statement->execute();

                if ($statement->errno) {
                    throw new Exception($statement->error);
                }
            } finally {
                $statement->close();
            }
        }

        $statement = $connection->stmt_init();
        try {
            if (!$statement->prepare('SELECT `domain`, logo_url, title_color, icon_color, button_color FROM restaurants WHERE id = ?;')) {
                throw new Exception($statement->error);
            }

            $statement->bind_param('i', $restaurantId);
            $statement->execute();

            if ($statement->errno) {
                throw new Exception($statement->error);
            }

            $statement->store_result();

            if ($statement->num_rows == 0) {
                header('Location: /admin/login.php');
            }

            $statement->bind_result($domain, $logoUrl, $titleColor, $iconColor, $buttonColor);
            $statement->fetch();
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
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <title>Title of the document</title>
    <link rel="stylesheet" href="../administration.css">
    <style>
        #wrap {
            width: 100%;
            height: 100%;
            padding: 0;
            overflow: hidden;
        }

        #scaled-frame {
            width: 100%;
            border: 0px;
        }

        #scaled-frame {
            zoom: 0.75;
            -moz-transform: scale(0.75);
            -moz-transform-origin: 0 0;
            -o-transform: scale(0.75);
            -o-transform-origin: 0 0;
            -webkit-transform: scale(0.75);
            -webkit-transform-origin: 0 0;
        }

        @media screen and (-webkit-min-device-pixel-ratio: 0) {
            #scaled-frame {
                zoom: 1;
            }
        }
    </style>
</head>

<body>
<div class="testbox">
    <form action="index.php" method="post">
        <nav>
            <ul>
                <li><a href="index.php">Design</a></li>
                <li><a href="export.php">Export</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
        <div class="banner">
            <h1>Design</h1>
        </div>
        <br/>
        <p>The HELP Group is seeking volunteers to serve our community. Fill in the information below to indicate
            how you would like to become involved.</p>
        <br/>

        <div class="colums">
            <div class="wrap">
                <div style="margin: auto; width: 80%">
                    <iframe src="../../index.php?domain=<?= urlencode($domain) ?>" id="scaled-frame" name="quicklook">
                        <p>Ihr Browser kann leider keine eingebetteten Frames anzeigen.</p>
                    </iframe>
                </div>
            </div>
            <div class="item">
                <label for="title-color">Titel Farbe</label>
                <input type="color" id="title-color" name="title-color" value="<?= $titleColor ?>"
                       style="width:50px; height:50px"/>

                <label for="icon-color">Icon Farbe</label>
                <input type="color" id="icon-color" name="icon-color" value="<?= $iconColor ?>"
                       style="width:50px; height:50px"/>

                <label for="button-color">Button Farbe</label>
                <input type="color" id="button-color" name="button-color" value="<?= $buttonColor ?>"
                       style="width:50px; height:50px"/>

                <label for="logo">Logo:</label>
                <input type="file" id="logo" name="logo" accept="image/png, image/jpeg"/>

                <div class="btn-block">
                    <button type="submit" style="float:left">Speichern</button>
                </div>

            </div>
        </div>

    </form>
</div>
<script type="text/javascript">
    var iframe = document.getElementById("scaled-frame");
    iframe.onload = function () {
        iframe.style.height = iframe.contentWindow.document.body.scrollHeight + 'px';
    }

    document.getElementById("title-color").addEventListener("input", changeTitleColor, false);
    document.getElementById("icon-color").addEventListener("input", changeIconColor, false);
    document.getElementById("button-color").addEventListener("input", changeButtonColor, false);

    function changeTitleColor(event) {
        iframe.contentWindow.document.querySelectorAll("h1").forEach(function (p) {
            p.style.color = event.target.value;
        });
    }

    function changeIconColor(event) {
        iframe.contentWindow.document.querySelectorAll(".icon").forEach(function (p) {
            p.style.color = event.target.value;
        });
    }

    function changeButtonColor(event) {
        iframe.contentWindow.document.querySelectorAll("button").forEach(function (p) {
            p.style.backgroundColor = event.target.value;
        });
    }
</script>
</body>


</html>