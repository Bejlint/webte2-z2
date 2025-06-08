<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../public/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laureates</title>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../DataTables/datatables.css">
    <link rel="stylesheet" href="../css/vlastne.css">
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <script src="jquery-3.6.0.min.js"></script>
    <script src="../DataTables/datatables.js"></script>
    <script src="add_modify_delete.js"></script>
</head>
<body>
<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark ">
        <div class="container">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link text-light" href="registredHome.php">
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


<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal-overlay" style="display: none;">
    <div class="modal-content p-4 shadow-lg rounded-3">
    <h3>Confirm Delete</h3>
        <p>Are you sure you want to delete this laureate?</p>
        <div class="d-grid gap-2">
            <button class="btn btn-secondary  w-80" onclick="closeDeleteModal()">Cancel</button>
            <button class="btn btn-danger  w-80" onclick="deleteLaureate(event)">Delete</button>
        </div>
    </div>

</div>
<div class="outline">

    <div class="container mt-5 w-50 mx-auto">
        <select id="filterCategory" class="form-select bg-dark text-white border-secondary">
            <option value="">Choose category</option>
            <option value="Technology">Technology</option>
            <option value="Science">Science</option>
        </select>

        <label for="filterCountry">
        </label><select id="filterCountry" class="form-select bg-dark text-white border-secondary">
            <option value="">Choose country</option>
            <option value="USA">USA</option>
            <option value="Germany">Germany</option>
        </select>
    </div>
    <label for="filterCategory"></label>

    <table id="myTable" class="table table-striped table-bordered">
        <thead>
        <tr>
            <th>name</th>
            <th>year</th>
            <th>country</th>
            <th>category</th>
        </tr>
        </thead>
    </table>
</div>

</body>
<!-- Cookie Consent by FreePrivacyPolicy.com https://www.FreePrivacyPolicy.com -->
<script type="text/javascript" src="//www.freeprivacypolicy.com/public/cookie-consent/4.2.0/cookie-consent.js"
        charset="UTF-8"></script>
<script type="text/javascript" charset="UTF-8">
    document.addEventListener('DOMContentLoaded', function () {
        cookieconsent.run({
            "notice_banner_type": "simple",
            "consent_type": "express",
            "palette": "dark",
            "language": "en",
            "page_load_consent_levels": ["strictly-necessary"],
            "notice_banner_reject_button_hide": false,
            "preferences_center_close_button_hide": false,
            "page_refresh_confirmation_buttons": false,
            "website_name": "Nobel Prize"
        });
    });
</script>

<noscript>Cookie Consent by <a href="https://www.freeprivacypolicy.com/">Free Privacy Policy Generator</a></noscript>
<!-- End Cookie Consent by FreePrivacyPolicy.com https://www.FreePrivacyPolicy.com -->
<script>
    async function fetchLaureates() {
        try {
            const response = await fetch('/zadanie2/api/api/v0/laureates');
            const data = await response.json();
            return data;

        } catch (error) {
            console.error('Error fetching laureates:', error);
            return [];
        }
    }

    async function fetchPrizes() {
        try {
            const response = await fetch('/zadanie2/api/api/v0/prizes');
            const data = await response.json();
            return data;

        } catch (error) {
            console.error('Error fetching laureates:', error);
            return [];
        }
    }

    //TODO: ki kell javitani a fetchekben a textContenteket
    async function fetchCountries() {
        try {
            const response = await fetch('/zadanie2/api/api/v0/countries');
            const data = await response.json();
            console.log(data);
            return data;

        } catch (error) {
            console.error('Error fetching laureates:', error);
            return [];

        }

    }

    async function fetchDetails() {
        try {
            const response = await fetch('/zadanie2/api/api/v0/details');
            const data = await response.json();
            return data;

        } catch (error) {
            console.error('Error fetching laureates:', error);
            return [];

        }
    }

    function createDataTable(data) {
        if ($.fn.DataTable.isDataTable('#myTable')) {
            $('#myTable').DataTable().clear().destroy();
        }

        $('#myTable').DataTable({
            data: data,
            columns: [
                {
                    title: "Name",
                    data: "name",
                    render: function(data, type, row) {
                        if (type === 'display') {
                            // Create a clickable link with no special styling
                            return `<a href="afterClick.php?name=${encodeURIComponent(data)}" style="color: inherit; text-decoration: none;">${data}</a>`;
                        }
                        return data;
                    }
                },
                { title: "Year", data: "year" },
                { title: "Country", data: "country" },
                { title: "Category", data: "category" },
                {
                    title: "Actions",
                    data: null,
                    orderable: false,
                    render: function(data, type, row) {

                        return `
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-primary update-btn w-100" data-id="${row.id}">
                <i class="bi bi-pencil"></i> Update
            </button>
            <button class="btn btn-sm btn-danger delete-btn w-100" data-id="${row.id}">
                <i class="bi bi-trash"></i> Delete
            </button>
        </div>

        `;
                    }
                }

            ]
        });

        $('#myTable').on('click', '.update-btn', function() {
            const id = $(this).data('id');
            handleUpdate(id);
        });

        $('#myTable').on('click', '.delete-btn', function() {
            const id = $(this).data('id');
            handleDelete(id);
        });
    }
    function populateFilters(data) {
        const categories = [...new Set(data.map(item => item.category))].filter(Boolean);
        const categoryDropdown = $('#filterCategory');
        categoryDropdown.find('option:not(:first)').remove();
        categories.forEach(category => {
            categoryDropdown.append(`<option value="${category}">${category}</option>`);
        });

        // Get unique countriesprivate
        const countries = [...new Set(data.map(item => item.country))].filter(Boolean);
        const countryDropdown = $('#filterCountry');
        countryDropdown.find('option:not(:first)').remove();
        countries.forEach(country => {
            countryDropdown.append(`<option value="${country}">${country}</option>`);
        });


        $('#nameSearch').on('keyup', function() {
            $('#myTable').DataTable().search(this.value).draw();
        });
        $('#filterCategory, #filterCountry').on('change', function() {
            const categoryFilter = $('#filterCategory').val();
            const countryFilter = $('#filterCountry').val();

            const table = $('#myTable').DataTable();

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                const rowCategory = data[3]; // Category column
                const rowCountry = data[2];  // Country column

                // Apply category filter
                const categoryMatch = !categoryFilter || rowCategory === categoryFilter;

                // Apply country filter
                const countryMatch = !countryFilter || rowCountry === countryFilter;

                return categoryMatch && countryMatch;
            });

            table.draw();

            // Clear the custom filter
            $.fn.dataTable.ext.search.pop();
        });
    }
    async function fetchData() {
        try {
            const response = await fetch('/zadanie2/api/api/v0/laureates/formed');
            const laureatesData = await response.json();

            // Map the data to match the DataTable columns
            const tableData = laureatesData.map(item => {
                return {
                    id: item.id,
                    name: item.fullname,
                    year: item.rok,
                    country: item.country_name,
                    category: item.category
                };
            });

            createDataTable(tableData);

            populateFilters(tableData);

        } catch (error) {
            console.error('Error fetching data:', error);
        }
    }

    $(document).ready(function () {

        fetchData();
    });
</script>


</html>
>