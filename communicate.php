<?php

require_once 'vendor/autoload.php';
require_once 'init.php';

// STATE 1: first display of the form
$app->get('/communicate', function ($request, $response, $args) {
    if (isset($_GET['id'])) { // we're receving a submission
        $id = $_GET['id'];
    }

    if (!isset($_SESSION['user'])) {
        echo '<script>alert("To visit this page,You should login at first!")</script>';
        return $this->view->render($response, 'error_notlogin.html.twig');
    }
     
    $pet = DB::queryFirstRow("SELECT * FROM pets WHERE id=%d", $id);
    $communications = DB::query("SELECT * FROM communications WHERE petId=%d", $id);
    if ($pet) {
        return $this->view->render($response, 'communicate.html.twig', ['p' => $pet, 'c' => $communications]);
    } else { // not found - cause 404 here
        throw new \Slim\Exception\NotFoundException($request, $response);
    }
}); 

// STATE 2&3: receiving submission
$app->post('/communicate', function ($request, $response, $args) use ($log) {
    if (isset($_GET['id'])) { // we're receving a submission
        $id = $_GET['id'];
    }

    if (!isset($_SESSION['user'])) {
        echo '<script>alert("To visit this page,You should login at first!")</script>';
        return $this->view->render($response, 'error_notlogin.html.twig');
    }

    $userid = $_SESSION['user']['id'];
    $questions = $request->getParam('questions');
    $questiondate = date("Y-m-d");
    $status = 0;  // unprocessed request
    
    $errorList = [];

    if (strlen($questions) < 2 || strlen($questions) > 1000) {
        $errorList[] = "Pet questions must be 2-1000 characters long";
    }

    $pet = DB::queryFirstRow("SELECT * FROM pets WHERE id=%d", $id);
    if (!$pet) {
        $errorList[] = "petid doesn't exist";
        //throw new \Slim\Exception\NotFoundException($request, $response);
    }

    $communications = DB::query("SELECT * FROM communications WHERE petId=%d", $id);

    $valuesList = ['userId' => $userid, 'petId' => $id, 'questions' => $questions, 
                    'questionDate' => $questiondate, 'status' => $status ];

    if ($errorList) { // STATE 2: errors - redisplay the form
        return $this->view->render($response, 'communicate.html.twig', ['errorList' => $errorList, 'p' => $pet, 'v' => $valuesList, 'c' => $communications]);
    } else { // STATE 3: success
        DB::insert('communications', $valuesList);
        $log->debug(sprintf("Adoption request created with Id=%s", DB::insertId()));
        return $this->view->render($response, 'communicate_success.html.twig');
    }
});



