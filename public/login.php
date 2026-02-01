<?php
session_start();
$pageTitle = 'Login';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

include '../includes/header.php';
$conn = getConnection();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Enter username and password';
    } else {
        $stmt = $conn->prepare("SELECT id, username, password, is_admin FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC); // FIXED: associative array

        if ($user && password_verify($password, $user['password'])) {
            if ($user['is_admin'] == 1) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header('Location: index.php');
                exit();
            } else {
                $error = 'Admin access required';
            }
        } else {
            $error = 'Invalid credentials';
        }
    }
}
?>

<h2>Admin Login</h2>

<?php if ($error): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="POST">
    <label>Username</label>
    <input type="text" name="username" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <button type="submit">Login</button>
</form>

<p>Default: admin / admin123</p>

<?php include '../includes/footer.php'; ?>
