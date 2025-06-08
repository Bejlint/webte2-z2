<?php
// Ensure this file is included and not accessed directly
/*if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    header("Location: ../index.php");
    exit();
}*/

// Check if an ID was provided in the URL
$laureateId = isset($_GET['id']) ? $_GET['id'] : null;

if (!$laureateId) {
    echo "<div class='alert alert-danger'>No laureate ID specified.</div>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modify Laureate</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/vlastne.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>
<body>
<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark ">
        <div class="container">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link text-light" href="laureates.php">
                        <i class="bi bi-arrow-left"></i> Back
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
<section class="vh-100 bg-image" ">
    <div class="mask d-flex align-items-center h-100">
        <div class="container h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col-12 col-md-10 col-lg-8 col-xl-8">
                    <div class="card" style="border-radius: 15px;">
                        <div class="card-body p-4">
                            <h2 class="mb-4">Modify Laureate</h2>
                            <div id="errorMessage" class="alert alert-danger d-none"></div>
                            <div id="successMessage" class="alert alert-success d-none"></div>

                            <form id="modifyLaureateForm">
                                <input type="hidden" id="laureateId" value="<?php echo htmlspecialchars($laureateId); ?>">

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="fullname" class="form-label">Name</label>
                                            <input type="text" class="form-control" id="fullname" >
                                        </div>

                                        <div class="mb-3">
                                            <label for="organisation" class="form-label">Organisation</label>
                                            <input type="text" class="form-control" id="organisation">
                                        </div>

                                        <div class="mb-3">
                                            <label for="born" class="form-label">Birth Date</label>
                                            <input type="text" class="form-control" id="born">
                                        </div>

                                        <div class="mb-3">
                                            <label for="died" class="form-label">Death Date</label>
                                            <input type="text" class="form-control" id="died">
                                        </div>

                                        <div class="mb-3">
                                            <label for="sex" class="form-label">Sex</label>
                                            <select class="form-control" id="sex">
                                                <option value="M">Male</option>
                                                <option value="F">Female</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="country" class="form-label">Country</label>
                                            <input type="text" class="form-control" id="country">
                                        </div>

                                        <div class="mb-3">
                                            <label for="rok" class="form-label">Year</label>
                                            <input type="number" class="form-control" id="rok" >
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="category" class="form-label">Category</label>
                                            <input type="text" class="form-control" id="category" >
                                        </div>

                                        <div class="mb-3">
                                            <label for="language_sk" class="form-label">Language (Slovak)</label>
                                            <input type="text" class="form-control" id="language_sk">
                                        </div>

                                        <div class="mb-3">
                                            <label for="language_en" class="form-label">Language (English)</label>
                                            <input type="text" class="form-control" id="language_en">
                                        </div>

                                        <div class="mb-3">
                                            <label for="genre_sk" class="form-label">Genre (Slovak)</label>
                                            <input type="text" class="form-control" id="genre_sk">
                                        </div>

                                        <div class="mb-3">
                                            <label for="genre_en" class="form-label">Genre (English)</label>
                                            <input type="text" class="form-control" id="genre_en">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="contrib_sk" class="form-label">Contribution (Slovak)</label>
                                            <textarea class="form-control" id="contrib_sk" rows="3"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="contrib_en" class="form-label">Contribution (English)</label>
                                            <textarea class="form-control" id="contrib_en" rows="3"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between mt-3">
                                    <button type="submit" class="btn btn-primary">Update Laureate</button>
                                    <a href="laureates.php" class="btn btn-secondary">Cancel</a>
                                    <small id="createLaureateError" class="text-danger"></small>

                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
</body>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const laureateId = document.getElementById('laureateId').value;
        const form = document.getElementById('modifyLaureateForm');
        const errorMessage = document.getElementById('errorMessage');
        const successMessage = document.getElementById('successMessage');

        // Fetch laureate data and populate the form
        fetchLaureateData(laureateId);

        // Handle form submission
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            updateLaureate(laureateId);
        });

        function fetchLaureateData(id) {
            fetch(`../api/api/v0/laureates/modify/${id}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.laureate) {
                        populateForm(data.laureate);
                    } else {
                        showError(data.message || 'Failed to fetch laureate data');
                    }
                })
                .catch(error => {
                    showError('Error fetching laureate: ' + error.message);
                });
        }

        function populateForm(laureate) {
            document.getElementById('fullname').value = laureate.fullname || '';
            document.getElementById('organisation').value = laureate.organisation || '';
            document.getElementById('born').value = laureate.birth || '';
            document.getElementById('died').value = laureate.death || '';
            document.getElementById('sex').value = laureate.sex || 'M';
            document.getElementById('country').value = laureate.country || '';
            document.getElementById('rok').value = laureate.rok || '';
            document.getElementById('category').value = laureate.category || '';
            document.getElementById('language_sk').value = laureate.language_sk || '';
            document.getElementById('language_en').value = laureate.language_en || '';
            document.getElementById('genre_sk').value = laureate.genre_sk || '';
            document.getElementById('genre_en').value = laureate.genre_en || '';
            document.getElementById('contrib_sk').value = laureate.contrib_sk || '';
            document.getElementById('contrib_en').value = laureate.contrib_en || '';
        }

        function updateLaureate(id) {
            const laureateData = {
                id: id,
                fullname: document.getElementById('fullname').value,
                organisation: document.getElementById('organisation').value,
                birth: document.getElementById('born').value,
                death: document.getElementById('died').value,
                sex: document.getElementById('sex').value,
                country: document.getElementById('country').value,
                rok: document.getElementById('rok').value,
                category: document.getElementById('category').value,
                language_sk: document.getElementById('language_sk').value,
                language_en: document.getElementById('language_en').value,
                genre_sk: document.getElementById('genre_sk').value,
                genre_en: document.getElementById('genre_en').value,
                contrib_sk: document.getElementById('contrib_sk').value,
                contrib_en: document.getElementById('contrib_en').value
            };

            const errorField = document.getElementById('createLaureateError');

            errorField.innerText = ""; // Clear previous errors

            const currentYear = new Date().getFullYear();
            let errors = [];


            if (!laureateData.birth || laureateData.birth.trim() === '') {
                errors.push("Birth year is required");
            }

            if (!laureateData.rok || laureateData.rok.trim() === '') {
                errors.push("Award year (Rok) is required");
            }
            if (laureateData.birth && (!/^\d{4}$/.test(laureateData.birth) || laureateData.birth < 1800 || laureateData.birth > currentYear) ) {
                errors.push("Born year must be between 1800 and " + currentYear);
            }

            if (laureateData.death && (!/^\d{4}$/.test(laureateData.death) || laureateData.death < 1800 || laureateData.death > currentYear)) {
                errors.push("Died year must be between 1800 and " + currentYear);
            }

            if (laureateData.death && laureateData.death && parseInt(laureateData.birth) > parseInt(laureateData.death)) {
                errors.push("Born year cannot be later than Died year");
            }

            if (!/^\d{4}$/.test(laureateData.rok) || laureateData.rok < 1900 || laureateData.rok > currentYear) {
                errors.push("Year must be a valid year between 1900 and " + currentYear);
            }

            if (errors.length > 0) {
                showError(errors.join("\n")) ;
                return;
            }
            fetch(`../api/api/v0/laureates/modify/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(laureateData)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccess('Laureate updated successfully');
                    } else {
                        showError(data.message || 'Failed to update laureate');
                    }
                })
                .catch(error => {
                    showError('Error updating laureate: ' + error.message);
                });
        }

        function showError(message) {
            errorMessage.textContent = message;
            errorMessage.classList.remove('d-none');
            successMessage.classList.add('d-none');

            // Hide error message after 5 seconds
            setTimeout(() => {
                errorMessage.classList.add('d-none');
            }, 5000);
        }

        function showSuccess(message) {
            successMessage.textContent = message;
            successMessage.classList.remove('d-none');
            errorMessage.classList.add('d-none');

            // Hide success message after 5 seconds
            setTimeout(() => {
                successMessage.classList.add('d-none');
            }, 5000);
        }
    });
</script>
</html>
