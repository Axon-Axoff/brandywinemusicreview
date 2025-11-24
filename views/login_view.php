<?php
$page_title = "Login";
$login_error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_username = sanitizeInput($_POST['user_name'] ?? '');
    $input_password = $_POST['password'] ?? '';

    if (!empty($input_username) && !empty($input_password)) {
        $statement = $db_connection->prepare("
            SELECT user_id, user_name, password_hash, full_name, profile_pic, admin 
            FROM users
            WHERE user_name = :user_name
        ");
        $statement->execute(['user_name' => $input_username]);
        $user = $statement->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($input_password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['user_name'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['profile_pic'] = $user['profile_pic'] ?? '/uploads/profiles/default.png';
            $_SESSION['admin'] = $user['admin'];
            $redirect = $_SESSION['last_page'] ?? '/';
            header("Location: $redirect");
            exit;
        } else {
            $login_error = "Username or Password Incorrect.";
        }
    } else {
        $login_error = "Please enter both username and password.";
    }
}

require __DIR__ . '/../templates/login_template.php';
if ($user && password_verify($input_password, $user['password_hash'])) {
    
}
