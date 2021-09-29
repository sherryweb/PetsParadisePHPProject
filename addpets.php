<?php

require_once 'vendor/autoload.php';
require_once 'init.php';

// STATE 1: first display of the form
$app->get('/addpets', function ($request, $response, $args) {
    if (!isset($_SESSION['user'])) {
        echo '<script>alert("To visit this page,You should login at first!")</script>';
        return $this->view->render($response, 'error_notlogin.html.twig');
    }
    return $this->view->render($response, 'addpets.html.twig');
});

function verifyUploadedPhoto(&$photoFilePath) {
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] != 4) { // file uploaded
        // print_r($_FILES);
        $photo = $_FILES['photo'];
        if ($photo['error'] != 0) {
            return "Error uploading photo " . $photo['error'];
        } 
        //if ($photo['size'] > 1024*1024) { // 1MB
        //    return "File too big. 1MB max is allowed.";
        //}
        $info = getimagesize($photo['tmp_name']);
        if (!$info) {
            return "File is not an image";
        }

        $ext = "";
        //switch ($info['mime']) {
        //    case 'image/jpeg': $ext = "jpg"; break;
        //    case 'image/jpg': $ext = "jpg"; break;
        //    case 'image/gif': $ext = "gif"; break;
        //    case 'image/bmp': $ext = "bmp"; break;
        //    case 'image/png': $ext = "png"; break;
        //    default:
        //        return "Only JPG, GIF and PNG file types are allowed";
        //    }
        //$photoFilePath = "uploads/" .  $photo['name'] . "." . $ext;
        $photoFilePath = "uploads/" .  $photo['name'];
    }
    return TRUE;
}

// STATE 2&3: receiving submission
$app->post('/addpets', function ($request, $response, $args) use ($log) {
    if (!isset($_SESSION['user'])) {
        echo '<script>alert("To visit this page,You should login at first!")</script>';
        return $this->view->render($response, 'error_notlogin.html.twig');
    }

    $addedby = $_SESSION['user']['id'];
    $petname = $request->getParam('petname');
    $status = "In Shelter";
    $species = $request->getParam('species');
    $breed = $request->getParam('breed');
    $birthday = $request->getParam('birthday');
    $size = $request->getParam('size');
    $color = $request->getParam('color');
    $description = $request->getParam('description');
    $weight = $request->getParam('weight');
    
     
    $errorList = [];
    
    if (!isset($_SESSION['user'])) {
        // if user is not authenticated then do NOT display the form but
        // only "access denied" message with link back to index. php

        $errorList[] = "You must sign in at first to add a new pet. <a href='/login'>Click to sign in</a> ";    
    }
    
    if (strlen($petname) < 2 || strlen($petname) > 50) {
        $errorList[] = "petName must be 2-50 characters long";
    }
    if (preg_match('/^[a-zA-Z0-9]{4,20}$/',$petname) != 1) {
        $errorList[] = "petname must be 4-20 characters long made up of lower-case characters and numbers";
        $petname = "";
    }
    if (!is_numeric($size) || $size < 0 || $size > 100) {
        $errorList[] = "Animal size must be a number between 0 and 100 inches";
        $size = 0.0;
    }
    if (!is_numeric($weight) || $weight < 0 || $weight > 1000) {
        $errorList[] = "Animal weight must be a number between 0 and 1000 pounds";
        $weight = 0.0;
    }
    if (strlen($description) < 2 || strlen($description) > 1000) {
        $errorList[] = "Pets description must be 2-1000 characters long";
    }
    if ( $species == NULL) {
        $errorList[] = "Pets species can not be null";
    }
    if ( $breed == NULL) {
        $errorList[] = "Pets breed can not be null";
    } 
    
    $imageFolderPath = NULL;  
    $retval = verifyUploadedPhoto($imageFolderPath);
    if ($retval !== TRUE) {
        $errorList[] = $retval; // string with error was returned - add it to list of errors
    }

    //
    $valuesList = ['name' => $petname, 'status' => $status, 'species' => $species, 
                    'breed' => $breed, 'birthday' => $birthday, 'size' => $size, 
                    'color' => $color, 'description' => $description, 'weight' => $weight,
                    'ImageFolderPath' => $imageFolderPath, 'addedBy' => $addedby ];
    if ($errorList) { // STATE 2: errors - redisplay the form
        return $this->view->render($response, 'addpets.html.twig', ['errorList' => $errorList, 'v' => $valuesList]);
    } else { // STATE 3: success
        DB::insert('pets', $valuesList);

        if ($imageFolderPath != null) {
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $imageFolderPath) != true) {
                echo "Error moving the uploaded file. Action aborted.";
            }
        }

        $log->debug(sprintf("New pet created with Id=%s", DB::insertId()));
        return $this->view->render($response, 'addpets_success.html.twig');
    }
});

