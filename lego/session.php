<?php
// Sessie

// Start sessie
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1); // Geen JavaScript toegang
    ini_set('session.use_only_cookies', 1); // Alleen cookies
    ini_set('session.cookie_secure', 0); // Zet op 1 voor HTTPS
    
    session_start();
}

// Login
function loginUser($user) {
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_naam'] = $user['naam'];
    $_SESSION['user_achternaam'] = $user['achternaam'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();
    
    // winkelwagen
    if (!isset($_SESSION['winkelwagen'])) {
        $_SESSION['winkelwagen'] = [];
    }
}

// Ingelogd?
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// Uitloggen
function logoutUser() {
    $_SESSION = array();
    
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    session_destroy();
    session_start();
}

// Huidige gebruiker
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'naam' => $_SESSION['user_naam'],
        'achternaam' => $_SESSION['user_achternaam'],
        'email' => $_SESSION['user_email']
    ];
}

// Vereist login
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: login.php');
        exit();
    }
}

// Toevoegen aan winkelwagen
function addToCart($productId, $aantal = 1) {
    if (!isset($_SESSION['winkelwagen'])) {
        $_SESSION['winkelwagen'] = [];
    }
    
    if (isset($_SESSION['winkelwagen'][$productId])) {
        $_SESSION['winkelwagen'][$productId] += $aantal;
    } else {
        $_SESSION['winkelwagen'][$productId] = $aantal;
    }
}

// Haal winkelwagen
function getCart() {
    return isset($_SESSION['winkelwagen']) ? $_SESSION['winkelwagen'] : [];
}

// Leeg wagen
function clearCart() {
    $_SESSION['winkelwagen'] = [];
}

// Tel items
function getCartItemCount() {
    $cart = getCart();
    return array_sum($cart);
}

// xss preventie
function sanitizeOutput($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>