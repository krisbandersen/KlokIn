<?php
require_once 'utils.php'; // Assuming your utility functions are in utils.php

if (isset($_GET['requestID']) && isset($_GET['verifyCode'])) {
    $requestID = intval($_GET['requestID']);
    $verifyCode = $_GET['verifyCode'];

    $C = connect();
    if ($C) {
        // Retrieve the request from the database using the requestID
        $res = sqlSelect($C, 'SELECT requests.user, requests.hash, users.verified FROM requests JOIN users ON requests.user = users.id WHERE requests.id = ? AND type = 0', 'i', $requestID);

        if ($res && $res->num_rows === 1) {
            $request = $res->fetch_assoc();

            // Check if the user is already verified
            if ($request['verified'] == 1) {
                echo 'This email has already been verified.';
            } else {
                // Verify the provided code with the hash in the database
                if (password_verify($verifyCode, $request['hash'])) {
                    // If verified, update the user to mark them as verified
                    if (sqlUpdate($C, 'UPDATE users SET verified = 1 WHERE id = ?', 'i', $request['user'])) {
                        // Delete the verification request to prevent reuse
                        sqlUpdate($C, 'DELETE FROM requests WHERE id = ?', 'i', $requestID);

                        // Redirect to login.php after successful verification
                        header("Location: /login.php");
                        exit();  // Make sure to stop further execution
                    } else {
                        echo 'Failed to update user verification status.';
                    }
                } else {
                    echo 'Invalid verification code.';
                }
            }
        } else {
            echo 'Invalid request ID.';
        }

        $res->free_result();
        $C->close();
    } else {
        echo 'Database connection failed.';
    }
} else {
    echo 'Invalid request. Please use the verification link from the email.';
}
?>
