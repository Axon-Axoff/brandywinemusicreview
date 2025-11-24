<?php
// Main controller for Music DB
require_once __DIR__ . '/core/initialize_data.php';
require_once __DIR__ . '/core/functions.php';

$page = $_GET['page'] ?? 'home';
$type = $_GET['type'] ?? 'album'; // default to albums

$routes = [
    'login'        => __DIR__ . '/views/login_view.php',
    'register'     => __DIR__ . '/views/register_user_view.php',
    'music_list'   => __DIR__ . '/views/music_list_view.php',
    'music'        => __DIR__ . '/views/music_view.php',
    'members_list' => __DIR__ . '/views/members_list_view.php',
    'member'       => __DIR__ . '/views/member_view.php',
    'messages'     => __DIR__ . '/views/messages_view.php',
    'logout'       => __DIR__ . '/views/logout_view.php',
    'profile'      => __DIR__ . '/views/profile_view.php',
    'add'          => __DIR__ . '/views/add_music_view.php',
    'home'         => __DIR__ . '/views/home_view.php',
    'about'        => __DIR__ . '/views/home_view.php',
    'update_music' => __DIR__ . '/views/update_music_view.php',
    'hash'         => __DIR__ . '/views/hash_view.php',
    'stats'        => __DIR__ . '/views/stats_view.php',
];

// Load the requested page or fallback to home
if (array_key_exists($page, $routes)) {
    require $routes[$page];
} else {
    require $routes['home'];
}
