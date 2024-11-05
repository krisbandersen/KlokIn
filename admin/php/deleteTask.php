<?php
require_once '../../php/utils.php';

session_start();
$errors = [];

// Validate POST data
if (!isset($_POST['id'])) {
    $errors[] = 1; // no id
}

// Ensure no errors so far
if (count($errors) === 0) {
    if (isset($_POST['csrf_token']) && validateToken($_POST['csrf_token'])) {
        // Connect to the database
        $C = connect();
        if ($C) {
            // Retrieve the organization ID from the logged-in user
            if (isset($_SESSION['organization_id'])) {
                $org_id = $_SESSION['organization_id']; // Get the org ID from session

                // Check if an organization exists with the provided ID
                $org_res = sqlSelect($C, 'SELECT id, label_name FROM organizations WHERE id=?', 'i', $org_id);
                
                $results = $org_res->fetch_assoc();


                if ($org_res && $org_res->num_rows === 1) {     
                    $taskId = $_POST['id'];
                    $delete_res = sqlDelete($C, 'DELETE FROM tasks WHERE id = ? AND organization_id = ?', 'ii', $taskId, $org_id);

                    if ($delete_res) {
                        $errors[] = 0; // Success
                    } else {
                        $errors[] = 10; // Deletion failed
                    }
                } else {
                    $errors[] = 7; // Organization not found
                }
            }
        } else {
            // Database connection failed
            $errors[] = 6; // Database connection error
        }
    } else {
        // Invalid CSRF Token
        $errors[] = 9; // Invalid token
    }
}

// Output the result as JSON
echo json_encode($errors);
