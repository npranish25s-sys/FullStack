<?php
session_start();
$pageTitle = 'Search Movies';
include '../includes/header.php';

$conn = getConnection();
$admin = isAdmin($conn);

$results = [];
$searchPerformed = false;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])) {
    $searchPerformed = true;
    $searchTerm = sanitize($_GET['search'] ?? '');
    $genreFilter = $_GET['genre'] ?? '';
    $yearFrom = $_GET['year_from'] ?? '';
    $yearTo = $_GET['year_to'] ?? '';
    $ratingMin = $_GET['rating_min'] ?? '';
    
    $sql = "SELECT DISTINCT m.id, m.title, m.year, m.rating, m.description,
            GROUP_CONCAT(DISTINCT g.name ORDER BY g.name SEPARATOR ', ') as genres,
            GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') as cast_members
            FROM movies m
            LEFT JOIN movie_genres mg ON m.id = mg.movie_id
            LEFT JOIN genres g ON mg.genre_id = g.id
            LEFT JOIN movie_cast mc ON m.id = mc.movie_id
            LEFT JOIN cast c ON mc.cast_id = c.id
            WHERE 1=1";
    
    $params = [];
    
    if (!empty($searchTerm)) {
        $sql .= " AND (m.title LIKE ? OR m.description LIKE ? OR c.name LIKE ?)";
        $searchParam = '%' . $searchTerm . '%';
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    if (!empty($genreFilter)) {
        $sql .= " AND g.id = ?";
        $params[] = $genreFilter;
    }
    
    if (!empty($yearFrom)) {
        $sql .= " AND m.year >= ?";
        $params[] = $yearFrom;
    }
    
    if (!empty($yearTo)) {
        $sql .= " AND m.year <= ?";
        $params[] = $yearTo;
    }
    
    if (!empty($ratingMin)) {
        $sql .= " AND m.rating >= ?";
        $params[] = $ratingMin;
    }
    
    $sql .= " GROUP BY m.id ORDER BY m.year DESC, m.title ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
}

$genresStmt = $conn->prepare("SELECT * FROM genres ORDER BY name");
$genresStmt->execute();
$allGenres = $genresStmt->fetchAll();
?>

<h2>Search Movies</h2>

<form method="GET">
    <label>Search</label>
    <input type="text" id="search" name="search" value="<?php echo escape($_GET['search'] ?? ''); ?>" placeholder="Title, cast, or description">
    <div id="autocomplete-results"></div>
    
    <h3>Advanced Search</h3>
    
    <label>Genre</label>
    <select name="genre">
        <option value="">All Genres</option>
        <?php foreach ($allGenres as $genre): ?>
            <option value="<?php echo $genre['id']; ?>" <?php echo (isset($_GET['genre']) && $_GET['genre'] == $genre['id']) ? 'selected' : ''; ?>>
                <?php echo escape($genre['name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    
    <label>Minimum Rating</label>
    <select name="rating_min">
        <option value="">Any Rating</option>
        <?php for ($i = 1; $i <= 9; $i++): ?>
            <option value="<?php echo $i; ?>" <?php echo (isset($_GET['rating_min']) && $_GET['rating_min'] == $i) ? 'selected' : ''; ?>>
                <?php echo $i; ?>+
            </option>
        <?php endfor; ?>
    </select>
    
    <label>Year From</label>
    <input type="number" name="year_from" value="<?php echo escape($_GET['year_from'] ?? ''); ?>">
    
    <label>Year To</label>
    <input type="number" name="year_to" value="<?php echo escape($_GET['year_to'] ?? ''); ?>">
    
    <button type="submit">Search</button>
    <a href="search.php">Clear</a>
</form>

<?php if ($searchPerformed): ?>
    <h3>Results: <?php echo count($results); ?></h3>
    
    <?php if (empty($results)): ?>
        <p>No movies found.</p>
    <?php else: ?>
        <div class="movies">
            <?php foreach ($results as $movie): ?>
                <div class="movie-card">
                    <h3><?php echo escape($movie['title']); ?> (<?php echo escape($movie['year']); ?>)</h3>
                    <p>Rating: <?php echo formatRating($movie['rating']); ?>/10</p>
                    <?php if ($movie['genres']): ?>
                        <p>Genres: <?php echo escape($movie['genres']); ?></p>
                    <?php endif; ?>
                    <?php if ($movie['cast_members']): ?>
                        <p>Cast: <?php echo escape($movie['cast_members']); ?></p>
                    <?php endif; ?>
                    <?php if ($admin): ?>
                        <a href="edit.php?id=<?php echo $movie['id']; ?>">Edit</a>
                        <a href="delete.php?id=<?php echo $movie['id']; ?>">Delete</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
