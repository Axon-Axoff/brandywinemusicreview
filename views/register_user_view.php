<?php
$page_title = "Register";
$errors = [];
$success_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';

    $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret="
        . $recaptcha_secret . "&response=" . $recaptcha_response);
    $captcha_success = json_decode($verify, true);

    if ($app_dev == 'production' && !$captcha_success['success']) {
        $errors[] = "Captcha verification failed. Please try again.";
    } else {
        $user_name  = trim($_POST['user_name'] ?? '');
        $full_name  = trim($_POST['full_name'] ?? '');
        $email  = trim($_POST['email'] ?? '');
        $password   = $_POST['password'] ?? '';
        $confirm_pw = $_POST['confirm_password'] ?? '';

        // Validate
        if (empty($user_name)) $errors[] = "Username is required.";
        if (empty($full_name)) $errors[] = "Full name is required.";
        if (empty($email)) $errors[] = "Email is required.";
        if (empty($password)) $errors[] = "Password is required.";
        if ($password !== $confirm_pw) $errors[] = "Passwords do not match.";

        // Check for existing username
        if (empty($errors)) {
            $stmt = $db_connection->prepare("SELECT COUNT(*) FROM users WHERE user_name = :u");
            $stmt->execute(['u' => $user_name]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = "That username is already taken.";
            }
        }
      
        if (empty($errors)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
        
            $stmt = $db_connection->prepare("
                INSERT INTO users (user_name, full_name, email, password_hash, created_at, last_login, rater_flag) 
                VALUES (:user_name, :full_name, :email, :hash, NOW(), NOW(), 0)
            ");
            $stmt->execute([
                'user_name' => $user_name,
                'full_name' => $full_name,
                'email'     => $email,
                'hash'      => $hash
            ]);
          
            $new_id = $db_connection->lastInsertId();
          
            // Auto-login
            $_SESSION['user_id'] = $new_id;
            $_SESSION['user_name'] = $user_name;
            $_SESSION['full_name'] = $full_name;
            $_SESSION['email'] = $email;
            $_SESSION['profile_pic'] = "/uploads/profiles/generic.png"; // default
          
            // Redirect to homepage (or profile)
            redirect('?page=profile');
            exit;
        }
    }
    
}

require __DIR__ . '/../templates/register_user_template.php';
