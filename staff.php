<?php

require_once 'vendor/autoload.php';
require_once 'init.php';

// STATE 1: first display of the form
$app->get('/staff', function ($request, $response, $args) {
    $adoptionList = DB::query("SELECT * FROM adoptions WHERE status = 0 ORDER BY id DESC");
    $communicateList = DB::query("SELECT * FROM communications WHERE status = 0 ORDER BY id DESC");
    return $this->view->render($response, 'stafftasks.html.twig', ['adoptionlist' => $adoptionList, 'communicatelist' => $communicateList]);
});

//STATE 1: first display of the form
$app->get('/adoptionconfirm', function ($request, $response, $args) {
    if (isset($_GET['id'])) { 
        $id = $_GET['id'];
    }
    
    if (!isset($_SESSION['user'])) {
        echo '<script>alert("To visit this page,You should login at first!")</script>';
        return $this->view->render($response, 'error_notlogin.html.twig');
    }

    $req = DB::queryFirstRow("SELECT * FROM adoptions WHERE id=%d", $id); 
    $pet = DB::queryFirstRow("SELECT * FROM pets WHERE id=%d", $req['petId']);
    if ($pet) {
        return $this->view->render($response, 'adoptionconfirm.html.twig', ['r' => $req, 'p' => $pet]);
    } else { // not found - cause 404 here
        throw new \Slim\Exception\NotFoundException($request, $response);
    }
});

// STATE 2&3: receiving submission
$app->post('/adoptionconfirm', function ($request, $response, $args) use ($log) {
    if (isset($_GET['id'])) { // we're receving a submission
        $id = $_GET['id'];
    }

    if (!isset($_SESSION['user'])) {
        echo '<script>alert("To visit this page,You should login at first!")</script>';
        return $this->view->render($response, 'error_notlogin.html.twig');
    }

    $confirmedby = $_SESSION['user']['id'];
    $req = DB::queryFirstRow("SELECT * FROM adoptions WHERE id=%d", $id); 
    $pet = DB::queryFirstRow("SELECT * FROM pets WHERE id=%d", $req['petId']);
    if (!$pet) {
        throw new \Slim\Exception\NotFoundException($request, $response);
    } 

    $confirmationresult = $request->getParam('confirmationresult');

    $errorList = [];
    if ( $confirmationresult != 1 && $confirmationresult != 2) {
        $errorList[] = "Status error";
    }

    $newstatus = "Adopted";

    if ($errorList) { // STATE 2: errors - redisplay the form
        return $this->view->render($response, 'adoptionconfirm.html.twig', ['errorList' => $errorList, 'r' => $req, 'p' => $pet]);
    } else { // STATE 3: success
        DB::update('adoptions', ['status' => $confirmationresult, 'confirmedBy' => $confirmedby], "id=%s", $id);
        DB::update('pets', ['status' => $newstatus], "id=%s", $pet['id']);
        $log->debug(sprintf("Adoption request confirmed with Id=%s", $id));
        return $this->view->render($response, 'adoptionconfirm_success.html.twig');
    }
});

//STATE 1: first display of the form
$app->get('/questionresponse', function ($request, $response, $args) {
    if (isset($_GET['id'])) { // we're receving a submission
        $id = $_GET['id'];
    }
    if (!isset($_SESSION['user'])) {
        echo '<script>alert("To visit this page,You should login at first!")</script>';
        return $this->view->render($response, 'error_notlogin.html.twig');
    }    

    $question = DB::queryFirstRow("SELECT * FROM communications WHERE id=%d", $id); 
    $pet = DB::queryFirstRow("SELECT * FROM pets WHERE id=%d", $question['petId']);
    if ($pet) {
        return $this->view->render($response, 'questionresponse.html.twig', ['q' => $question, 'p' => $pet]);
    } else { // not found - cause 404 here
        throw new \Slim\Exception\NotFoundException($request, $response);
    }
});


// STATE 2&3: receiving submission
$app->post('/questionresponse', function ($request, $response, $args) use ($log) {
    if (isset($_GET['id'])) { // we're receving a submission
        $id = $_GET['id'];
    }
    if (!isset($_SESSION['user'])) {
        echo '<script>alert("To visit this page,You should login at first!")</script>';
        return $this->view->render($response, 'error_notlogin.html.twig');
    }

    $question = DB::queryFirstRow("SELECT * FROM communications WHERE id=%d", $id); 
    $pet = DB::queryFirstRow("SELECT * FROM pets WHERE id=%d", $question['petId']);
    if (!$pet) {
        throw new \Slim\Exception\NotFoundException($request, $response);
    } 

    $answers = $request->getParam('answers');
    $staffid = $_SESSION['user']['id'];
    $answerdate = date("Y-m-d");
    $status = 1;

    $errorList = [];
    if (strlen($answers) < 2 || strlen($answers) > 1000) {
        $errorList[] = "answers must be 2-1000 characters long";
    }

    if ($errorList) { // STATE 2: errors - redisplay the form
        return $this->view->render($response, 'questionresponse.html.twig', ['errorList' => $errorList, 'q' => $question, 'p' => $pet]);
    } else { // STATE 3: success
        DB::update('communications', ['answers' => $answers, 'answerDate' => $answerdate, 'staffId' => $staffid, 'status' => $status], "id=%s", $id);
        $log->debug(sprintf("Communication response with Id=%s", $id));
        return $this->view->render($response, 'questionresponse_success.html.twig');
    }
});

