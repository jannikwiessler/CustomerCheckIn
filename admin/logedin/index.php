<?php
session_start();
include('../../config.php');

try {
    if (!isset($_SESSION['restaurantId'])) {
        header('Location: /admin/login.php');
        exit;
    }

    $restaurantId = $_SESSION['restaurantId'];

    $connection = new mysqli($mysqlServer, $mysqlUser, $mysqlPassword, $mysqlDatabase);
    try {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($_FILES['logo']['size'] > 0) {
                $uploadFolder = 'uploads/'; //Das Upload-Verzeichnis
                $filename = pathinfo($_FILES['logo']['name'], PATHINFO_FILENAME);
                $extension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));

                //Überprüfung der Dateigröße
                $maxSize = 500 * 1024; //500 KB
                if ($_FILES['logo']['size'] > $maxSize) {
                    $fileError = "Bitte keine Dateien größer 500kb hochladen";
                }

                //Überprüfung dass das Bild keine Fehler enthält
                if (function_exists('exif_imagetype')) { //Die exif_imagetype-Funktion erfordert die exif-Erweiterung auf dem Server
                    $allowedTypes = array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF);
                    $detectedType = exif_imagetype($_FILES['logo']['tmp_name']);
                    if (!in_array($detectedType, $allowedTypes)) {
                        $fileError = "Nur der Upload von Bilddateien ist gestattet";
                    }
                } else {
                    //Überprüfung der Dateiendung
                    $allowedExtensions = array('png', 'jpg', 'jpeg', 'gif');
                    if (!in_array($extension, $allowedExtensions)) {
                        $fileError = "Ungültige Dateiendung. Nur png, jpg, jpeg und gif-Dateien sind erlaubt";
                    }
                }

                //Pfad zum Upload
                $newPath = $uploadFolder . $filename . '.' . $extension;

                //Neuer Dateiname falls die Datei bereits existiert
                if (file_exists('../../' . $newPath)) { //Falls Datei existiert, hänge eine Zahl an den Dateinamen
                    $id = 1;
                    do {
                        $newPath = $uploadFolder . $filename . '_' . $id . '.' . $extension;
                        $id++;
                    } while (file_exists('../../' . $newPath));
                }

                //Alles okay, verschiebe Datei an neuen Pfad
                if (!move_uploaded_file($_FILES['logo']['tmp_name'], '../../' . $newPath)) {
                    $fileError = "Datei wurde nicht hochgeladen";
                }

                if (!isset($fileError)) {
                    $statement = $connection->stmt_init();
                    try {
                        if (!$statement->prepare('UPDATE restaurants SET logo_url=?, title_color=?, icon_color=?, button_color=? WHERE id = ?;')) {
                            throw new Exception($statement->error);
                        }

                        $statement->bind_param('ssssi', $newPath, $_POST['title-color'], $_POST['icon-color'], $_POST['button-color'], $restaurantId);

                        $statement->execute();

                        if ($statement->errno) {
                            throw new Exception($statement->error);
                        }
                    } finally {
                        $statement->close();
                    }
                }
            } else {
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
                header('Location: /admin/registration/');
            }

            $statement->bind_result($domain, $logoUrl, $titleColor, $iconColor, $buttonColor);
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
} catch (Exception $ex) {
    echo $ex->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <title>Online-Check-In Design</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css"
          integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
    <link rel="stylesheet" href="../administration.css">
    <style>
        .wrap {
            width: 100%;
            height: 100%;
            padding: 0;
            overflow: hidden;
        }

        #scaled-frame {
            width: 100%;
            border: 0;
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
    <form action="index.php" method="post" enctype="multipart/form-data">
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
                        <p>Ihr Browser kann leider keine eingebetteten Frames anzeigen. Hier stände eine Live-Vorschau
                            der Check-In-Seite.</p>
                    </iframe>
                </div>
            </div>
            <div class="item">
                <label for="logo">Logo:</label>
                <input type="file" id="logo" name="logo" accept="image/png, image/jpeg"/>
                <?php
                if (isset($fileError)) {
                    echo '<p style="color:red;">' . $fileError . '</p>';
                }
                ?>

                <label for="colors">Farben:</label>
                <div id="colors" class="flex-columns">
                    <div>
                        <div class="color-selector-container--select-textcolor">
                            <input type="color" id="title-color" name="title-color" value="<?= $titleColor ?>"/>
                            <label for="title-color" class="input" style="color:<?= $titleColor ?>;">Titel</label>
                        </div>
                        <div class="flex-columns color-palette" data-input-id="title-color">
                            <button type="button" data-color="Vibrant"></button>
                            <button type="button" data-color="Muted"></button>
                            <button type="button" data-color="DarkVibrant"></button>
                            <button type="button" data-color="DarkMuted"></button>
                            <button type="button" data-color="LightVibrant"></button>
                            <button type="button" data-color="LightMuted"></button>
                        </div>
                    </div>
                    <div>
                        <div class="color-selector-container--select-textcolor">
                            <input type="color" id="icon-color" name="icon-color" value="<?= $iconColor ?>"/>
                            <label for="icon-color" style="color:<?= $iconColor ?>;"><i class="fas fa-user fa-lg"></i>
                                Icon</label>
                        </div>
                        <div class="flex-columns color-palette" data-input-id="icon-color">
                            <button type="button" data-color="Vibrant"></button>
                            <button type="button" data-color="Muted"></button>
                            <button type="button" data-color="DarkVibrant"></button>
                            <button type="button" data-color="DarkMuted"></button>
                            <button type="button" data-color="LightVibrant"></button>
                            <button type="button" data-color="LightMuted"></button>
                        </div>
                    </div>
                    <div>
                        <div class="color-selector-container--select-background">
                            <input type="color" id="button-color" name="button-color" value="<?= $buttonColor ?>"/>
                            <label for="button-color" style="background-color:<?= $buttonColor ?>;">Button</label>
                        </div>
                        <div class="flex-columns color-palette" data-input-id="button-color">
                            <button type="button" data-color="Vibrant"></button>
                            <button type="button" data-color="Muted"></button>
                            <button type="button" data-color="DarkVibrant"></button>
                            <button type="button" data-color="DarkMuted"></button>
                            <button type="button" data-color="LightVibrant"></button>
                            <button type="button" data-color="LightMuted"></button>
                        </div>
                    </div>
                </div>

                <div class="btn-block">
                    <button type="submit" style="float:left">Speichern</button>
                </div>

            </div>
        </div>

    </form>
</div>
<script type="text/javascript" src="/node_modules/node-vibrant/dist/vibrant.min.js"></script>
<script type="text/javascript">
    var iframe = document.getElementById("scaled-frame");
    iframe.onload = function () {
        iframe.style.height = iframe.contentWindow.document.body.scrollHeight + 'px';
    };

    window.onload = function () {
        document.getElementById("logo").addEventListener("change", changeLogo, false);

        document.getElementById("title-color").addEventListener("input", updateTitleColor, false);
        document.getElementById("icon-color").addEventListener("input", updateIconColor, false);
        document.getElementById("button-color").addEventListener("input", updateButtonColor, false);

        Vibrant.from('<?=$logoUrl?>').getPalette(function (err, palette) {
            document.querySelectorAll('.color-palette').forEach(function (colorPalette) {
                colorPalette.querySelectorAll('button').forEach(function (button) {
                    button.style.backgroundColor = palette[button.dataset.color].hex;
                    button.value = palette[button.dataset.color].hex;
                    button.addEventListener("click", function () {
                        document.getElementById(colorPalette.dataset.inputId).value = button.value;
                        updateTitleColor();
                        updateIconColor();
                        updateButtonColor();
                    }, false);
                });
            });
        });
    };

    function changeLogo(event) {
        var file = event.target.files[0];
        if (['image/png', 'image/jpeg'].includes(file.type)) {
            var reader = new FileReader();
            reader.onloadend = function () {
                iframe.contentWindow.document.getElementById("logo").src = reader.result;
                iframe.style.height = iframe.contentWindow.document.body.scrollHeight + 'px';

                Vibrant.from(reader.result).getPalette(function (err, palette) {
                    document.querySelectorAll('.color-palette').forEach(function (colorPalette) {
                        colorPalette.querySelectorAll('button').forEach(function (button) {
                            button.style.backgroundColor = palette[button.dataset.color].hex;
                            button.value = palette[button.dataset.color].hex;
                        });
                    });
                });
            };
            reader.readAsDataURL(file);
        } else {
            alert('Datei im falschen Format.');
        }
    }

    function updateTitleColor() {
        let color = document.getElementById('title-color').value;
        document.querySelector("#title-color + label").style.color = color;
        iframe.contentWindow.document.querySelectorAll("h1").forEach(function (p) {
            p.style.color = color;
        });
    }

    function updateIconColor() {
        let color = document.getElementById('icon-color').value;
        document.querySelector("#icon-color + label").style.color = color;
        iframe.contentWindow.document.querySelectorAll(".icon").forEach(function (p) {
            p.style.color = color;
        });
    }

    function updateButtonColor() {
        let color = document.getElementById('button-color').value;
        document.querySelector("#button-color + label").style.backgroundColor = color;
        iframe.contentWindow.document.querySelectorAll("button").forEach(function (p) {
            p.style.backgroundColor = color;
        });
    }
</script>
</body>


</html>