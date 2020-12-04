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
        $statement = $connection->stmt_init();
        try {
            if (!$statement->prepare('SELECT COUNT(*) FROM customers WHERE restaurant_id = ? AND (checkin_time >= NOW() - INTERVAL 30 DAY);')) {
                throw new Exception($statement->error);
            }

            $statement->bind_param('i', $restaurantId);
            $statement->execute();

            if ($statement->errno) {
                throw new Exception($statement->error);
            }

            $statement->bind_result($customersInLast30Days);
            $statement->fetch();
        } finally {
            $statement->close();
        }

        $statement = $connection->stmt_init();
        try {
            if (!$statement->prepare('SELECT AVG(timediff(checkout_time, checkin_time)) FROM customers WHERE restaurant_id = ? AND (checkin_time >= NOW() - INTERVAL 30 DAY) AND checkout_time IS NOT NULL;')) {
                throw new Exception($statement->error);
            }

            $statement->bind_param('i', $restaurantId);
            $statement->execute();

            if ($statement->errno) {
                throw new Exception($statement->error);
            }

            $statement->bind_result($averageStaySeconds);
            $statement->fetch();
            $time = new DateTime(date("H:i:s", intval($averageStaySeconds)));
            $time->setTimezone(new DateTimeZone('UTC'));
            $averageStay = $time->format("H:i:s");
        } finally {
            $statement->close();
        }

        $statement = $connection->stmt_init();
        try {
            if (!$statement->prepare('SELECT zip_code FROM customers WHERE restaurant_id = ? AND (checkin_time >= NOW() - INTERVAL 30 DAY) GROUP BY zip_code ORDER BY COUNT(*) DESC;')) {
                throw new Exception($statement->error);
            }

            $statement->bind_param('i', $restaurantId);
            $statement->execute();

            if ($statement->errno) {
                throw new Exception($statement->error);
            }

            $statement->bind_result($mostCommonZipCode);
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

<div class="banner">
    <h1>Dashboard</h1>
</div>

<div class="flex-columns dashboard">
    <div class="box dashboard-box">
        <p class="dashboard-measure">
            <span><?= $customersInLast30Days ?></span>
        </p>
        <p>
            Kunden in den letzten 30 Tagen
        </p>
        <p>
            <a class="button" style="width:auto;" href="/admin/logedin/export.html">exportieren</a>
        </p>
    </div>
    <div class="box dashboard-box">
        <img id="qrcode" src="" alt="QR-Code für https://<?= $domain ?>" style="height: 200px;"/>
        <p>
            https://<?= $domain ?>
        </p>
        <p>
            <a id="qrcode-download" class="button" style="width:auto;" download="qrcode.png" href="">herunterladen</a>
        </p>
    </div>
    <div class="box dashboard-box">
        <p class="dashboard-measure" style="font-size: 7em;">
            <span><?= $averageStay ?></span>
        </p>
        <p>
            Durchschnittliche Verweildauer
        </p>
    </div>
    <div class="box dashboard-box">
        <p class="dashboard-measure">
            <span><?= $mostCommonZipCode ?></span>
        </p>
        <p>
            Häufigste Postleitzahl
        </p>
    </div>
</div>
<script type="text/javascript" src="/node_modules/qrcode/build/qrcode.js"></script>
<script type="text/javascript">
    window.onload = function () {
        QRCode.toDataURL('https://<?=$domain?>').then(url => {
            document.getElementById('qrcode').src = url;
            document.getElementById('qrcode-download').href = url;
        });
    };
</script>