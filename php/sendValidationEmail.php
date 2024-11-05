<?php
require_once 'utils.php';

function sendValidationEmail($email) {
    $C = connect();
    if ($C) {
        $oneDayAgo = time() - 60 * 60 * 24;
        $res = sqlSelect($C, 'SELECT users.id,name,verified,COUNT(requests.id) FROM users LEFT JOIN requests ON users.id = requests.user AND type=0 AND timestamp>? WHERE email=? GROUP BY users.id ', 'is', $oneDayAgo, $email);
        
        if ($res && $res->num_rows === 1) {
            $user = $res->fetch_assoc();
            $res->free_result(); // Free the result set after fetching the data

            if ($user['verified'] === 0) {
                if ($user['COUNT(requests.id)'] <= MAX_EMAIL_VERIFICATION_REQUESTS_PER_DAY) {
                    // Send validation request
                    $verifyCode = bin2hex(random_bytes(32));
                    $hash = password_hash($verifyCode, PASSWORD_DEFAULT); // Hash the hex string
                    $requestID = sqlInsert($C, 'INSERT INTO requests VALUES (NULL, ?, ?, ?, 0)', 'isi', $user['id'], $hash, time());

                    if ($requestID !== -1) {
                        if (sendEmail($email, $user['name'], 'Email Verification', 'Click this <a href="' . VALIDATE_EMAIL_ENDPOINT . '.php?requestID=' . $requestID . '&verifyCode=' . $verifyCode . '">link</a> to verify your email')) {
                            $C->close();
                            return 0;
                        } else {
                            $C->close();
                            return 1;
                        }
                    } else {
                        // Failed to insert request
                        $C->close();
                        return 2;
                    }
                } else {
                    $C->close();
                    return 3;
                }
            } else {
                $C->close();
                return 4;
            }
        } else {
            if ($res) {
                $res->free_result();
            }
            $C->close();
            return 5;
        }
    } else {
        return 6;
    }
    return -1;
}

if (isset($_POST['validateEmail']) && isset($_POST['csrf_token']) && validateToken($_POST['csrf_token'])) {
    echo sendValidationEmail($_POST['validateEmail']);
}
