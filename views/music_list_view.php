<?php
$type = $_GET['type'] ?? 'album';
// only allow album or song
if (!in_array($type, ['album','song'])) {
    $type = 'album';
}
$page_title = ucfirst($type) . 's';

$allowed_sorts = [
    'rating',
    'weighted_rating',
    'title',
    'artist',
    'genre',
    'release_date',
    'creator_name',
    'date_added',
    'total_ratings'
];

$sort   = $_GET['sort'] ?? 'date_added';
$dir    = $_GET['dir'] ?? 'desc';
$filter = $_GET['filter'] ?? null;
$value  = $_GET['value'] ?? null;

// ---------------- NEW PARAMS ----------------
$genres = $_GET['genres'] ?? [];
if (!is_array($genres)) {
    $genres = [$genres];
}

$year_range = $_GET['year_range'] ?? null;
$year_start = isset($_GET['year_start']) ? (int)$_GET['year_start'] : null;
$year_end   = isset($_GET['year_end'])   ? (int)$_GET['year_end']   : null;

// ---------------- SANITIZE ----------------
if (!in_array($sort, $allowed_sorts)) {
    $sort = 'weighted_rating';
}
$dir = strtolower($dir);
if (!in_array($dir, ['asc','desc'])) {
    $dir = 'desc';
}

$per_page = 18;
$page     = isset($_GET['page_num']) ? max(1, (int)$_GET['page_num']) : 1;

// ---------------- COUNT ----------------
$count_sql    = "SELECT COUNT(*) FROM music m WHERE m.type = :type";
$count_params = ['type' => $type];

// genres
if (!empty($genres)) {
    $placeholders = [];
    foreach ($genres as $i => $g) {
        $ph = ":genre$i";
        $placeholders[] = $ph;
        $count_params["genre$i"] = $g;
    }
    if ($placeholders) {
        $count_sql .= " AND m.genre IN (" . implode(',', $placeholders) . ")";
    }
}

// year range
if ($year_range) {
    [$start, $end] = explode('-', $year_range);
    $count_sql .= " AND YEAR(m.release_date) BETWEEN :yr_start AND :yr_end";
    $count_params['yr_start'] = (int)$start;
    $count_params['yr_end']   = (int)$end;
}

// custom range
if ($year_start && $year_end) {
    $count_sql .= " AND YEAR(m.release_date) BETWEEN :custom_start AND :custom_end";
    $count_params['custom_start'] = (int)$year_start;
    $count_params['custom_end']   = (int)$year_end;
}

// search filter
if ($filter === 'search' && !empty($value)) {
    $count_sql .= " AND (m.title LIKE :search OR m.artist LIKE :search OR m.genre LIKE :search OR m.creator_name LIKE :search OR m.release_date LIKE :search)";
    $count_params['search'] = "%" . $value . "%";
}

$count_stmt = $db_connection->prepare($count_sql);
foreach ($count_params as $k => $v) {
    $pdoType = is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR;
    $count_stmt->bindValue(":$k", $v, $pdoType);
}
$count_stmt->execute();
$total_items = (int)$count_stmt->fetchColumn();

$total_pages = (int)ceil($total_items / $per_page);
$offset      = ($page - 1) * $per_page;

// ---------------- FETCH ----------------
$sql = "
    SELECT m.*, COUNT(c.comment_id) AS comment_count
    FROM music m
    LEFT JOIN comments c 
      ON c.target_type = :type AND c.target_id = m.music_id
    WHERE m.type = :type
";

$fetch_params = ['type' => $type];

// genres
if (!empty($genres)) {
    $placeholders = [];
    foreach ($genres as $i => $g) {
        $ph = ":genreSel$i";
        $placeholders[] = $ph;
        $fetch_params["genreSel$i"] = $g;
    }
    if ($placeholders) {
        $sql .= " AND m.genre IN (" . implode(',', $placeholders) . ")";
    }
}

// year range
if ($year_range) {
    [$start, $end] = explode('-', $year_range);
    $sql .= " AND YEAR(m.release_date) BETWEEN :yr_start_sel AND :yr_end_sel";
    $fetch_params['yr_start_sel'] = (int)$start;
    $fetch_params['yr_end_sel']   = (int)$end;
}

// custom range
if ($year_start && $year_end) {
    $sql .= " AND YEAR(m.release_date) BETWEEN :custom_start_sel AND :custom_end_sel";
    $fetch_params['custom_start_sel'] = (int)$year_start;
    $fetch_params['custom_end_sel']   = (int)$year_end;
}

// search filter
if ($filter === 'search' && !empty($value)) {
    $sql .= " AND (m.title LIKE :searchSel OR m.artist LIKE :searchSel OR m.genre LIKE :searchSel OR m.creator_name LIKE :searchSel OR m.release_date LIKE :searchSel)";
    $fetch_params['searchSel'] = "%" . $value . "%";
}

$sql .= "
    GROUP BY m.music_id
    ORDER BY $sort $dir, m.title ASC, m.music_id ASC
    LIMIT :limit OFFSET :offset
";

$stmt = $db_connection->prepare($sql);

foreach ($fetch_params as $k => $v) {
    $pdoType = is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR;
    $stmt->bindValue(":$k", $v, $pdoType);
}
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();
$music_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/../templates/music_list_template.php';
