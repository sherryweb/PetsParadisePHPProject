<?php

require_once 'vendor/autoload.php';

require_once 'init.php';

// $fb = new Facebook\Facebook([
//     'app_id' => '2847778545469742',
//     'app_secret' => '6c402912a31eeb0e81f1d90ff0a64fab',
//     'default_graph_version' => 'v5.7',
//     ]);
  
//   $helper = $fb->getRedirectLoginHelper();
  
//   $permissions = ['email']; // Optional permissions
//   $loginUrl = $helper->getLoginUrl('http://petsparadise.local:8888/', $permissions);
  
//   echo '<a href="' . $loginUrl . '">Log in with Facebook!</a>';

// $fb = new Facebook\Facebook([
//     'app_id' => '{app-id}',
//     'app_secret' => '{app-secret}',
//     'default_graph_version' => 'v2.10',
//     ]);
  
//   $helper = $fb->getRedirectLoginHelper();
  
//   try {
//     $accessToken = $helper->getAccessToken();
//   } catch(Facebook\Exceptions\FacebookResponseException $e) {
//     // When Graph returns an error
//     echo 'Graph returned an error: ' . $e->getMessage();
//     exit;
//   } catch(Facebook\Exceptions\FacebookSDKException $e) {
//     // When validation fails or other local issues
//     echo 'Facebook SDK returned an error: ' . $e->getMessage();
//     exit;
//   }
  
//   if (! isset($accessToken)) {
//     if ($helper->getError()) {
//       header('HTTP/1.0 401 Unauthorized');
//       echo "Error: " . $helper->getError() . "\n";
//       echo "Error Code: " . $helper->getErrorCode() . "\n";
//       echo "Error Reason: " . $helper->getErrorReason() . "\n";
//       echo "Error Description: " . $helper->getErrorDescription() . "\n";
//     } else {
//       header('HTTP/1.0 400 Bad Request');
//       echo 'Bad request';
//     }
//     exit;
//   }
  
//   // Logged in
//   echo '<h3>Access Token</h3>';
//   var_dump($accessToken->getValue());
  
//   // The OAuth 2.0 client handler helps us manage access tokens
//   $oAuth2Client = $fb->getOAuth2Client();
  
//   // Get the access token metadata from /debug_token
//   $tokenMetadata = $oAuth2Client->debugToken($accessToken);
//   echo '<h3>Metadata</h3>';
//   var_dump($tokenMetadata);
  
//   // Validation (these will throw FacebookSDKException's when they fail)
//   $tokenMetadata->validateAppId($config['app_id']);
//   // If you know the user ID this access token belongs to, you can validate it here
//   //$tokenMetadata->validateUserId('123');
//   $tokenMetadata->validateExpiration();
  
//   if (! $accessToken->isLongLived()) {
//     // Exchanges a short-lived access token for a long-lived one
//     try {
//       $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
//     } catch (Facebook\Exceptions\FacebookSDKException $e) {
//       echo "<p>Error getting long-lived access token: " . $e->getMessage() . "</p>\n\n";
//       exit;
//     }
//     echo '<h3>Long-lived</h3>';
//     var_dump($accessToken->getValue());
//   }
  
//   $_SESSION['fb_access_token'] = (string) $accessToken;



// LOGIN / LOGOUT USING FLASH MESSAGES TO CONFIRM THE ACTION
// $config = [
//     // Location where to redirect users once they authenticate with a provider
//     'callback' => 'https://example.com/path/to/this/script.php',
//     // Providers specifics
//     'providers' => [
//         'Facebook' => [
//             'enabled' => true,     // Optional: indicates whether to enable or disable Twitter adapter. Defaults to false
//             'keys' => [
//                 'key' => '2847778545469742', // 
//                 'secret' => '6c402912a31eeb0e81f1d90ff0a64fab'  //  
//             ]
//         ],
//         'Google' => [
//             'enabled' => true, 
//             'keys' => ['id' => '', 
//             'secret' => ''
//             ]], // To populate in a similar way to Twitter           
//     ]
// ];

// try {
//     $hybridauth = new Hybridauth\Hybridauth($config);
//     $adapter = $hybridauth->authenticate('Facebook'); 
//     // Returns a boolean of whether the user is connected with Facebook
//     $isConnected = $adapter->isConnected();
 
//     // Retrieve the user's profile
//     $userProfile = $adapter->getUserProfile();

//     // Inspect profile's public attributes
//     var_dump($userProfile);

//     // Disconnect the adapter (log out)
//     $adapter->disconnect();
// }
// catch(\Exception $e){
//     echo 'Oops, we ran into an issue! ' . $e->getMessage();
// }


// STATE 1: first display
$app->get('/login', function ($request, $response, $args) {
    return $this->view->render($response, 'admin/login.html.twig');
});

// STATE 2&3: receiving submission
$app->post('/login', function ($request, $response, $args) use ($log) {
    $email = $request->getParam('email');
    $password = $request->getParam('password');
    //
    $errorList = [];

    $record = DB::queryFirstRow("SELECT id,userName,type,email,password FROM users WHERE email=%s", $email);
    $loginSuccess = false;
    if ($record) {
        if ($record['password'] == $password) {
            $loginSuccess = true;
        }        
    }
    //
    if (!$loginSuccess) {
        $log->info(sprintf("Login failed for email %s from %s", $email, $_SERVER['REMOTE_ADDR']));
        return $this->view->render($response, 'index.html.twig', [ 'error' => true ]);
    } else {
        unset($record['password']); // for security reasons remove password from session
        $_SESSION['user'] = $record; // remember user logged in
        $log->debug(sprintf("Login successful for email %s, uid=%d, from %s", $email, $record['id'], $_SERVER['REMOTE_ADDR']));
        if ($record['type'] != 'staff' && $record['type'] != 'admin')
            return $this->view->render($response, 'index.html.twig', ['userSession' => $_SESSION['user'] ]);
        else {
            if ($record['type'] == 'staff') {
                $adoptionList = DB::query("SELECT * FROM adoptions WHERE status = 0 ORDER BY id DESC");
                $communicateList = DB::query("SELECT * FROM communications WHERE status = 0 ORDER BY id DESC");
                return $this->view->render($response, 'stafftasks.html.twig', ['userSession' => $_SESSION['user'], 'adoptionlist' => $adoptionList, 'communicatelist' => $communicateList]);
            }
        }
    }
});

$app->get('/session', function ($request, $response, $args) {
    echo "<pre>\n";
    print_r($_SESSION);
    return $response->write("");
});

$app->get('/logout', function ($request, $response, $args) use ($log) {
    $log->debug(sprintf("Logout successful for uid=%d, from %s", @$_SESSION['user']['id'], $_SERVER['REMOTE_ADDR']));
    unset($_SESSION['user']);
    return $response->withRedirect("/");
});
