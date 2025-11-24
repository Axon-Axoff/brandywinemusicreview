<?php
session_start();

//.env loader
function loadEnv($path)
{
  if (!file_exists($path)) return;
  $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $line) {
    if (str_starts_with(trim($line), '#')) continue; // skip comments
    [$name, $value] = array_map('trim', explode('=', $line, 2));
    $value = trim($value, '"\''); // strip quotes
    putenv("$name=$value");
    $_ENV[$name] = $value;
  }
}

// Try loading .env in project root or one directory up
loadEnv(__DIR__ . '/../.env');

$site_title = getenv('SITE_TITLE') ?: "Music DB";
$recaptcha_key = getenv('RECAPTCHA_SITE_KEY');
$recaptcha_secret = getenv('RECAPTCHA_SECRET_KEY');

// Detect environment (development / production)
$app_env = getenv('APP_ENV') ?: 'production';

if ($app_env === 'development') {
  $database_host = getenv('DEV_DB_HOST');
  $database_name = getenv('DEV_DB_NAME');
  $database_user = getenv('DEV_DB_USER');
  $database_password = getenv('DEV_DB_PASS');
} else {
  $database_host = getenv('PROD_DB_HOST');
  $database_name = getenv('PROD_DB_NAME');
  $database_user = getenv('PROD_DB_USER');
  $database_password = getenv('PROD_DB_PASS');
}

try {
  $db_connection = new PDO(
    "mysql:host=$database_host;dbname=$database_name;charset=utf8mb4",
    $database_user,
    $database_password
  );
  $db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $exception) {
  // Hide credentials in production
  if ($app_env === 'development') {
    die("Database connection failed: " . $exception->getMessage());
  } else {
    error_log("Database connection failed: " . $exception->getMessage());
    die("Database connection error. Please try again later.");
  }
}

$valid_genres = array(
  'A cappella',
  'Acoustic Blues',
  'Alternative Country',
  'Alternative Rap',
  'Alt Rock',
  'Alt Punk',
  'Ambient',
  'Avant-Garde Jazz',
  'Bebop',
  'Big Band',
  'Bluegrass',
  'Blues',
  'Blues Country',
  'Blues Rock',
  'Breakbeat / Breakstep',
  'Classic Rock',
  'Club / Club Dance',
  'Contemporary Jazz',
  'Contemporary R&B',
  'Country',
  'Country Gospel',
  'Country Pop',
  'Country Rock',
  'Cowboy / Western',
  'Dance Pop',
  'Disco',
  'Drum & Bass',
  'Electric Blues',
  'Electro',
  'Electro Pop',
  'Electronic Rock',
  'Electronic',
  'Ethno-Jazz',
  'Europop',
  'Experimental Rock',
  'Folk',
  'Folk Blues',
  'Folk Punk',
  'Funk',
  'Fusion',
  'Gangsta Rap',
  'Glam Metal',
  'Glam Rock',
  'Gospel Blues',
  'Goth / Gothic Rock',
  'Hard Rock',
  'Hip-Hop',
  'House',
  'IDM/Experimental',
  'Indie Folk',
  'Indie / Lo-Fi',
  'Indie Rock',
  'Industrial',
  'Jam Bands',
  'Jazz',
  'Jazz-Funk',
  'Latin',
  'Lounge',
  'Metal',
  'Motown',
  'New Wave',
  'Old School Rap',
  'Pop',
  'Pop Punk',
  'Pop/Rock',
  'Post Punk',
  'Prog-Rock/Art Rock',
  'Progressive Metal',
  'Proto Punk',
  'Psychedelic Rock',
  'Psychedelic Pop',
  'Punk',
  'R&B/Soul',
  'Ragtime',
  'Reggae',
  'Rock & Roll',
  'Rockabilly',
  'Singer/Songwriter',
  'Ska',
  'Ska Jazz',
  'Smooth Jazz',
  'Soft Rock',
  'Soul Blues',
  'Southern Rock',
  'Surf',
  'Swing Jazz',
  'Synth Pop',
  'Techno',
  'Traditional Country',
  'Trance',
  'Vocal Jazz',
  'Vocal Pop',
  'World'
);
