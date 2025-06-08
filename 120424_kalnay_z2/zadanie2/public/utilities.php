<?php

function isEmpty($field) {

    if (empty(trim($field))) {
        return true;
    }
    return false;
}

function userExist($db2, $email)
{
    $stmt = $db2->prepare("SELECT id FROM login_input WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $stmt->store_result();

    $exist = $stmt->num_rows > 0;

    $stmt->close();
    return $exist;
}
