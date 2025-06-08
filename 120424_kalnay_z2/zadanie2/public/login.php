<?php
session_start();

if (!isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: ../private/registredHome.php");
    exit();
}

require_once('../../config.php');
require_once '../vendor/autoload.php';
require_once 'utilities.php';
ini_set('display_errors', 1);
ini_Set('display_startup_errors', 1);
error_reporting(E_ALL);

use RobThree\Auth\Providers\Qr\BaconQrCodeProvider;
use RobThree\Auth\Providers\Qr\EndroidQrCodeProvider;
use RobThree\Auth\TwoFactorAuth;
$redirect_uri = "https://node60.webte.fei.stuba.sk/zadanie2/oauth2callback.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $db2 = connectToDatabase($servername, $username, $password, $login_db);
    $errors = "";

    if (isset($_POST['email'], $_POST['password'])) {
        $email = $_POST['email'];
        $regPass = $_POST['password'];

        $stmt = $db2->prepare("SELECT name,heslo,2fa_code,created_at FROM login_input WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($name, $db_password, $fa_code, $created_at);
            $stmt->fetch();
            if(filter_var($email,FILTER_VALIDATE_EMAIL)){
                if (password_verify($regPass, $db_password)) {
                    $tfa = new TwoFactorAuth(new EndroidQrCodeProvider());
                    if ($tfa->verifyCode($fa_code, $_POST['2fa'], 2)) {
                        $_SESSION['email'] = $email;
                        $_SESSION['name'] = $name;
                        $_SESSION["loggedin"] = true;
                        $logStmt = $db2->prepare("INSERT INTO users_login (login_type,email, fullname) VALUES ('local',?,?)");
                        $logStmt->bind_param("ss", $email,$name);
                        $logStmt->execute();

                        echo "<script>
                        console.log('Storing logged-in user in localStorage');
                        localStorage.setItem('loggedInUser', '" . addslashes($name) . "');
                        window.location.href = '../private/registredHome.php';
                    </script>";
                        exit();
                    }
                    else {
                        $errors = "Neplatny kod 2F.";

                    }
            }
                else {
                    $errors = "Incorrect password or email";

                }

            } else {
                $errors = "Incorrect password or email";

            }
        } else {
            $errors = "Incorrect password or email";

        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/ownDesign.css">
    <link rel="stylesheet" href="../css/vlastne.css">
    <script src="loginError.js"></script>
    <title>Login</title>
</head>
<body>

<div>
    <!--credit:https://mdbootstrap.com/docs/standard/extended/registration/-->
    <section class="vh-100 bg-image"
             style="background-image: url('../pics/FEI_STU.png');">
        <div class="mask d-flex align-items-center h-100 ">
            <div class="container h-100">
                <div class="row d-flex justify-content-center align-items-center h-100">
                    <div class="col-12 col-md-9 col-lg-7 col-xl-6">
                        <div class="card" style="border-radius: 15px;">
                            <div class="card-body p-5">
                                <h2 class="text-uppercase text-center mb-5">Login</h2>

                                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">

                                    <div data-mdb-input-init class="form-outline mb-4">
                                        <input type="text" name="email" id="form3Example3cg"
                                               class="form-control form-control-lg"/>
                                        <label class="form-label" for="form3Example3cg">Your Email</label>
                                    </div>

                                    <div data-mdb-input-init class="form-outline mb-4">
                                        <input type="password" name="password" id="form3Example4cg"
                                               class="form-control form-control-lg"/>
                                        <label class="form-label" for="form3Example4cg">Password</label>
                                        <small class="error" id="errorWrongPassword">Email not found or incorrect
                                            password</small>

                                    </div>
                                    <div data-mdb-input-init class="form-outline mb-4">
                                        <input type="number" name="2fa" value="" id="2fa"
                                               class="form-control form-control-lg"/>
                                        <label class="form-label" for="form3Example4cg">2FA</label>
                                        <small class="error" id="errorWrongPassword">Email not found or incorrect
                                            password</small>
                                    </div>

                                    <div class="d-flex justify-content-center">
                                        <button type="submit" data-mdb-button-init
                                                data-mdb-ripple-init
                                                class="btn btn-success btn-block btn-lg gradient-custom-4 text-body">
                                            Login
                                        </button>
                                    </div>
                                    <?php
                                    if (!empty($errors)) {
                                    echo "<br><strong>Chyby:</strong><br>";
                                    echo "<div class='err'>";
                                        echo nl2br($errors);
                                        echo "</div>";
                                    }
                                    ?>
                                    <div class="d-flex justify-content-center" style="margin-top: 1rem">
                                        <p>Alebo sa prihláste pomocou <a href="<?php echo filter_var($redirect_uri, FILTER_SANITIZE_URL) ?>">Google konta</a></p>
                                    </div>
                                    <p class="text-center text-muted mt-5 mb-0">Don't have an account yet? Sign up here.
                                        <a href="registration.php"
                                           class="fw-bold text-body"><u>Register here</u></a></p>
                                </form>

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