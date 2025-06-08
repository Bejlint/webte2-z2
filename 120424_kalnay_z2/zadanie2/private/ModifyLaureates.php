<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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
    <script src="add_modify_delete.js"></script>
    <title>Title</title>
</head>
<body>
<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark ">
        <div class="container">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link text-light" href="#">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </li>
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
                                <h2 class="text-uppercase text-center mb-5" id="title">Add/modify/delete</h2>
                                <div class="row">
                                    <button id="showFieldForMultipleLaureates" class="btn btn-warning btn-block mb-4">
                                        <i class="bi bi-plus-circle"></i> Add multiple Laureates
                                    </button>


                                    <div id="multipleLaureatesInput" style="display: none;">
                                        <label for="jsonInput" class="form-label">Paste JSON or upload file</label>

                                        <!-- Szöveges beírás -->
                                        <textarea id="jsonInput" class="form-control mb-2" rows="6"
                                                  placeholder='[{"name": "John Doe", "category": "Peace"}, ...]'></textarea>

                                        <!-- Fájl feltöltés -->
                                        <input type="file" id="jsonFile" accept=".json" class="form-control mb-3"/>

                                        <!-- Beküldő gomb -->
                                        <button class="btn btn-success" onclick="submitMultipleLaureates()">Submit
                                        </button>

                                        <small id="jsonError" class="text-danger mt-2 d-block"></small>
                                    </div>
                                    <button id="showCreateFormButton" class="btn btn-primary btn-block mb-2">
                                        <i class="bi bi-plus-circle"></i> Create Laureate
                                    </button>
                                    <div id="createLaureateForm" style="display: none;">
                                        <div class="row">
                                            <!-- Left Column -->
                                            <div class="col-md-6">
                                                <div class="form-outline mb-3">
                                                    <label class="form-label" for="fullname">Fullname</label>
                                                    <input type="text" id="fullname" class="form-control"
                                                           placeholder="Fullname"/>
                                                </div>
                                                <div class="form-outline mb-3">
                                                    <label class="form-label" for="organisation">Organisation</label>
                                                    <input type="text" id="organisation" class="form-control"
                                                           placeholder="Organisation"/>
                                                </div>
                                                <div class="form-outline mb-3">
                                                    <label for="sex" class="form-label">Sex</label>
                                                    <select class="form-select" id="sex" name="sex">
                                                        <option value="M">Male</option>
                                                        <option value="F">Female</option>
                                                    </select>
                                                </div>
                                                <div class="form-outline mb-3">
                                                    <label class="form-label" for="born">Born</label>
                                                    <input type="text" id="born" class="form-control"
                                                           placeholder="1900"/>
                                                </div>
                                                <div class="form-outline mb-3">
                                                    <label class="form-label" for="died">Died</label>
                                                    <input type="text" id="died" class="form-control"
                                                           placeholder="1900"/>
                                                </div>
                                                <div class="form-outline mb-3">
                                                    <label class="form-label" for="country">Country</label>
                                                    <input type="text" id="country" class="form-control"
                                                           placeholder="Slovakia"/>
                                                </div>
                                                <div class="form-outline mb-3">
                                                    <label class="form-label" for="rok">Rok</label>
                                                    <input type="text" id="rok" class="form-control" placeholder="rok"/>
                                                </div>
                                            </div>

                                            <!-- Right Column -->
                                            <div class="col-md-6">
                                                <div class="form-outline mb-3">
                                                    <label class="form-label" for="category">Category</label>
                                                    <input type="text" id="category" class="form-control"
                                                           placeholder="Mier"/>
                                                </div>
                                                <div class="form-outline mb-3">
                                                    <label class="form-label" for="language_sk">Language SK</label>
                                                    <input type="text" id="language_sk" class="form-control"
                                                           placeholder="Slovensky"/>
                                                </div>
                                                <div class="form-outline mb-3">
                                                    <label class="form-label" for="language_en">Language EN</label>
                                                    <input type="text" id="language_en" class="form-control"
                                                           placeholder="Slovak"/>
                                                </div>
                                                <div class="form-outline mb-3">
                                                    <label class="form-label" for="genre_sk">Genre SK</label>
                                                    <input type="text" id="genre_sk" class="form-control"
                                                           placeholder="roman"/>
                                                </div>
                                                <div class="form-outline mb-3">
                                                    <label class="form-label" for="genre_en">Genre EN</label>
                                                    <input type="text" id="genre_en" class="form-control"
                                                           placeholder="idk"/>
                                                </div>
                                                <div class="form-outline mb-3">
                                                    <label class="form-label" for="contrib_sk">Contribution SK</label>
                                                    <textarea rows="2" id="contrib_sk" class="form-control"
                                                              placeholder="..."></textarea>
                                                </div>
                                                <div class="form-outline mb-3">
                                                    <label class="form-label" for="contrib_en">Contribution EN</label>
                                                    <textarea rows="2" id="contrib_en" class="form-control"
                                                              placeholder="..."></textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-3">
                                            <div class="col-12">
                                                <button id="createButton" class="btn btn-success btn-block"
                                                        onclick="createLaureate(event)">
                                                    <i class="bi bi-plus-circle"></i> Create Laureate
                                                </button>
                                                <small id="createLaureateError" class="text-danger"></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </section>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const showCreateFormButton = document.getElementById("showCreateFormButton");
        const createLaureateForm = document.getElementById("createLaureateForm");

        showCreateFormButton.addEventListener("click", function () {
            createLaureateForm.style.display = createLaureateForm.style.display === "none" ? "block" : "none";
        });
    });
    document.addEventListener("DOMContentLoaded", () => {
        const showFieldBtn = document.getElementById("showFieldForMultipleLaureates");
        const inputDiv = document.getElementById("multipleLaureatesInput");
        const fileInput = document.getElementById("jsonFile");
        const textArea = document.getElementById("jsonInput");
        const error = document.getElementById("jsonError");

        showFieldBtn.addEventListener("click", () => {
            inputDiv.style.display = inputDiv.style.display === "none" ? "block" : "none";
        });

        fileInput.addEventListener("change", function () {
            const file = fileInput.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function (e) {
                try {
                    const json = JSON.parse(e.target.result);
                    textArea.value = JSON.stringify(json, null, 2);
                    error.textContent = '';
                } catch (err) {
                    error.textContent = "Invalid JSON in uploaded file.";
                }
            };
            reader.readAsText(file);
        });
    });

    function submitMultipleLaureates() {
        const jsonInput = document.getElementById("jsonInput").value;
        const error = document.getElementById("jsonError");

        try {
            const data = JSON.parse(jsonInput);
            console.log("Parsed JSON array:", data);
            error.textContent = '';

            // Create a status message element if it doesn't exist
            let statusMsg = document.getElementById('jsonStatusMessage');
            if (!statusMsg) {
                statusMsg = document.createElement('div');
                statusMsg.id = 'jsonStatusMessage';
                statusMsg.className = 'mt-2';
                document.getElementById('multipleLaureatesInput').appendChild(statusMsg);
            }

            // Show loading state
            statusMsg.className = 'mt-2 text-info';
            statusMsg.textContent = 'Processing...';

            fetch('/zadanie2/api/api/v0/laureates/more', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => { throw err; });
                    }
                    return response.json();
                })
                .then(result => {
                    console.log("Success:", result);
                    // Show success message
                    statusMsg.className = 'mt-2 text-success';
                    statusMsg.textContent = 'Laureates updated successfully!';

                    // Clear the form after successful upload
                    document.getElementById("jsonInput").value = '';
                    document.getElementById("jsonFile").value = '';
                })
                .catch(err => {
                    console.error("Chyba", err);
                    error.textContent = "Chyba: " + (err.message || JSON.stringify(err));
                    statusMsg.className = 'mt-2 text-danger';
                    statusMsg.textContent = 'Error occurred during update.';
                });

        } catch (e) {
            error.textContent = "Invalid JSON format.";
        }
    }



</script>

</body>
</html>
