<?php

require_once 'vendor/autoload.php';
require_once 'init.php';

// STATE 1: first display of the form
$app->get('/donate', function ($request, $response, $args) {
    if (!isset($_SESSION['user'])) {
        echo '<script>alert("To visit this page,You should login at first!")</script>';
        return $this->view->render($response, 'error_notlogin.html.twig');
    }

    return $this->view->render($response, 'donate.html.twig');
});

// STATE 2&3: receiving submission
$app->post('/donate', function ($request, $response, $args) use ($log) {
    if (!isset($_SESSION['user'])) {
        echo '<script>alert("To visit this page,You should login at first!")</script>';
        return $this->view->render($response, 'error_notlogin.html.twig');
    }

    $userid = $_SESSION['user']['id'];
    $donationdate = date("Y-m-d");
    $comments = $request->getParam('comments');
    $donationtype = $request->getParam('donationtype');
    $donationamount = $request->getParam('donationamount');

    //
    $errorList = [];
    if (strlen($comments) < 2 || strlen($comments) > 1000) {
        $errorList[] = "Comments must be 2-1000 characters long";
    }
    if ( $donationamount == NULL) {
        $errorList[] = "donation amount can not be null";
    }
    if ( $donationtype != 1 && $donationtype != 2) {
        $errorList[] = "donationtype should be choosed";
    } 

    $valuesList = ['userId' => $userid, 'donationAmount' => $donationamount, 'donationType' => $donationtype, 
                    'donationDate' => $donationdate, 'comments' => $comments ];
    if ($errorList) { // STATE 2: errors - redisplay the form
        return $this->view->render($response, 'donate.html.twig', ['errorList' => $errorList, 'v' => $valuesList]);
    } else { // STATE 3: success
        DB::insert('donations', $valuesList);

        $log->debug(sprintf("New pet created with Id=%s", DB::insertId()));
        return $this->view->render($response, 'donate_success.html.twig');
    }
});

