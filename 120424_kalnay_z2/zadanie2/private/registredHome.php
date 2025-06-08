<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../public/login.php");
    exit;
}

require_once '../vendor/autoload.php';

use Google\Client;

$client = new Client();
$client->setAuthConfig('../../client_secret.json');


if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
    $client->setAccessToken($_SESSION['access_token']);

    // Get the user profile info from Google OAuth 2.0.
    $oauth = new Google\Service\Oauth2($client);
    $account_info = $oauth->userinfo->get();


    $_SESSION['name'] = $account_info->name;
    $name = $account_info->name;
    $_SESSION['gid'] = $account_info->id;
    $_SESSION['email'] = $account_info->email;

    echo "<script>
             localStorage.setItem('loggedInUser', '$name');
          </script>";

}

// TODO: Provide the user with the option to temporarily disable or reset 2FA.
// TODO: Provide the user with the option to reset the password.

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/ownDesign.css">
    <link rel="stylesheet" href="../css/vlastne.css">
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <script src="registeredWelcome.js"></script>
    <title>Title</title>
</head>
<body>
<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark ">
        <div class="container">
            <ul class="navbar-nav mx-auto">
               <!-- <li class="nav-item">
                    <a class="nav-link text-light" href="#">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </li>-->
                <li class="nav-item">
                    <a class="nav-link text-light" href="laureates.php">
                        <i class="bi bi-trophy"></i> Laureates
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-light" href="settings.php">
                        <i class="bi bi-gear"></i> Settings
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-light" href="logout.php">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</header>
<div>
    <!--credit:https://mdbootstrap.com/docs/standard/extended/registration/-->
    <section class="vh-100 bg-image" style="background-image: url('../pics/FEI_STU.png');">
        <div class="mask d-flex align-items-center h-100 ">
            <div class="container h-100">
                <div class="row d-flex justify-content-center align-items-center h-100">
                    <div class="col-12 col-md-8 col-lg-5 col-xl-5">
                        <div class="card" style="border-radius: 15px;">
                            <div class="card-body p-4">
                                <h2 class="text-uppercase text-center mb-5" id="welcomeMessage">Welcome</h2>
                                <div class="d-flex flex-column align-items-center">
                                    <a href="ModifyLaureates.php"
                                       class="btn btn-success btn-lg gradient-custom-4 text-body w-30 mb-3">
                                        Add laureates
                                    </a>
                                    <button class="btn btn-danger" id="logoutButton">Logout</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>


</body>
</html>
