<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getUserRole() {
    return $_SESSION['role'] ?? null;
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    return dbFetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireRole($role) {
    requireLogin();
    if (getUserRole() !== $role) {
        switch (getUserRole()) {
            case 'clerk':
                header('Location: clerk/index.php');
                break;
            case 'doctor':
                header('Location: doctor/index.php');
                break;
            case 'patient':
                header('Location: patient/index.php');
                break;
            default:
                header('Location: login.php');
        }
        exit;
    }
}

function login($user, $redirect = true) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role'] = $user['role'];

    if ($redirect) {
        switch ($user['role']) {
            case 'clerk':
                header('Location: clerk/index.php');
                break;
            case 'doctor':
                header('Location: doctor/index.php');
                break;
            case 'patient':
                header('Location: patient/index.php');
                break;
        }
        exit;
    }
}

function logout() {
    session_destroy();
    header('Location: index.php');
    exit;
}

function registerUser($username, $email, $password, $fullName, $role = 'patient', $phone = '', $specialization = '') {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    return dbInsert('users', [
        'username' => $username,
        'email' => $email,
        'password' => $hash,
        'full_name' => $fullName,
        'role' => $role,
        'phone' => $phone,
        'specialization' => $specialization
    ]);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function userExists($email) {
    return dbFetch("SELECT id FROM users WHERE email = ? OR username = ?", [$email, $email]) !== null;
}