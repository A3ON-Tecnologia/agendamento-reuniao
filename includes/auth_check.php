<?php
session_start();

function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../auth/login.php');
        exit();
    }
}

function checkRole($required_role) {
    checkAuth();
    if ($_SESSION['user_role'] !== $required_role) {
        header('Location: ../auth/login.php');
        exit();
    }
}

function checkRoles($allowed_roles) {
    checkAuth();
    if (!in_array($_SESSION['user_role'], $allowed_roles)) {
        header('Location: ../auth/login.php');
        exit();
    }
}

function getUserInfo() {
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'role' => $_SESSION['user_role']
    ];
}
?>
