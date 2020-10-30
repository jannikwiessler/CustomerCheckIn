<?php

if ( $_SERVER['REQUEST_METHOD'] != 'POST' ) {
    header('Location: index.html');
} else{
    include('config.php');

    $connection = new mysqli($mysqlServer, $mysqlUser, $mysqlPassword, $mysqlDatabase);

    $statement = $connection->stmt_init();

    if($statement->prepare('SELECT id FROM customers WHERE id=?;')){
        do {
            if(function_exists('random_int')){
                $id = random_int(10**10, PHP_INT_MAX);
            } else {
                $id = mt_rand(10**10, PHP_INT_MAX);
            }
            $statement->bind_param('i', $id);

            $statement->execute();
        } while ($statement->num_rows > 0);

        $statement->close();
    }

    $statement = $connection->stmt_init();

    if($statement->prepare('INSERT INTO customers (id, first_name,last_name,email,tel,address,zip_code,city,checkin_time) VALUES (?,?,?,?,?,?,?,?,NOW());')){
        $statement->bind_param('isssssss', $id, $_POST['firstname'], $_POST['lastname'], $_POST['email'], $_POST['tel'], $_POST['street'], $_POST['zipcode'], $_POST['city']);

        $statement->execute();

        if($statement->errno){
            echo $statement->error;
        } else {
            header('Location: checkout.php?id='.$id);
        }

        $statement->close();
    }

    $connection->close();

}


