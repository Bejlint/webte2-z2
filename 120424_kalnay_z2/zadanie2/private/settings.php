<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../public/login.php");
    exit;
}
require_once '../../config.php';
require_once '../vendor/autoload.php';
$error = "";
$success = "";
$db2 = connectToDatabase($servername, $username, $password, $login_db);
$email = $_SESSION['email'];
$sql = "UPDATE login_input SET heslo = ? WHERE email = ?";
$sql_select = "SELECT heslo FROM login_input WHERE email = ?";

$sql_logins = "SELECT login_time FROM users_login WHERE email = ? ORDER BY login_time DESC";
$logins = [];

if ($stmt = $db2->prepare($sql_logins)) {
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $logins[] = $row;
    }

    $stmt->close();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($stmt = $db2->prepare($sql_select)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($db_password);
        $stmt->fetch();
        $stmt->close();

        if (password_verify($current_password, $db_password)) {
            if ($new_password === $confirm_password) {
                $new_password = password_hash($new_password, PASSWORD_BCRYPT);

                if ($stmt = $db2->prepare($sql)) {
                    $stmt->bind_param("ss", $new_password, $email);
                    if($stmt->execute()){
                        $success = "Password changed successfully!";
                    }
                    else{
                        $error = "An error occurred while updating the password.1";
                    }
                    $stmt->close();
                }
                else{
                    $error = "An error occurred while updating the password.2";
                }
            }
            else{
                $error = "An error occurred while updating the password.3";
            }
        }
        else{
            $error = "An error occurred while updating the password.4";
        }
    }
    else{
        $error = "An error occurred while updating the password.5";
    }

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password</title>
    <link rel="stylesheet" href="../css/vlastne.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/ownDesign.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <script src="registeredWelcome.js"></script>
    <script src="../public/regiForm.js"></script></head>
<body>
<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark ">
        <div class="container">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link text-light" href="registredHome.php">
                        <i class="bi bi-house"></i> Home
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
<section class="vh-100 bg-image" style="background-image: url('../pics/FEI_STU.png');">
    <div class="mask d-flex align-items-center h-100">
        <div class="container h-100">
            <div class="row d-flex justify-content-center align-items-center h-100 flex-column-reverse flex-lg-row">">
                <div class="d-flex flex-column col-12 col-md-8 col-lg-5 col-xl-5">
                    <div class="card" style="border-radius: 15px;">
                        <div class="card-body p-4">
                            <h2 class="text-uppercase text-center mb-5">Change Password</h2>


                            <form method="POST">
                                <div class="form-outline mb-4">
                                    <input type="password" id="current_password" class="form-control"
                                           name="current_password" required/>
                                    <label class="form-label" for="current_password">Current Password</label>
                                </div>

                                <div class="form-outline mb-4">
                                    <input type="password" id="form3Example4cg" class="form-control" name="new_password"
                                           oninput="validatePassword()"/>
                                    <label class="form-label" for="password">New Password</label>
                                    <small class="error" id="errorMessagePassword">Email musi mat @</small>

                                </div>

                                <div class="form-outline mb-4">
                                    <input type="password" id="form3Example4cdg" class="form-control"
                                           name="confirm_password" oninput="validatePasswordWithPasswordAgain()"/>
                                    <label class="form-label" for="passwordAgain">Confirm New Password</label>
                                    <small class="error" id="errorMessagePasswordAgain">Email musi mat @</small>

                                </div>

                                <button type="submit" class="btn btn-warning btn-lg w-100"
                                        onclick="validateFormForPasswordChange(event)">Change Password
                                </button>
                                <?php if (!empty($error) || !empty($success)): ?>
                                    <div class="alert <?= !empty($error) ? 'alert-danger' : 'alert-success' ?>">
                                        <?= !empty($error) ? $error : $success ?>
                                    </div>
                                <?php endif; ?>

                            </form>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-column col-12 col-md-8 col-lg-5 col-xl-5 position-absolute end-0 h-100" style="width: 20%;">
                    <div class="card h-100" style="border-radius: 0;">
                        <div class="card-body p-4">
                            <h2 class="text-uppercase text-center mb-5">Logins</h2>
                            <ul class="list-group">
                                <?php if (!empty($logins)): ?>
                                    <?php foreach ($logins as $login): ?>
                                        <li class="list-group-item">
                                            <strong>Time:</strong> <?= htmlspecialchars($login['login_time']) ?> <br>
                                            <strong>Email:</strong> <?= htmlspecialchars($email) ?>
                                        </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li class="list-group-item">No logins found.</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
</body>
</html>