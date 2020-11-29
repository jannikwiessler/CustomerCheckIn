<?php
/**
 * @var int $restaurantId
 * @var string $restaurantName
 * @var string $domain
 * @var string $logoUrl
 * @var string $titleColor
 * @var string $iconColor
 * @var string $buttonColor
 */
try {
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
                if (file_exists(__DIR__ . '/../../' . $newPath)) { //Falls Datei existiert, hänge eine Zahl an den Dateinamen
                    $id = 1;
                    do {
                        $newPath = $uploadFolder . $filename . '_' . $id . '.' . $extension;
                        $id++;
                    } while (file_exists(__DIR__ . '/../../' . $newPath));
                }

                //Alles okay, verschiebe Datei an neuen Pfad
                if (!move_uploaded_file($_FILES['logo']['tmp_name'], __DIR__ . '/../../' . $newPath)) {
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

                        $logoUrl = '../../' . $newPath;
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

            $titleColor = $_POST['title-color'];
            $iconColor = $_POST['icon-color'];
            $buttonColor = $_POST['button-color'];
        }
    } finally {
        $connection->close();
    }

} catch (Exception $ex) {
    echo $ex->getMessage();
    exit();
}
?>
<div class="banner">
    <h1>Design</h1>
</div>

<div class="flex-columns">
    <div class="wrap box">
        <div style="margin: auto; width: 100%">
            <iframe src="/index.php?domain=<?= urlencode($domain) ?>" id="scaled-frame"
                    name="quicklook">
                <p>Ihr Browser kann leider keine eingebetteten Frames anzeigen. Hier stände eine
                    Live-Vorschau
                    der Check-In-Seite.</p>
            </iframe>
        </div>
        <div class="click-blocker"></div>
    </div>
    <form class="box" action="/admin/logedin/index.php" method="post" enctype="multipart/form-data">
        <div class="flex-rows" style="justify-content: space-evenly">
            <div class="item">
                <label for="logo">Logo:</label>
                <input type="file" id="logo" name="logo" accept="image/png, image/jpeg"/>
                <?php
                if (isset($fileError)) {
                    echo '<p style="color:red;">' . $fileError . '</p>';
                }
                ?>
            </div>

            <div style="flex-basis: 40%;">
                <label for="colors">Farben:</label>
                <div id="colors" class="flex-rows" style="height: 100%; justify-content: space-between">
                    <div>
                        <div class="color-selector-container--select-textcolor">
                            <input type="color" id="title-color" name="title-color"
                                   value="<?= $titleColor ?>"/>
                            <label for="title-color" class="input"
                                   style="color:<?= $titleColor ?>;">Titel</label>
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
                            <input type="color" id="icon-color" name="icon-color"
                                   value="<?= $iconColor ?>"/>
                            <label for="icon-color" style="color:<?= $iconColor ?>;"><i
                                        class="fas fa-user fa-lg"></i>
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
                            <input type="color" id="button-color" name="button-color"
                                   value="<?= $buttonColor ?>"/>
                            <label for="button-color"
                                   style="background-color:<?= $buttonColor ?>;">Button</label>
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
            </div>

            <div class="btn-block">
                <button type="submit" style="float:left">Speichern</button>
            </div>
        </div>
    </form>
</div>
<script type="text/javascript" src="/node_modules/node-vibrant/dist/vibrant.min.js"></script>
<script type="text/javascript">
    var iframe = document.getElementById("scaled-frame");
    iframe.addEventListener("click", function () {
        return false;
    }, true);
    iframe.onload = function () {
        iframe.style.height = iframe.contentWindow.document.body.scrollHeight + 'px';
        iframe.style.width = iframe.contentWindow.document.body.scrollWidth + 'px'
        iframe.contentWindow.document.querySelector('.secondlayer').style.overflow = 'hidden';
        iframe.parentElement.style.height = (iframe.contentWindow.document.body.scrollHeight * 0.75) + 'px'
        iframe.parentElement.style.width = (iframe.contentWindow.document.body.scrollWidth * 0.75) + 'px'
    };

    var colorPaletteInitialized = false;

    window.onload = function () {
        document.getElementById("logo").addEventListener("change", changeLogo, false);

        document.getElementById("title-color").addEventListener("input", updateTitleColor, false);
        document.getElementById("icon-color").addEventListener("input", updateIconColor, false);
        document.getElementById("button-color").addEventListener("input", updateButtonColor, false);

        if ('<?=$logoUrl?>') {
            Vibrant.from('<?=$logoUrl?>').getPalette(updateColorPalette);
        } else {
            document.querySelectorAll('.color-palette').forEach(function (colorPalette) {
                colorPalette.style.display = 'none';
            })
        }
    };

    function changeLogo(event) {
        var file = event.target.files[0];
        if (['image/png', 'image/jpeg'].includes(file.type)) {
            var reader = new FileReader();
            reader.onloadend = function () {
                iframe.contentWindow.document.getElementById("logo").src = reader.result;
                iframe.style.height = iframe.contentWindow.document.body.scrollHeight + 'px';

                Vibrant.from(reader.result).getPalette(updateColorPalette);
            };
            reader.readAsDataURL(file);
        } else {
            alert('Datei im falschen Format.');
        }
    }

    function updateColorPalette(err, palette) {
        document.querySelectorAll('.color-palette').forEach(function (colorPalette) {
            colorPalette.querySelectorAll('button').forEach(function (button) {
                button.style.backgroundColor = palette[button.dataset.color].hex;
                button.value = palette[button.dataset.color].hex;
                if (!colorPaletteInitialized) {
                    button.addEventListener("click", function () {
                        document.getElementById(colorPalette.dataset.inputId).value = button.value;
                        updateTitleColor();
                        updateIconColor();
                        updateButtonColor();
                    }, false);
                }
            });
            if (!colorPaletteInitialized) {
                colorPalette.style.display = null;
            }
        });
        colorPaletteInitialized = true;
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