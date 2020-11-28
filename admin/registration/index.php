<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $connection = new mysqli($mysqlServer, $mysqlUser, $mysqlPassword, $mysqlDatabase);
        try {
            /*            if ($_POST['info'] == '1') {
                            $domain = $_POST['domain'];
                        } else {*/
            $domain = $_POST['subdomain'] . '.' . $_SERVER['SERVER_NAME'];
//            }

            $errors = [];

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
                    $errors['domain'] = "Die Domain ist bereits registriert.";
                }
            } finally {
                $statement->close();
            }

            $statement = $connection->stmt_init();
            try {
                if (!$statement->prepare('SELECT id FROM restaurants WHERE `email` = ?;')) {
                    throw new Exception($statement->error);
                }

                $statement->bind_param('s', $_POST['email']);
                $statement->execute();

                if ($statement->errno) {
                    throw new Exception($statement->error);
                }

                $statement->store_result();

                if ($statement->num_rows > 0) {
                    $errors['email'] = "Ein Account mit dieser E-Mail-Adresse existiert bereits.";
                }
            } finally {
                $statement->close();
            }

            $password = $_POST['password'];
            if ($password != $_POST['password2']) {
                $errors['password'] = "Die Passwörter sind nicht gleich.";
            } else {
                $passwordStrength = 0;
                if (preg_match("#[0-9]+#", $password)) {
                    $passwordStrength++;
                }

                if (preg_match("#[a-z]+#", $password)) {
                    $passwordStrength++;
                }

                if (preg_match("#[A-Z]+#", $password)) {
                    $passwordStrength++;
                }

                if (preg_match("#\W+#", $password)) {
                    $passwordStrength++;
                }

                if (strlen($password) < 8 || $passwordStrength < 3) {
                    $errors['password'] = "Das Passwort muss mindestens 8 Zeichen lang sein und 3 der folgenden Zeichenarten enthalten: Kleinbuchstaben, Großbuchstaben, Zahlen und Zeichen";
                }
            }
            if (sizeof($errors) == 0) {
                $statement = $connection->stmt_init();
                try {
                    if (!$statement->prepare('INSERT INTO restaurants (domain,first_name,last_name,restaurant_name,email,password,tel,address,zip_code,city,website) VALUES (?,?,?,?,?,?,?,?,?,?,?);')) {
                        throw new Exception($statement->error);
                    }

                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                    $statement->bind_param('sssssssssss', $domain, $_POST['firstname'], $_POST['lastname'], $_POST['restaurantname'], $_POST['email'], $hashedPassword, $_POST['phone'], $_POST['street'], $_POST['zip'], $_POST['city'], $_POST['website']);

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

<div class="banner">
    <h1>Online-Check-In registrieren</h1>
</div>
<br/>
<p>TestText TestText TestText TestText TestText TestText TestText TestText TestText TestText TestText
    TestText
    TestText TestText TestText TestText TestText TestText TestText TestText TestText TestText TestText
    TestText
    TestText TestText TestText TestText TestText TestText TestText TestText TestText TestText TestText
    TestText
    TestText TestText TestText TestText TestText TestText TestText TestText TestText TestText .</p>
<br/>
<form action="index.php" method="post">
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
            <input id="restaurantname" type="text" name="restaurantname"
                   value="<?= $_POST['restaurantname'] ?>"
                   required/>
        </div>
        <div class="item">
            <label for="email">Email-Adresse (Username)<span>*</span></label>
            <input id="email" type="text" name="email" value="<?= $_POST['email'] ?>" required/>
            <?php
            if (isset($errors['email'])) {
                echo '<p style="color:red;">' . $errors['email'] . '</p>';
            }
            ?>
        </div>
        <div class="item">
            <label for="password">Passwort<span>*</span></label>
            <input id="password" type="password" name="password" required/>
        </div>
        <div class="item">
            <label for="password2">Passwort wiederholen<span>*</span></label>
            <input id="password2" type="password" name="password2" required/>
        </div>
        <?php
        if (isset($errors['password'])) {
            echo '<p style="color:red;flex-grow:2;width:100%">' . $errors['password'] . '</p>';
        }
        ?>
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
        <div class="item">
            <label for="subdomain">Wunsch Domain</label>
            <div style="display: flex;width:100%;align-items: center;">
                <input id="subdomain" type="text" name="subdomain" value="<?= $_POST['subdomain'] ?>"
                       placeholder="my-restaurant-name" style="flex-grow: 2;"/>
                <span>.<?= $_SERVER['SERVER_NAME'] ?></span>
            </div>

            <?php
            if (isset($errors['domain'])) {
                echo '<p style="color: red;">' . $errors['domain'] . '</p>';
            }
            ?>
        </div>
    </div>
    <!--        <div class="question">
<label>Ich habe eine eigene Domain.</label>
<div class="question-answer">
    <div>
        <input type="radio" value="1" id="radio_1"
               name="info" <? /*= $_POST['info'] == 1 ? 'checked="checked"' : '' */ ?>
               onchange="domainCheckBoxChanged(this)"/>
        <label for="radio_1" class="radio"><span>Ja</span></label>
    </div>
    <div>
        <input type="radio" value="2" id="radio_2"
               name="info" <? /*= $_POST['info'] == 2 ? 'checked="checked"' : '' */ ?>
               onchange="domainCheckBoxChanged(this)"
               checked="checked" />
        <label for="radio_2" class="radio"><span>Nein</span></label>
    </div>
</div>
<div class="item">
    <label for="domain">Domain</label>
    <input id="domain" type="text" name="domain" value="<? /*= $_POST['domain'] */ ?>"
           placeholder="checkin.my-restaurant-name.de"
           disabled="disabled" />
</div>
<div class="item">
    <label for="subdomain">Wunsch Domain</label>
    <input id="subdomain" type="text" name="subdomain" value="<?= $_POST['subdomain'] ?>"
           placeholder="my-restaurant-name"/>.checkin.de
</div>
<?php
    if (isset($errors['domain'])) {
        echo '<p style="color: red;">' . $errors['domain'] . '</p>';
    }
    ?>
</div>-->

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
<!--<script type="text/javascript">
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
-->