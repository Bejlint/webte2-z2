<?php
session_start();

require_once '../config.php';


$db2 = connectToDatabase($servername, $username, $password, $login_db);

if (!isset($_GET['name']) || empty($_GET['name'])) {
    die("No name provided!");
}

$name = $_GET['name'];



$stmt->bind_param("ss", $name,$name);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("No data found for the specified name.");
}



$data = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/ownDesign.css">
    <link rel="stylesheet" href="css/vlastne.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <script src="private/registeredWelcome.js"></script>
    <title>Title</title>
</head>
<body>
<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark ">
        <div class="container">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link text-light" href="../../120424_kalnay_z2/zadanie2/index.php">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-light" href="public/login.php">
                        <i class="bi bi-lock"></i> Login
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-light" href="public/registration.php">
                        <i class="bi bi-gear"></i> Register
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</header>
<div>
    <!--credit:https://mdbootstrap.com/docs/standard/extended/registration/-->
    <section class="vh-100 bg-image" style="background-image: url('pics/FEI_STU.png');">
        <div class="mask d-flex align-items-center h-100 ">
            <div class="container h-100">
                <div class="row d-flex justify-content-center align-items-center h-100">
                    <div class="col-12 col-md-8 col-lg-5 col-xl-5">
                        <div class="card" style="border-radius: 15px;">
                            <div class="card-body p-4">
                                <div class="d-flex flex-column align-items-left">
                                    <h1>Selected Record Details</h1>
                                    <p><strong>Organisation:</strong> <?php echo $data['organisation'] ?? $data['fullname']; ?></p>
                                    <p><strong>Birth:</strong> <?php echo $data['birth']?></p>
                                    <p><strong>Death:</strong> <?php echo $data['death']?></p>
                                    <p><strong>Year:</strong> <?php echo $data['rok']; ?></p>
                                    <p><strong>Country:</strong> <?php echo $data['countries']; ?></p>
                                    <p><strong>Category:</strong> <?php echo $data['category']; ?></p>
                                    <p><strong>Contribution SK:</strong> <?php echo $data['contirb_sk']; ?></p>
                                    <p><strong>Contribution EN:</strong> <?php echo $data['contrb_en']; ?></p>
                                    <p><strong>Language SK:</strong> <?php echo $data['language_sk']; ?></p>
                                    <p><strong>Genre SK:</strong> <?php echo $data['genre_sk']; ?></p>

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
