<?php

if (!isLoggedIn()) {
    redirect('?page=login');
}

$page_title = "Members";

// Fetch members with rating counts
$stmt = $db_connection->prepare("
    SELECT 
        u.user_id,
        u.user_name,
        u.full_name,
        u.profile_pic,
        u.created_at,
        COALESCE(SUM(CASE WHEN r.target_type = 'album' THEN 1 ELSE 0 END), 0) AS albums_rated,
        COALESCE(SUM(CASE WHEN r.target_type = 'song' THEN 1 ELSE 0 END), 0) AS songs_rated
    FROM users u
    LEFT JOIN ratings r ON u.user_id = r.rater_id
    GROUP BY u.user_id, u.user_name, u.full_name, u.profile_pic, u.created_at
    ORDER BY u.user_name ASC
");
$stmt->execute();
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch last 3 activities per user
$stmt = $db_connection->prepare("
    WITH all_activity AS (
        SELECT r.rater_id AS user_id, 'rated' AS action_type, r.target_type, r.target_id, r.date_added AS activity_time
        FROM ratings r
        UNION ALL
        SELECT c.commenter_id, 'commented', c.target_type, c.target_id, c.date_added
        FROM comments c
        UNION ALL
        SELECT m.creator_id, 'added', m.type, m.music_id, m.date_added
        FROM music m
    ),
    ranked AS (
        SELECT a.*, ROW_NUMBER() OVER (PARTITION BY a.user_id ORDER BY a.activity_time DESC) AS rn
        FROM all_activity a
    )
    SELECT * 
    FROM ranked
    WHERE rn <= 3
    ORDER BY user_id, activity_time DESC
");
$stmt->execute();
$raw_activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// group by user_id
$userActivities = [];
foreach ($raw_activities as $act) {
    $userActivities[$act['user_id']][] = $act;
}

// pick final
$latestByUser = [];
foreach ($userActivities as $uid => $acts) {
    $latestByUser[$uid] = pickLatestActivity($acts);
}

// Merge into members
foreach ($members as &$m) {
    $m['latest_activity'] = $latestByUser[$m['user_id']] ?? null;
}
unset($m);

// Load template
require __DIR__ . '/../templates/members_list_template.php';
