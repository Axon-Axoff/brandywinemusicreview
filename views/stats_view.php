<?php
if (!isLoggedIn()) {
    redirect('?page=login');
}

$page_title = "Statistics";

// --- TOP 10 ALBUMS (weighted) ---
$stmt = $db_connection->query("
    SELECT m.*, COUNT(r.rating_id) AS total_raters, AVG(r.rating) AS avg_rating, m.weighted_rating
    FROM music m
    LEFT JOIN ratings r ON r.target_type='album' AND r.target_id=m.music_id
    WHERE m.type='album'
    GROUP BY m.music_id
    ORDER BY m.weighted_rating DESC
    LIMIT 10
");
$top_albums = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- TOP 10 SONGS (weighted) ---
$stmt = $db_connection->query("
    SELECT m.*, COUNT(r.rating_id) AS total_raters, AVG(r.rating) AS avg_rating, m.weighted_rating
    FROM music m
    LEFT JOIN ratings r ON r.target_type='song' AND r.target_id=m.music_id
    WHERE m.type='song'
    GROUP BY m.music_id
    ORDER BY m.weighted_rating DESC
    LIMIT 10
");
$top_songs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- TOP GENRES (albums) ---
$stmt = $db_connection->query("
    SELECT genre, COUNT(*) AS total
    FROM music
    WHERE type='album'
    GROUP BY genre
    ORDER BY total DESC
    LIMIT 10
");
$top_album_genres = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- TOP GENRES (songs) ---
$stmt = $db_connection->query("
    SELECT genre, COUNT(*) AS total
    FROM music
    WHERE type='song'
    GROUP BY genre
    ORDER BY total DESC
    LIMIT 10
");
$top_song_genres = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- MOST RATED ALBUMS ---
$stmt = $db_connection->query("
    SELECT m.*, COUNT(r.rating_id) AS total_raters
    FROM music m
    LEFT JOIN ratings r ON r.target_type='album' AND r.target_id=m.music_id
    WHERE m.type='album'
    GROUP BY m.music_id
    ORDER BY total_raters DESC
    LIMIT 10
");
$most_rated_albums = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- MOST RATED SONGS ---
$stmt = $db_connection->query("
    SELECT m.*, COUNT(r.rating_id) AS total_raters
    FROM music m
    LEFT JOIN ratings r ON r.target_type='song' AND r.target_id=m.music_id
    WHERE m.type='song'
    GROUP BY m.music_id
    ORDER BY total_raters DESC
    LIMIT 10
");
$most_rated_songs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- BY THE YEARS ---
$year_ranges = [
    [1900, 1945], [1945, 1955], [1950, 1960], [1955, 1965], [1960, 1970]
];
for ($y = 1965; $y <= 2015; $y += 5) {
    $year_ranges[] = [$y, $y + 10];
}

$albums_by_year = [];
$songs_by_year = [];

foreach ($year_ranges as [$start, $end]) {
    // Albums
    $stmt = $db_connection->prepare("
        SELECT COUNT(DISTINCT m.music_id) AS total, AVG(r.rating) AS avg_rating
        FROM music m
        LEFT JOIN ratings r ON r.target_type='album' AND r.target_id=m.music_id
        WHERE m.type='album' AND YEAR(m.release_date) BETWEEN :start AND :end
    ");
    $stmt->execute(['start' => $start, 'end' => $end]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $albums_by_year[] = [
        'range' => "$start-$end",
        'total' => (int)$row['total'],
        'avg_rating' => (float)$row['avg_rating']
    ];

    // Songs
    $stmt = $db_connection->prepare("
        SELECT COUNT(DISTINCT m.music_id) AS total, AVG(r.rating) AS avg_rating
        FROM music m
        LEFT JOIN ratings r ON r.target_type='song' AND r.target_id=m.music_id
        WHERE m.type='song' AND YEAR(m.release_date) BETWEEN :start AND :end
    ");
    $stmt->execute(['start' => $start, 'end' => $end]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $songs_by_year[] = [
        'range' => "$start-$end",
        'total' => (int)$row['total'],
        'avg_rating' => (float)$row['avg_rating']
    ];
}

require __DIR__ . '/../templates/stats_template.php';
