<?php
session_start();

use RobThree\Auth\Providers\Qr\BaconQrCodeProvider;
use RobThree\Auth\Providers\Qr\EndroidQrCodeProvider;
use RobThree\Auth\TwoFactorAuth;

if (!isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: ../private/registredHome.php");
    exit();
}
require_once('../../config.php');
require_once __DIR__ . '/../vendor/autoload.php';
require_once 'utilities.php';
ini_set('display_errors', 1);
ini_Set('display_startup_errors', 1);
error_reporting(E_ALL);

$db2 = connectToDatabase($servername, $username, $password, $login_db);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = "";

    if (isEmpty($_POST['email']) === true) {
        $errors .= "Nevyplnený e-mail.\n";
    }

    // TODO: validate if user entered correct e-mail format

    if (userExist($db2, $_POST['email']) === true) {
        $errors .= "Používateľ s týmto e-mailom už existuje.\n";
    }

    if (isEmpty($_POST['name']) === true) {
        $errors .= "Nevyplnené meno.\n";
    }

    // TODO: Implement name and surname length validation based on the database column length.
    if(strlen($_POST['name']) > 255){
        $errors .= "Name is too long.\n";
    }
    // TODO: Implement name and surname allowed characters validation.


    if (isEmpty($_POST['password']) === true) {
        $errors .= "Nevyplnené heslo.\n";
    }

    // TODO: Implement repeat password validation.
    if(($_POST['password']) !== ($_POST['passwordAgain'])){
        $errors .= "Hesla sa nerovnaju.\n";

    }

    if (isEmpty($errors)) {
        if (isset($_POST['name'], $_POST['email'], $_POST['password'])) {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $regPass = $_POST['password'];

            $tfa = new TwoFactorAuth(new EndroidQrCodeProvider());
            $user_secret = $tfa->createSecret();


            if ($user_secret) {
                $qr_code = $tfa->getQRCodeImageAsDataUri("Nobel Prizes", $user_secret);
                echo "<script>console.log('QR  ok: " . addslashes($qr_code) . "');</script>"; // Konzolba írja a QR kódot
                insertRegistration($db2, $name, $email, $regPass, $user_secret);
            } else {
                echo "<script>console.log('qr chyba.');</script>"; // Konzolba írja a hibát
                echo "<p>qr chyba.</p>";
            }
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
    <script src="regiForm.js"></script>
    <title>Index</title>
</head>
<body style="background-image: url('../pics/FEI_STU.png');background-repeat: no-repeat;min-height=100vh;height: auto;overflow: visible">
<?php if (isset($reg_status)) {
    echo "<h3>$reg_status</h3>";
} ?>
<div>
    <!--credit:https://mdbootstrap.com/docs/standard/extended/registration/-->
    <section class="vh-100 bg-image"
             >
        <div class="mask d-flex align-items-center h-100 ">
            <div class="container h-100">
                <div class="row d-flex justify-content-center align-items-center h-100">
                    <div class="col-12 col-md-9 col-lg-7 col-xl-6">
                        <div class="card" style="border-radius: 15px;">
                            <div class="card-body p-5">
                                <h2 class="text-uppercase text-center mb-5">Create an account</h2>

                                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                                    <div data-mdb-input-init class="form-outline mb-4">
                                        <input type="text" name="name" id="form3Example1cg"
                                               class="form-control form-control-lg" oninput="validateName()"/>
                                        <label class="form-label" for="form3Example1cg">Your Name</label>
                                        <small class="error" id="errorMessageName">Meno musi byt zadane</small>
                                    </div>

                                    <div data-mdb-input-init class="form-outline mb-4">
                                        <input type="text" name="email" id="form3Example3cg"
                                               class="form-control form-control-lg" oninput="controlOfZavinac()"/>
                                        <label class="form-label" for="form3Example3cg">Your Email</label>
                                        <small class="error" id="errorMessageEmail">Email musi mat @</small>
                                    </div>

                                    <div data-mdb-input-init class="form-outline mb-4">
                                        <input type="password" name="password" id="form3Example4cg"
                                               class="form-control form-control-lg" oninput="validatePassword()"/>
                                        <label class="form-label" for="form3Example4cg">Password</label>
                                        <small class="error" id="errorMessagePassword">Email musi mat @</small>

                                    </div>

                                    <div data-mdb-input-init class="form-outline mb-4">
                                        <input type="password" name="passwordAgain" id="form3Example4cdg"
                                               class="form-control form-control-lg"
                                               oninput="validatePasswordWithPasswordAgain()"/>
                                        <label class="form-label" for="form3Example4cdg">Repeat your password</label>
                                        <small class="error" id="errorMessagePasswordAgain">Email musi mat @</small>

                                    </div>


                                    <div class="d-flex justify-content-center">
                                        <button type="submit" data-mdb-button-init
                                                data-mdb-ripple-init
                                                class="btn btn-success btn-block btn-lg gradient-custom-4 text-body"
                                                onclick="validateForm(event)">Register
                                        </button>
                                    </div>

                                    <?php
                                    if (!empty($errors)) {
                                        echo "<br><strong>Chyby:</strong><br>";
                                        echo "<div class='err'>";
                                        echo nl2br($errors);
                                        echo "</div>";
                                    }
                                    if (isset($qr_code)) {
                                        $message = '<p>Zadajte kód: ' . $user_secret . ' do aplikácie pre 2FA</p>';
                                        $message .= '<p>alebo naskenujte QR kód:<br><img src="' . $qr_code . '" alt="qr kod pre aplikaciu authenticator"></p>';
                                        echo $message;
                                        echo '<p>Teraz sa môžete prihlásiť: <a href="login.php">Login stránka</a></p>';
                                    }
                                    ?>

                                    <p class="text-center text-muted mt-5 mb-0">Have already an account? <a
                                                href="login.php"
                                                class="fw-bold text-body"><u>Login here</u></a></p>

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