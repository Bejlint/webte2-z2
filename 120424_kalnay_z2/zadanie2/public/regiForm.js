function controlOfZavinac(event) {
    let email = document.getElementById("form3Example3cg").value;
    let errorName = document.getElementById('errorMessageEmail');

    let hasError = false;
    errorName.style.display = 'none';

    if (email.length === 0) {
        errorName.textContent = "Email je povinne";
        errorName.style.display = 'block';
        hasError = true;
    } else {
        if (!email.includes('@')) {
            errorName.textContent = "Email musí obsahovať zavináč '@'";
            errorName.style.display = 'block';
            hasError = true;
        } else {
            let atPosition = email.indexOf('@');
            if (atPosition < 3) {
                errorName.textContent = "Email musi obsahovat aspon tri znaky pred zavinacom";
                errorName.style.display = 'block';
                hasError = true;
            }

            let domain = email.split('.').pop();
            if (domain.length < 2 || domain.length > 4) {
                errorName.textContent = "Domena musi obsahovat aspon 2, maximum 4 znakov napr. .sk, .com...";
                errorName.style.display = 'block';
                hasError = true;
            }
        }
    }

    return !hasError;
}

function validateName(event) {
    let meno = document.getElementById('form3Example1cg');
    let errorName = document.getElementById('errorMessageName');
    const letterPattern = /^[A-Za-zÁÉÍÓÚáéíóúýŽžŠšČčŘřŇňĽľŤťĆćŐőŰűÜüÖöÄäÕõ ]+$/;
    const maxLength = 20;

    let hasError = false;
    errorName.style.display = 'none';

    if (meno.value.length === 0) {
        errorName.textContent = 'Meno je povinne';
        errorName.style.display = 'block';
        hasError = true;
    } else if (meno.value.length > maxLength || !letterPattern.test(meno.value)) {
        errorName.textContent = meno.value.length > maxLength ? 'Meno nesmie presiahnut 20 znakov' : 'Meno moze obsahovat iba pismena';
        errorName.style.display = 'block';
        hasError = true;
    }

    return !hasError;
}

function validatePassword(event) {
    let password = document.getElementById("form3Example4cg").value;
    let errorPassword = document.getElementById("errorMessagePassword");

    errorPassword.style.display = 'none';
    let hasError = false;

    if (password.length < 12) {
        errorPassword.textContent = "Password must be at least 12 characters long";
        errorPassword.style.display = 'block';
        hasError = true;
    } else if (!/[A-Z]/.test(password)) {
        errorPassword.textContent = "Password must contain at least one uppercase letter";
        errorPassword.style.display = 'block';
        hasError = true;
    } else if (!/[0-9]/.test(password)) {
        errorPassword.textContent = "Password must contain at least one number";
        errorPassword.style.display = 'block';
        hasError = true;
    }
    return !hasError;
}

function validatePasswordWithPasswordAgain(event){
    let password = document.getElementById('form3Example4cg').value;
    let passwordAgain = document.getElementById('form3Example4cdg').value;
    let errorPassword = document.getElementById('errorMessagePasswordAgain');
    errorPassword.style.display = 'none';
    let hasError = false;
    if(password !== passwordAgain){
        errorPassword.textContent = "Passwords do not match. Please try again.";
        errorPassword.style.display = 'block';
        hasError = true;
    }
    console.log("validatePasswordWithPasswordAgain called, hasError:", hasError);
    return !hasError;


}

function validateForm(event) {
    const isNameValid = validateName(event);
    const isEmailValid = controlOfZavinac(event);
    const isPasswordValid = validatePassword(event);
    const isPassEqual = validatePasswordWithPasswordAgain(event);
    console.log("validateForm called, isNameValid:", isNameValid, "isEmailValid:", isEmailValid, "isPasswordValid:", isPasswordValid, "isPassEqual:", isPassEqual);
    if (!isNameValid || !isEmailValid || !isPasswordValid ||!isPassEqual) {
        event.preventDefault();
        return false;
    }

    return true;
}
function validateFormForPasswordChange(event){
    const isPasswordValid = validatePassword(event);
    const isPassEqual = validatePasswordWithPasswordAgain(event);
    if (!isPasswordValid ||!isPassEqual) {
        event.preventDefault();
        return false;
    }

    return true;

}
