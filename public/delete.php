<?php
session_start();
$pageTitle = 'Delete Movie';
include '../includes/header.php';

$conn = getConnection();
requireAdmin($conn);

$movieId = $_GET['id'] ?? 0;
if (!$movieId) {
    header('Location: index.php');
    exit();
}

$stmt = $conn->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->execute([$movieId]);
$movie = $stmt->fetch();

if (!$movie) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();
        $conn->prepare("DELETE FROM movie_genres WHERE movie_id = ?")->execute([$movieId]);
        $conn->prepare("DELETE FROM movie_cast WHERE movie_id = ?")->execute([$movieId]);
        $conn->prepare("DELETE FROM movies WHERE id = ?")->execute([$movieId]);
        $conn->commit();
        header('Location: index.php');
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
        $error = 'Error: ' . $e->getMessage();
    }
}
?>

<h2>Delete Movie</h2>

<p>Are you sure you want to delete this movie?</p>

<div class="movie-card">
    <h3><?php echo escape($movie['title']); ?> (<?php echo escape($movie['year']); ?>)</h3>
    <p>Rating: <?php echo formatRating($movie['rating']); ?>/10</p>
    <?php if ($movie['description']): ?>
        <p><?php echo escape($movie['description']); ?></p>
    <?php endif; ?>
</div>

<form method="POST">
    <button type="submit">Yes, Delete</button>
    <a href="index.php">Cancel</a>
</form>

<?php include '../includes/footer.php'; ?>
