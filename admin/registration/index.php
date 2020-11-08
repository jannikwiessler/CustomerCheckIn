<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        session_start();
        include('../../config.php');

        $connection = new mysqli($mysqlServer, $mysqlUser, $mysqlPassword, $mysqlDatabase);
        try {
            if ($_POST['info'] == '1') {
                $domain = $_POST['domain'];
            } else {
                $domain = $_POST['subdomain'] . '.' . $_SERVER['SERVER_NAME'];
            }

            $statement = $connection->stmt_init();
            try {
                if (!$statement->prepare('SELECT id FROM restaurants WHERE `domain` = ?;')) {
                    throw new Exception($statement->error);
                }

                $statement->bind_param('s', $domain);
                $statement->execute();

                if ($statement->errno) {
                    throw new Exception($statement->error);
                }

                $statement->store_result();

                if ($statement->num_rows > 0) {
                    $domainError = "Die Domain ist bereits registriert.";
                }
            } finally {
                $statement->close();
            }

            if (!isset($domainError)) {
                $statement = $connection->stmt_init();
                try {
                    if (!$statement->prepare('INSERT INTO restaurants (domain,first_name,last_name,restaurant_name,email,tel,address,zip_code,city,website) VALUES (?,?,?,?,?,?,?,?,?,?);')) {
                        throw new Exception($statement->error);
                    }

                    $statement->bind_param('ssssssssss', $domain, $_POST['firstname'], $_POST['lastname'], $_POST['restaurantname'], $_POST['email'], $_POST['phone'], $_POST['street'], $_POST['zip'], $_POST['city'], $_POST['website']);

                    $statement->execute();

                    if ($statement->errno) {
                        throw new Exception($statement->error);
                    } else {
                        $_SESSION['restaurantId'] = $statement->insert_id;
                        header('Location: /admin/logedin/');
                    }
                } finally {
                    $statement->close();
                }
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
<html>

<head>
    <title>Online-Check-In registrieren</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css"
          integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
    <link rel="stylesheet" href="../administration.css">

</head>

<body>
<div class="testbox">
    <form action="index.php" method="post">
        <div class="banner">
            <h1>Online-Check-In registrieren</h1>
        </div>
        <br />
        <p>TestText TestText TestText TestText TestText TestText TestText TestText TestText TestText TestText TestText
            TestText TestText TestText TestText TestText TestText TestText TestText TestText TestText TestText TestText
            TestText TestText TestText TestText TestText TestText TestText TestText TestText TestText TestText TestText
            TestText TestText TestText TestText TestText TestText TestText TestText TestText TestText .</p>
        <br />
        <div class="colums">
            <div class="item">
                <label for="lastname">Name<span>*</span></label>
                <input id="lastname" type="text" name="lastname" value="<?= $_POST['lastname'] ?>" required/>
            </div>
            <div class="item">
                <label for="firstname">Vorname<span>*</span></label>
                <input id="firstname" type="text" name="firstname" value="<?= $_POST['firstname'] ?>" required/>
            </div>
            <div class="item">
                <label for="restaurantname">Restaurant-Name<span>*</span></label>
                <input id="restaurantname" type="text" name="restaurantname" value="<?= $_POST['restaurantname'] ?>"
                       required/>
            </div>
            <div class="item">
                <label for="email">Email-Adresse (Username)<span>*</span></label>
                <input id="email" type="text" name="email" value="<?= $_POST['email'] ?>" required/>
            </div>
            <div class="item">
                <label for="phone">Telefon</label>
                <input id="phone" type="tel" name="phone" value="<?= $_POST['phone'] ?>"/>
            </div>
            <div class="item">
                <label for="street">Straße<span>*</span></label>
                <input id="street" type="text" name="street" value="<?= $_POST['street'] ?>" required/>
            </div>
            <div class="item">
                <label for="zip">Postleitzahl<span>*</span></label>
                <input id="zip" type="text" name="zip" value="<?= $_POST['zip'] ?>" required/>
            </div>
            <div class="item">
                <label for="city">Stadt<span>*</span></label>
                <input id="city" type="text" name="city" value="<?= $_POST['city'] ?>" required/>
            </div>
            <div class="item">
                <label for="website">Website<span></span></label>
                <input id="website" type="text" name="website" value="<?= $_POST['website'] ?>"/>
            </div>
        </div>
        <div class="question">
            <label>Ich habe eine eigene Domain.</label>
            <div class="question-answer">
                <div>
                    <input type="radio" value="1" id="radio_1"
                           name="info" <?= $_POST['info'] == 1 ? 'checked="checked"' : '' ?>
                           onchange="domainCheckBoxChanged(this)"/>
                    <label for="radio_1" class="radio"><span>Ja</span></label>
                </div>
                <div>
                    <input type="radio" value="2" id="radio_2"
                           name="info" <?= $_POST['info'] == 2 ? 'checked="checked"' : '' ?>
                           onchange="domainCheckBoxChanged(this)"
                           checked="checked" />
                    <label for="radio_2" class="radio"><span>Nein</span></label>
                </div>
            </div>
            <div class="item">
                <label for="domain">Domain</label>
                <input id="domain" type="text" name="domain" value="<?= $_POST['domain'] ?>"
                       placeholder="checkin.my-restaurant-name.de"
                       disabled="disabled" />
            </div>
            <div class="item">
                <label for="subdomain">Wunsch Domain</label>
                <input id="subdomain" type="text" name="subdomain" value="<?= $_POST['subdomain'] ?>"
                       placeholder="my-restaurant-name"/>.checkin.de
            </div>
            <?php
            if (isset($domainError)) {
                echo '<p style="color: red;">' . $domainError . '</p>';
            }
            ?>
        </div>

        <!-- <div class="item">
      <p>Meal Preference</p>
      <select>
        <option selected value="" disabled selected></option>
        <option value="b" >Beef</option>
        <option value="ch">Chicken</option>
        <option value="v">Vegetarian</option>
        <option value="n">None</option>
      </select>
    </div> -->
        <!--            <div class="item">
                        <label for="visit">Anmerkungen</label>
                        <textarea id="visit" rows="3"></textarea>
                    </div>
        -->
        <div class="btn-block">
            <button type="submit">Submit</button>
        </div>
    </form>
</div>
<script type="text/javascript">
    function domainCheckBoxChanged(checkbox) {
        if (checkbox.value === "1") {
            document.getElementById('domain').disabled = false;
            document.getElementById('domain2').disabled = true;
            document.getElementById('domain2').value = "";
        } else {
            document.getElementById('domain2').disabled = false;
            document.getElementById('domain').disabled = true;
            document.getElementById('domain').value = "";
        }
    }
</script>
</body>

</html>