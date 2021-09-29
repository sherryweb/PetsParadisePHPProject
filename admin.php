<?php

require_once 'vendor/autoload.php';
require_once 'init.php';

$userCount = DB::query("SELECT COUNT(*) FROM users");

$app->get('/admin', function ($request, $response, $args) {
    $userList = DB::query("SELECT * FROM users ORDER BY id DESC");
    return $this->view->render($response, 'admin/indexAdmin.html.twig', ['list' => $userList]);
});

//********************************User CRUD *************************************************** */
$app->get('/admin/user', function ($request, $response, $args) {
    $userList = DB::query("SELECT * FROM users ORDER BY id DESC");
    return $this->view->render($response, 'admin/user_list.html.twig', ['list' => $userList]);
});

$app->get('/user/edit', function ($request, $response, $args) {
    if (isset($_GET['id'])) {
        $id=$_GET['id'];
    }
    $userList = DB::queryFirstRow("SELECT * FROM users WHERE id=%d",$id);
    return $this->view->render($response, 'admin/user_edit.html.twig', ['list' => $userList]);
});

//STATE 2&3: receiving submission
$app->post('/user/edit', function ($request, $response, $args) use ($log) {
    if (isset($_GET['id'])) {
        $id=$_GET['id'];
    }
    $userName = $request->getParam('userName');
    $email = $request->getParam('email');
    $phone = $request->getParam('phone');

    $errorList = [];
    if (preg_match('/^[a-zA-Z0-9]{4,20}$/',$userName) != 1) {
        $errorList[] = "User name must be 4-20 characters long made up of characters and numbers";
        $userName = "";
    }
    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $errorList[] = "Email does not look valid";
        $email = "";
    }else { // STATE 3: success
        $data = ['userName' => $userName, 'email' => $email, 'phone' => $phone];
        DB::update('users', $data, "id=%d", $id);
        $log->debug(sprintf("User with Id=%s updated", $id));
        // FLASH MESSAGE INSTEAD of success page
        setFlashMessage("User update successfully");
        // return $this->view->render($response, 'placebid_success.html.twig');
        return $this->view->render($response, 'admin/users_edit_success.html.twig');
    }
});

$app->get('/user/delete', function ($request, $response, $args) {
    if (isset($_GET['id'])) {
        $id=$_GET['id'];
    }
    $userList = DB::queryFirstRow("SELECT * FROM users WHERE id=%d",$id);
    return $this->view->render($response, 'admin/user_delete.html.twig', ['list' => $userList]);
});

$app->post('/user/delete', function ($request, $response, $args) {
    if (isset($_GET['id'])) {
        $id=$_GET['id'];
    }
    DB::delete('users', "id=%d", $id);
    return $this->view->render($response,'admin/user_delete_success.html.twig');

});

$app->get('/user/add', function ($request, $response, $args) {
    if (isset($_GET['id'])) {
        $id=$_GET['id'];
    }
    $userList = DB::queryFirstRow("SELECT * FROM users WHERE id=%d",$id);
    return $this->view->render($response, 'admin/user_add.html.twig', ['list' => $userList]);
});

//STATE 2&3: receiving submission
$app->post('/user/add', function ($request, $response, $args) use ($log) {
    $userName = $request->getParam('userName');
    $type = $request->getParam('type');
    $email = $request->getParam('email');
    $phone = $request->getParam('phone');
    $address = $request->getParam('address');
    $city = $request->getParam('city');
    $zipcode = $request->getParam('zipcode');
    $password = $request->getParam('password');
    $repassword = $request->getParam('password1');

    $errorList = [];
    if (preg_match('/^[a-zA-Z0-9]{4,20}$/',$userName) != 1) {
        $errorList[] = "User name must be 4-20 characters long made up of characters and numbers";
        $userName = "";
    }

    $option = isset($_POST['type']) ? $_POST['type'] : false;
   if ($option== "0") {
    $errorList[] = "User type is required";
   }
    // verify email
    if (filter_var($email, FILTER_VALIDATE_EMAIL) == FALSE) {
            array_push($errorList, "Email does not look valid");
            $email = "";
    } else {
            // is email already in use?
        $record = DB::queryFirstRow("SELECT id FROM users WHERE email=%s", $email);
        if ($record) {
        array_push($errorList, "This email is already registered");
        $email = "";
        }
    }

    if ($password != $repassword) {
        $errorList[] = "Passwords do not match";
    }
    if ((strlen($password) < 6) || (strlen($password) > 100)
    || (preg_match("/[A-Z]/", $password) == FALSE )
    || (preg_match("/[a-z]/", $password) == FALSE )
    || (preg_match("/[0-9]/", $password) == FALSE )) {
        $errorList[] = "Password must be 6-100 characters long, "
        . "with at least one uppercase, one lowercase, and one digit in it";
        $password = "";
    }

    $data = ['userName' => $userName, 'type' => $type, 'email' => $email, 
    'phone' => $phone, 'address' => $address, 'city' => $city, 
    'zipcode' => $zipcode, 'password' => $password];

    if ($errorList) {
        return $this->view->render($response, 'admin/user_add.html.twig',
                   [ 'errorList' => $errorList, 'v' =>$data ]);
    }else{
        DB::insert('users', $data);
        // FLASH MESSAGE INSTEAD of success page
        setFlashMessage("User update successfully");
        // return $this->view->render($response, 'placebid_success.html.twig');
        return $this->view->render($response, 'admin/user_add_success.html.twig');
       }
});

//********************************Activity CRUD *************************************************** */
$app->get('/admin/activity', function ($request, $response, $args) {
    $activityList = DB::query("SELECT * FROM activities ORDER BY id DESC");
    return $this->view->render($response, 'admin/activity_list.html.twig', ['list' => $activityList]);
});

$app->get('/activity/edit', function ($request, $response, $args) {
    if (isset($_GET['id'])) {
        $id=$_GET['id'];
    }
    $activityList = DB::queryFirstRow("SELECT * FROM activities WHERE id=%d",$id);
    return $this->view->render($response, 'admin/activity_edit.html.twig', ['list' => $activityList]);
});

$app->post('/activity/edit', function ($request, $response, $args) use ($log) {
    if (isset($_GET['id'])) {
        $id=$_GET['id'];
    }
    $theme = $request->getParam('theme');
    $description = $request->getParam('description');
    $type = $request->getParam('type');
    $givenTime = $request->getParam('givenTime');

    $errorList = [];

    if ($theme==null) {
        $errorList[] = "Please entre activity theme";
       }
    if ($description==null) {
        $errorList[] = "Please entre activity description";
    }
    $option = isset($_POST['type']) ? $_POST['type'] : false;
   if ($option== "0") {
    $errorList[] = "Activity type is required";
   }

    $data = ['theme' => $theme, 'description' => $description, 'type' => $type,'givenTime' => $givenTime];
    if ($errorList) {
        return $this->view->render($response, 'admin/activity_edit.html.twig',
                   [ 'errorList' => $errorList, 'list' =>$data ]);
    }else{
        DB::update('activities', $data, "id=%d", $id);
        $log->debug(sprintf("Activity with Id=%s updated", $id));
        // FLASH MESSAGE INSTEAD of success page
        setFlashMessage("Activity update successfully");
        // return $this->view->render($response, 'placebid_success.html.twig');
        return $this->view->render($response, 'admin/activity_edit_success.html.twig');
    }
});

$app->get('/activity/delete', function ($request, $response, $args) {
    if (isset($_GET['id'])) {
        $id=$_GET['id'];
    }
    $activityList = DB::queryFirstRow("SELECT * FROM activities WHERE id=%d",$id);
    return $this->view->render($response, 'admin/activity_delete.html.twig', ['list' => $activityList]);
});

$app->post('/activity/delete', function ($request, $response, $args) {
    if (isset($_GET['id'])) {
        $id=$_GET['id'];
    }
    DB::delete('activities', "id=%d", $id);
    return $this->view->render($response,'admin/activity_delete_success.html.twig');
});


$app->get('/activity/add', function ($request, $response, $args) {
    if (isset($_GET['id'])) {
        $id=$_GET['id'];
    }
    $activityList = DB::queryFirstRow("SELECT * FROM activities WHERE id=%d",$id);
    return $this->view->render($response, 'admin/activity_add.html.twig', ['list' => $activityList]);
});


//STATE 2&3: receiving submission
$app->post('/activity/add', function ($request, $response, $args) use ($log) {
    $theme = $request->getParam('theme');
    $description = $request->getParam('description');
    $type = $request->getParam('type');
    $givenTime = $request->getParam('givenTime');

    $errorList = [];

    if ($theme==null) {
        $errorList[] = "Please entre activity theme";
       }

    $record = DB::queryFirstRow("SELECT id FROM activities WHERE theme=%s", $theme);
    if ($record) {
       array_push($errorList, "This theme is already used");
       $theme = "";
    }

    if ($description==null) {
        $errorList[] = "Please entre activity description";
    }
    $option = isset($_POST['type']) ? $_POST['type'] : false;
   if ($option== "0") {
    $errorList[] = "Activity type is required";
   }

    $data = ['theme' => $theme, 'description' => $description, 'type' => $type,'givenTime' => $givenTime];

    if ($errorList) {
        return $this->view->render($response, 'admin/activity_add.html.twig',
                   [ 'errorList' => $errorList, 'list' =>$data ]);
    }else{
        DB::insert('activities', $data);
        // FLASH MESSAGE INSTEAD of success page
        setFlashMessage("Activity update successfully");
        // return $this->view->render($response, 'placebid_success.html.twig');
        return $this->view->render($response, 'admin/activity_add_success.html.twig');
       }
});


// Attach middleware that verifies only Admin can access /admin... URLs

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

// Function to check string starting 
// with given substring 

function startsWith($string, $startString) 
{ 
    $len = strlen($startString); 
    return (substr($string, 0, $len) === $startString); 
} 

$app->add(function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
    $url = $request->getUri()->getPath();
    // print_r($url);
    if (startsWith($url, "/admin")||startsWith($url, "/user")) {// refuse if user not logged in AS ADMIN
        if (!isset($_SESSION['user']) || ($_SESSION['user']['type'] !== 'admin')) {
            $response = $response->withStatus(403);
            return $this->view->render($response, 'admin/error_access_denied.html.twig');
        }
    }
    return $next($request, $response);
});