<?php
session_start();
$pageTitle = 'All Movies';
include '../includes/header.php';

$conn = getConnection();
$admin = isAdmin($conn);

$sql = "SELECT m.id, m.title, m.year, m.rating, m.description, 
        GROUP_CONCAT(DISTINCT g.name ORDER BY g.name SEPARATOR ', ') as genres,
        GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') as cast_members
        FROM movies m
        LEFT JOIN movie_genres mg ON m.id = mg.movie_id
        LEFT JOIN genres g ON mg.genre_id = g.id
        LEFT JOIN movie_cast mc ON m.id = mc.movie_id
        LEFT JOIN cast c ON mc.cast_id = c.id
        GROUP BY m.id
        ORDER BY m.year DESC, m.title ASC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$movies = $stmt->fetchAll();
?>

<h2>All Movies</h2>
<?php if ($admin): ?>
    <a href="add.php" class="btn">Add New Movie</a>
<?php endif; ?>

<div class="movies">
    <?php foreach ($movies as $movie): ?>
        <div class="movie-card">
            <h3><?php echo escape($movie['title']); ?> (<?php echo escape($movie['year']); ?>)</h3>
            <p>Rating: <?php echo formatRating($movie['rating']); ?>/10</p>
            <?php if ($movie['genres']): ?>
                <p>Genres: <?php echo escape($movie['genres']); ?></p>
            <?php endif; ?>
            <?php if ($movie['cast_members']): ?>
                <p>Cast: <?php echo escape($movie['cast_members']); ?></p>
            <?php endif; ?>
            <?php if ($movie['description']): ?>
                <p><?php echo escape($movie['description']); ?></p>
            <?php endif; ?>
            <?php if ($admin): ?>
                <a href="edit.php?id=<?php echo $movie['id']; ?>">Edit</a>
                <a href="delete.php?id=<?php echo $movie['id']; ?>" onclick="return confirm('Delete this movie?');">Delete</a>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<?php include '../includes/footer.php'; ?>
