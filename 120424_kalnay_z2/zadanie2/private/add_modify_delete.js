document.addEventListener('DOMContentLoaded', function() {
    // Add event listener to the delete button
    const deleteButton = document.getElementById('deleteButton');
    if (deleteButton) {
        // Remove the onclick attribute since we're using addEventListener
        deleteButton.removeAttribute('onclick');
        deleteButton.addEventListener('click', deleteLaureate);
    }
});
let laureateIdToDelete = null;

function handleDelete(id) {
    console.log('Törlés megerősítése, ID:', id);

    if (!id) {
        console.error('Hibás ID:', id);
        return;
    }

    laureateIdToDelete = id;

    // Megmutatjuk a modalt
    document.getElementById('deleteModal').style.display = 'flex';
}

function closeDeleteModal() {
    // Elrejtjük a modalt
    document.getElementById('deleteModal').style.display = 'none';
}



// Add event listener for the confirm delete button in the modal
function deleteLaureate(event) {
    // Prevent the default button behavior if event is provided

    if (event) event.preventDefault();

    // Use the stored ID if available, otherwise use input field
    const laureateId = laureateIdToDelete || document.getElementById('laureateId').value;
    const errorElement = document.getElementById('laureateIdError');
    const statusElement = document.getElementById('deleteStatus');

    // Hide the modal if it's open
    document.getElementById('deleteModal').style.display = 'none';

    // Reset messages if elements exist
    if (errorElement) errorElement.textContent = '';
    if (statusElement) statusElement.innerHTML = '';

    if (!laureateId) {
        if (errorElement) errorElement.textContent = 'Please enter a valid laureate ID';
        return;
    }
    // Correct the API endpoint path
    fetch(`/zadanie2/api/api/v0/laureates/${laureateId}`, {
        method: 'DELETE',
    })
        .then(response => {
            const statusCode = response.status;
            if (!response.ok) {
                return response.json().then(data => {
                    throw { code: statusCode, message: data.message || 'Could not delete laureate' };
                });
            }
            return response.json();
        })
        .then(() => {
            // Add success message in green
            if (errorElement) {
                errorElement.innerHTML = '<span style="color: green">Successfully deleted!</span>';
            } else {
            }

            // Clear the input field if it exists
            if (document.getElementById('laureateId')) {
                document.getElementById('laureateId').value = '';
            }

            // Reset the stored ID
            laureateIdToDelete = null;

            // Refresh data table if available
            if (typeof fetchData === 'function') {
                fetchData();
            }
        })
        .catch(error => {
            const errorCode = error.code || '';
            const errorMessage = error.message || 'Unknown error';

            if (errorElement) {
                errorElement.textContent = `Error code: ${errorCode} - ${errorMessage}`;
            } else {

            }
        });
}

// Add event listener for the confirm delete button in the modal
document.addEventListener('DOMContentLoaded', function() {
    // Your existing code...

    // Set up the confirm button in the delete modal
    const confirmDeleteButton = document.getElementById('confirmDelete');
    if (confirmDeleteButton) {
        confirmDeleteButton.addEventListener('click', deleteLaureate);
    }
});
function handleUpdate(id) {
    // Redirect to modifyMenu.php with the laureate ID as a parameter
    window.location.href = `modifyMenu.php?id=${id}`;
}

function createLaureate(event) {
    event.preventDefault();

    const fullname = document.getElementById('fullname').value || null;
    const organisation = document.getElementById('organisation').value || null; // Fixed - was using fullname
    const birth = document.getElementById('born').value; // Fixed parameter name to match API
    const death = document.getElementById('died').value ; // Fixed parameter name to match API
    const sex = document.getElementById("sex").value;
    const country_name = document.getElementById("country").value; // Fixed parameter name
    const rok = document.getElementById("rok").value ;
    const category = document.getElementById("category").value;
    const language_sk = document.getElementById("language_sk").value || null; // Added .value
    const language_en = document.getElementById("language_en").value || null; // Added .value
    const genre_sk = document.getElementById("genre_sk").value || null; // Added .value
    const genre_en = document.getElementById("genre_en").value || null; // Added .value
    const contrib_sk = document.getElementById("contrib_sk").value || null; // Added .value
    const contrib_en = document.getElementById("contrib_en").value || null; // Added .value
    const errorField = document.getElementById('createLaureateError');

    errorField.innerText = ""; // Clear previous errors

    const currentYear = new Date().getFullYear();
    let errors = [];

    if (birth && (!/^\d{4}$/.test(birth) || birth < 1800 || birth > currentYear)) {
        errors.push("Born year must be between 1800 and " + currentYear);
    }

    if (death && (!/^\d{4}$/.test(death) || death < 1800 || death > currentYear)) {
        errors.push("Died year must be between 1800 and " + currentYear);
    }

    if (birth && death && parseInt(birth) > parseInt(death)) {
        errors.push("Born year cannot be later than Died year");
    }

    if (!/^\d{4}$/.test(rok) || rok < 1900 || rok > currentYear) {
        errors.push("Rok must be a valid year between 1900 and " + currentYear);
    }

    if (errors.length > 0) {
        errorField.innerText = errors.join("\n");
        return;
    }
    const laureateData = {
        fullname: fullname,
        organisation: organisation,
        birth: birth,     // Make sure this matches your API parameter name
        death: death,     // Make sure this matches your API parameter name
        sex: sex,
        country_name: country_name,
        rok: rok,
        category: category,
        language_sk: language_sk,
        language_en: language_en,
        genre_sk: genre_sk,
        genre_en: genre_en,
        contrib_sk: contrib_sk,
        contrib_en: contrib_en
    };

    fetch('/zadanie2/api/api/v0/laureates', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(laureateData)
    })
        .then(response => {
            const statusCode = response.status;
            if (!response.ok) {
                return response.json().then(data => {
                    throw { code: statusCode, message: data.message || 'Could not create laureate' };
                });
            }
            return response.json();
        })
        .then(() => {
            // Success message
            if (errorField) errorField.innerHTML = '<span style="color: green">Successfully created!</span>';
            // Clear the form
            document.getElementById('fullname').value = '';
            document.getElementById('organisation').value = '';
            document.getElementById('born').value = '';
            document.getElementById('died').value = '';
            document.getElementById('sex').value = '';
            document.getElementById('country').value = '';
            document.getElementById('rok').value = '';
            document.getElementById('language_sk').value = '';
            document.getElementById('language_en').value = '';
            document.getElementById('contrib_sk').value = '';
            document.getElementById('contrib_en').value = '';
            document.getElementById('genre_sk').value = '';
            document.getElementById('genre_en').value = '';
            document.getElementById('sex').value = '';
        })
        .catch(error => {
            const errorCode = error.code || '';
            const errorMessage = error.message || 'Unknown error';

            if (errorField) errorField.textContent = `Error code: ${errorCode} - ${errorMessage}`;
        });
}
