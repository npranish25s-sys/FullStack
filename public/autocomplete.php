<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isset($_GET['term']) || strlen($_GET['term']) < 2) {
    echo json_encode([]);
    exit();
}

$term = sanitize($_GET['term']);
$conn = getConnection();

$sql = "SELECT DISTINCT m.title, m.year
        FROM movies m
        LEFT JOIN movie_cast mc ON m.id = mc.movie_id
        LEFT JOIN cast c ON mc.cast_id = c.id
        WHERE m.title LIKE ? OR c.name LIKE ?
        ORDER BY m.title ASC
        LIMIT 10";

$searchParam = '%' . $term . '%';
$stmt = $conn->prepare($sql);
$stmt->execute([$searchParam, $searchParam]);
$results = $stmt->fetchAll();

$suggestions = [];
foreach ($results as $result) {
    $suggestions[] = [
        'label' => $result['title'] . ' (' . $result['year'] . ')',
        'value' => $result['title']
    ];
}

echo json_encode($suggestions);
?>
