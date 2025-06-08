<?php

session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
} else {
    header("Location: private/registredHome.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/ownDesign.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="DataTables/datatables.css">
    <link rel="stylesheet" href="css/vlastne.css">
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <script src="private/jquery-3.6.0.min.js"></script>
    <script src="DataTables/datatables.js"></script>
    <title>Title</title>
</head>
<body>
<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark ">
        <div class="container">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link text-light" href="public/login.php">
                        <i class="bi bi-arrow-in-right"></i> Login
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-light" href="public/registration.php">
                        <i class="bi bi-person-plus"></i> Register
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</header>
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
                { title: "Category", data: "category" }
            ]
        });
    }
    function populateFilters(data) {
        const categories = [...new Set(data.map(item => item.category))].filter(Boolean);
        const categoryDropdown = $('#filterCategory');
        categoryDropdown.find('option:not(:first)').remove();
        categories.forEach(category => {
            categoryDropdown.append(`<option value="${category}">${category}</option>`);
        });

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
                const rowCategory = data[3];
                const rowCountry = data[2];

                const categoryMatch = !categoryFilter || rowCategory === categoryFilter;

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

            const tableData = laureatesData.map(item => {
                return {
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
