<?php
session_start();
$pageTitle = 'Add Movie';
include '../includes/header.php';

$conn = getConnection();
requireAdmin($conn);

$errors = [];
$success = '';

$genresStmt = $conn->prepare("SELECT * FROM genres ORDER BY name");
$genresStmt->execute();
$allGenres = $genresStmt->fetchAll();

$castStmt = $conn->prepare("SELECT * FROM cast ORDER BY name");
$castStmt->execute();
$allCast = $castStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $year = sanitize($_POST['year'] ?? '');
    $rating = sanitize($_POST['rating'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $genres = $_POST['genres'] ?? [];
    $cast = $_POST['cast'] ?? [];
    
    if (empty($title)) $errors[] = 'Title required';
    if (empty($year) || !validateYear($year)) $errors[] = 'Valid year required';
    if (empty($rating) || !validateRating($rating)) $errors[] = 'Valid rating required';
    
    if (empty($errors)) {
        try {
            $conn->beginTransaction();
            
            $stmt = $conn->prepare("INSERT INTO movies (title, year, rating, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([$title, $year, $rating, $description]);
            $movieId = $conn->lastInsertId();
            
            if (!empty($genres)) {
                $genreStmt = $conn->prepare("INSERT INTO movie_genres (movie_id, genre_id) VALUES (?, ?)");
                foreach ($genres as $genreId) {
                    $genreStmt->execute([$movieId, $genreId]);
                }
            }
            
            if (!empty($cast)) {
                $castStmt = $conn->prepare("INSERT INTO movie_cast (movie_id, cast_id) VALUES (?, ?)");
                foreach ($cast as $castId) {
                    $castStmt->execute([$movieId, $castId]);
                }
            }
            
            $conn->commit();
            $success = 'Movie added successfully!';
            $title = $year = $rating = $description = '';
            $genres = $cast = [];
        } catch (Exception $e) {
            $conn->rollBack();
            $errors[] = 'Error: ' . $e->getMessage();
        }
    }
}
?>

<h2>Add New Movie</h2>

<?php if (!empty($errors)): ?>
    <div class="error">
        <?php foreach ($errors as $error): ?>
            <p><?php echo escape($error); ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="success"><?php echo escape($success); ?></div>
<?php endif; ?>

<form method="POST">
    <label>Title *</label>
    <input type="text" name="title" value="<?php echo escape($title ?? ''); ?>" required>
    
    <label>Year *</label>
    <input type="number" name="year" value="<?php echo escape($year ?? ''); ?>" required>
    
    <label>Rating (0-10) *</label>
    <input type="number" name="rating" step="0.1" min="0" max="10" value="<?php echo escape($rating ?? ''); ?>" required>
    
    <label>Description</label>
    <textarea name="description"><?php echo escape($description ?? ''); ?></textarea>
    
    <label>Genres</label>
    <div class="checkboxes">
        <?php foreach ($allGenres as $genre): ?>
            <label><input type="checkbox" name="genres[]" value="<?php echo $genre['id']; ?>"><?php echo escape($genre['name']); ?></label>
        <?php endforeach; ?>
    </div>
    
    <label>Cast</label>
    <div class="checkboxes">
        <?php foreach ($allCast as $actor): ?>
            <label><input type="checkbox" name="cast[]" value="<?php echo $actor['id']; ?>"><?php echo escape($actor['name']); ?></label>
        <?php endforeach; ?>
    </div>
    
    <button type="submit">Add Movie</button>
    <a href="index.php">Cancel</a>
</form>

<?php include '../includes/footer.php'; ?>
