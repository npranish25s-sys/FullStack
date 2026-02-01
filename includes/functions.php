<?php
function escape($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function sanitize($data) {
    return trim(strip_tags($data));
}

function validateYear($year) {
    return is_numeric($year) && $year >= 1888 && $year <= date('Y') + 5;
}

function validateRating($rating) {
    return is_numeric($rating) && $rating >= 0 && $rating <= 10;
}

function formatRating($rating) {
    return number_format($rating, 1);
}

function isAdmin($conn) {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    $stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    return $user && $user['is_admin'] == 1;
}

function requireAdmin($conn) {
    if (!isAdmin($conn)) {
        header('Location: login.php');
        exit();
    }
}
?>
