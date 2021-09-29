<?php

session_start();

require_once 'vendor/autoload.php';
require_once 'init.php';

// Define app routes below

require_once 'account.php';
require_once 'admin.php';
require_once 'register.php';
require_once 'adoption.php';
require_once 'addpets.php';
require_once 'communicate.php';
require_once 'donate.php';
require_once 'staff.php';

// STATE 1: The index.php
$app->get('/', function ($request, $response, $args) {
    return $this->view->render($response, 'index.html.twig', ['user' => $_SESSION['user']]);
});


// Run app - must be the last operation
// if you forget it all you'll see is a blank page
$app->run();
