<?php
$page_title = "Hash";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $pwd_hash = $_POST['pwdHash'];
  $hashed = password_hash($pwd_hash, PASSWORD_DEFAULT);
}
require __DIR__ . '/../templates/hash_template.php';
