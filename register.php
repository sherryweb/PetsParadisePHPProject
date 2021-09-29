<?php

require_once 'vendor/autoload.php';
require_once 'init.php';

$app->get('/isemailexist/{email}', function ($request, $response, $args) {
    $mail = DB::queryFirstField("SELECT email from users WHERE email=%s", $args['email']);
    if ($mail != null) {
        echo "email already exist";
        return;
    }
});

// STATE 1: first display of the form
$app->get('/register', function ($request, $response, $args) {
    return $this->view->render($response, 'register.html.twig');
});

// STATE 2&3: receiving submission
$app->post('/register', function ($request, $response, $args) use ($log) {
    $username = $request->getParam('userName');
    $type = 1;  // common user
    $email = $request->getParam('email');
    $address = $request->getParam('address');
    $phone = $request->getParam('phone');
    $city = $request->getParam('city');
    $zipcode = $request->getParam('zipcode');
    $password = $request->getParam('password');
    $repassword = $request->getParam('password1');
    //
    $errorList = [];
    if (strlen($username) < 2 || strlen($username) > 50) {
        $errorList[] = "UserName must be 2-50 characters long";
    }
    if (preg_match('/^[a-zA-Z0-9]{4,20}$/',$username) != 1) {
        $errorList[] = "Username must be 4-20 characters long made up of lower-case characters and numbers";
        $username = "";
    }
    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $errorList[] = "Seller's email must look like an email";
        $email = "";
    }
    if ($password != $repassword) {
        $errorList[] = "Passwords do not match";
    }
    //
    $valuesList = ['userName' => $username, 'type' => $type, 'email' => $email, 
                    'phone' => $phone, 'address' => $address, 'city' => $city, 
                    'zipcode' => $zipcode, 'password' => $password];

    if ($errorList) { // STATE 2: errors - redisplay the form
        return $this->view->render($response, 'register.html.twig', ['errorList' => $errorList, 'v' => $valuesList]);
    } else { // STATE 3: success
        DB::insert('users', $valuesList);
        $userid = DB::insertId();
        $record = DB::queryFirstRow("SELECT id,userName,type,email FROM users WHERE id=%i", $userid);
        $_SESSION['user'] = $record;
        $log->debug(sprintf("New user created with Id=%s", $userid));
        return $this->view->render($response, 'register_success.html.twig');
    }
});



