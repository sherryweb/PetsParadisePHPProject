<?php

require_once 'vendor/autoload.php';
require_once 'init.php';

// STATE 1: first display of the form
$app->get('/adoptionlist', function ($request, $response, $args) {
    $status = "In Shelter";
    $dogspecies = "Dog";
    $catspecies = "Cat";
    $birdspecies = "Bird";
    $dogList = DB::query("SELECT * FROM pets WHERE status = %s and species = %s ORDER BY id DESC", $status, $dogspecies);
    $catList = DB::query("SELECT * FROM pets WHERE status = %s and species = %s ORDER BY id DESC", $status, $catspecies);
    $birdList = DB::query("SELECT * FROM pets WHERE status = %s and species = %s ORDER BY id DESC", $status, $birdspecies);
    return $this->view->render($response, 'list.html.twig', ['doglist' => $dogList, 'catlist' => $catList, 'birdlist' => $birdList]);
});

//STATE 1: first display of the form
$app->get('/adoption', function ($request, $response, $args) {
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
        return $this->view->render($response, 'adoption.html.twig', ['p' => $pet]);
    } else { // not found - cause 404 here
        throw new \Slim\Exception\NotFoundException($request, $response);
    }
});

// STATE 2&3: receiving submission
$app->post('/adoption', function ($request, $response, $args) use ($log) {
    if (isset($_GET['id'])) { // we're receving a submission
        $petid = $_GET['id'];
    }

    if (!isset($_SESSION['user'])) {
        echo '<script>alert("To visit this page,You should login at first!")</script>';
        return $this->view->render($response, 'error_notlogin.html.twig');
    }

    $userid = $_SESSION['user']['id'];
    $type = $request->getParam('type');
    $begindate = $request->getParam('begindate');
    $enddate = $request->getParam('enddate');    
    $notes = $request->getParam('notes');
    $status = 0;
    
    $errorList = [];

    if (strlen($notes) < 2 || strlen($notes) > 1000) {
        $errorList[] = "Adoption notes must be 2-1000 characters long";
    }

    $pet = DB::queryFirstRow("SELECT * FROM pets WHERE id=%d", $petid);
    if (!$pet) {
        $errorList[] = "petid doesn't exist";
        //throw new \Slim\Exception\NotFoundException($request, $response);
    }
    $valuesList = ['userId' => $userid, 'petId' => $petid, 'type' => $type, 
                    'beginDate' => $begindate, 'endDate' => $enddate, 'status' => $status, 
                    'notes' => $notes];

    if ($errorList) { // STATE 2: errors - redisplay the form
        return $this->view->render($response, 'adoption.html.twig', ['errorList' => $errorList, 'p' => $pet, 'v' => $valueList]);
    } else { // STATE 3: success
        DB::insert('adoptions', $valuesList);
        $log->debug(sprintf("Adoption request created with Id=%s", DB::insertId()));
        return $this->view->render($response, 'adoption_success.html.twig');
    }
});



