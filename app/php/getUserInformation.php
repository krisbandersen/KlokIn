<?php
require_once '../../php/utils.php';
session_start();

if (isset($_POST['csrf_token']) && validateToken($_POST['csrf_token'])) {
    echo json_encode([
        'firstname' => $_SESSION['firstname'],
        'lastname' => $_SESSION['lastname'],
        'email' => $_SESSION['email'],
        'organization_label_name' => $_SESSION['org_label_name'],
        'organization_cvr' => $_SESSION['org_cvr']
    ]);
} else {
    // Invalid CSRF token or missing parameters
    echo json_encode(['error' => 'Invalid request']);
}