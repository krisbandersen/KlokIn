<?php
require_once 'sendValidationEmail.php';
$errors = [];

if (!isset($_POST['name']) || strlen($_POST['name']) > 255 || !preg_match('/^[a-zA-Z- ]+$/', $_POST['name'])) {
    $errors[] = 1;
}
if (!isset($_POST['email']) || strlen($_POST['email']) > 255 || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 2;
} else if (!checkdnsrr(substr($_POST['email'], strpos($_POST['email'], '@') + 1), 'MX')) {
    $errors[] = 3;
}
if (!isset($_POST['password']) || !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[\~?!@#\$%\^&\*])(?=.{8,})/', $_POST['password'])) {
    $errors[] = 4;
} else if (!isset($_POST['confirm-password']) || $_POST['confirm-password'] !== $_POST['password']) {
    $errors[] = 5;
}

if (count($errors) === 0) {
    if (isset($_POST['csrf_token']) && validateToken($_POST['csrf_token'])) {
        //Connect to database
        $C = connect();
        if ($C) {
            //Check if there is an organization with a matching contact_email
            $org_res = sqlSelect($C, 'SELECT id FROM organizations WHERE contact_email=?', 's', $_POST['email']);
            
            if ($org_res && $org_res->num_rows === 1) {
                $organization = $org_res->fetch_assoc();

                // Check if this organization already has a user registered
                $user_res = sqlSelect($C, 'SELECT id FROM users WHERE organization_id=?', 'i', $organization['id']);
                
                if ($user_res && $user_res->num_rows === 0) {
                    // No users found, proceed with registration
                    $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);    
                    $id = sqlInsert($C, 'INSERT INTO users (name, email, password, organization_id) VALUES (?, ?, ?, ?)', 'sssi', $_POST['name'], $_POST['email'], $hash, $organization['id']);
                    
                    if ($id !== -1) {
                        $err = sendValidationEmail($_POST['email']);
                        if ($err === 0) {
                            $errors[] = 0; // Success
                        } else {
                            $errors[] = $err + 9; // Email sending error
                        }
                    } else {
                        //Failed to insert into database
                        $errors[] = 6;
                    }
                } else {
                    // This organization already has a user registered
                    $errors[] = 10; // Organization already registered
                }
            } else {
                // No matching organization found
                $errors[] = 11; // Organization not found
            }
        } else {
            //Failed to connect to database
            $errors[] = 8;
        }
    } else {
        //Invalid CSRF Token
        $errors[] = 9;
    }
}

echo json_encode($errors);
