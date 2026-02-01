<?php
session_start();
$pageTitle = 'Edit Movie';
include '../includes/header.php';

$conn = getConnection();
requireAdmin($conn);

$movieId = $_GET['id'] ?? 0;
if (!$movieId) {
    header('Location: index.php');
    exit();
}

$genresStmt = $conn->prepare("SELECT * FROM genres ORDER BY name");
$genresStmt->execute();
$allGenres = $genresStmt->fetchAll();

$castStmt = $conn->prepare("SELECT * FROM cast ORDER BY name");
$castStmt->execute();
$allCast = $castStmt->fetchAll();

$movieStmt = $conn->prepare("SELECT * FROM movies WHERE id = ?");
$movieStmt->execute([$movieId]);
$movie = $movieStmt->fetch();

if (!$movie) {
    header('Location: index.php');
    exit();
}

$currentGenresStmt = $conn->prepare("SELECT genre_id FROM movie_genres WHERE movie_id = ?");
$currentGenresStmt->execute([$movieId]);
$currentGenres = $currentGenresStmt->fetchAll(PDO::FETCH_COLUMN);

$currentCastStmt = $conn->prepare("SELECT cast_id FROM movie_cast WHERE movie_id = ?");
$currentCastStmt->execute([$movieId]);
$currentCast = $currentCastStmt->fetchAll(PDO::FETCH_COLUMN);

$errors = [];
$success = '';

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
            
            $stmt = $conn->prepare("UPDATE movies SET title = ?, year = ?, rating = ?, description = ? WHERE id = ?");
            $stmt->execute([$title, $year, $rating, $description, $movieId]);
            
            $conn->prepare("DELETE FROM movie_genres WHERE movie_id = ?")->execute([$movieId]);
            if (!empty($genres)) {
                $genreStmt = $conn->prepare("INSERT INTO movie_genres (movie_id, genre_id) VALUES (?, ?)");
                foreach ($genres as $genreId) {
                    $genreStmt->execute([$movieId, $genreId]);
                }
            }
            
            $conn->prepare("DELETE FROM movie_cast WHERE movie_id = ?")->execute([$movieId]);
            if (!empty($cast)) {
                $castStmt = $conn->prepare("INSERT INTO movie_cast (movie_id, cast_id) VALUES (?, ?)");
                foreach ($cast as $castId) {
                    $castStmt->execute([$movieId, $castId]);
                }
            }
            
            $conn->commit();
            $success = 'Movie updated!';
            
            $movieStmt->execute([$movieId]);
            $movie = $movieStmt->fetch();
            $currentGenresStmt->execute([$movieId]);
            $currentGenres = $currentGenresStmt->fetchAll(PDO::FETCH_COLUMN);
            $currentCastStmt->execute([$movieId]);
            $currentCast = $currentCastStmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            $conn->rollBack();
            $errors[] = 'Error: ' . $e->getMessage();
        }
    }
} else {
    $title = $movie['title'];
    $year = $movie['year'];
    $rating = $movie['rating'];
    $description = $movie['description'];
    $genres = $currentGenres;
    $cast = $currentCast;
}
?>

<h2>Edit Movie</h2>

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
    <input type="text" name="title" value="<?php echo escape($title); ?>" required>
    
    <label>Year *</label>
    <input type="number" name="year" value="<?php echo escape($year); ?>" required>
    
    <label>Rating (0-10) *</label>
    <input type="number" name="rating" step="0.1" min="0" max="10" value="<?php echo escape($rating); ?>" required>
    
    <label>Description</label>
    <textarea name="description"><?php echo escape($description); ?></textarea>
    
    <label>Genres</label>
    <div class="checkboxes">
        <?php foreach ($allGenres as $genre): ?>
            <label><input type="checkbox" name="genres[]" value="<?php echo $genre['id']; ?>" <?php echo in_array($genre['id'], $genres) ? 'checked' : ''; ?>><?php echo escape($genre['name']); ?></label>
        <?php endforeach; ?>
    </div>
    
    <label>Cast</label>
    <div class="checkboxes">
        <?php foreach ($allCast as $actor): ?>
            <label><input type="checkbox" name="cast[]" value="<?php echo $actor['id']; ?>" <?php echo in_array($actor['id'], $cast) ? 'checked' : ''; ?>><?php echo escape($actor['name']); ?></label>
        <?php endforeach; ?>
    </div>
    
    <button type="submit">Update Movie</button>
    <a href="index.php">Cancel</a>
</form>

<?php include '../includes/footer.php'; ?>
