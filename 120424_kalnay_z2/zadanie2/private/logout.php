<?php
session_start();
$_SESSION["loggedin"] = false;
$_SESSION = array();

header("location: ../index.php");
session_unset();
session_destroy();

echo "<script>
    localStorage.removeItem('loggedInUser');  
    sessionStorage.removeItem('loggedInUser'); 
    window.location.reload();  
</script>";

exit();
?>
