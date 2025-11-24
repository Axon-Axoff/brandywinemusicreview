<?php
if (!isLoggedIn()) {
    redirect('?page=login');
}

$page_title = "My Profile";
$user_id = $_SESSION['user_id'];
$error_message = "";
$success_message = "";

// Fetch current user info
$stmt = $db_connection->prepare("SELECT full_name, email, bio, link_1_title, link_1_url, link_2_title, link_2_url, profile_pic FROM users WHERE user_id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

// Ensure default profile picture if none set
if (empty($user_data['profile_pic'])) {
    $user_data['profile_pic'] = '/uploads/profiles/generic.png';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitizeInput($_POST['full_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $bio = substr(sanitizeInput($_POST['bio'] ?? ''), 0, 500);
    $link_1_title = sanitizeInput($_POST['link_1_title'] ?? '');
    $link_1_url = sanitizeInput($_POST['link_1_url'] ?? '');
    $link_2_title = sanitizeInput($_POST['link_2_title'] ?? '');
    $link_2_url = sanitizeInput($_POST['link_2_url'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $profile_pic_path = $user_data['profile_pic'];

    if (empty($link_1_title) && !empty($link_1_url)) $link_1_title = $link_1_url;
    if (empty($link_2_title) && !empty($link_2_url)) $link_2_title = $link_2_url;
    
    if (!empty($_FILES['profile_pic']['name'])) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
        $file_type = mime_content_type($_FILES['profile_pic']['tmp_name']);

        if (in_array($file_type, $allowed_types)) {
            $tmp_file = $_FILES['profile_pic']['tmp_name'];
        
            // Load image
            switch ($file_type) {
                case 'image/jpeg': $image = imagecreatefromjpeg($tmp_file); break;
                case 'image/png':  $image = imagecreatefrompng($tmp_file); break;
                case 'image/webp': $image = imagecreatefromwebp($tmp_file); break;
                default: $image = null;
            }
          
            if ($image) {
                $width = imagesx($image);
                $height = imagesy($image);
                $min_side = min($width, $height);
            
                // Create cropped 300x300 canvas
                $cropped = imagecreatetruecolor(300, 300);
                imagecopyresampled(
                    $cropped, $image,
                    0, 0,
                    ($width - $min_side) / 2, ($height - $min_side) / 2,
                    300, 300,
                    $min_side, $min_side
                );
              
                // Ensure uploads dir exists
                $upload_dir = __DIR__ . '/../uploads/profiles/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
              
                // Save file as PNG
                $filename = 'user_' . $user_id . '.png';
                $full_path = $upload_dir . $filename;
                imagepng($cropped, $full_path);
              
                // Cleanup
                imagedestroy($cropped);
                imagedestroy($image);
              
                // Store relative path in DB and session
                $profile_pic_path = '/uploads/profiles/' . $filename;
                $_SESSION['profile_pic'] = $profile_pic_path;
            } else {
                $error_message = "Unable to process image.";
            }
        } else {
            $error_message = "Invalid file type. Please upload JPG, PNG, or WEBP.";
        }
    }

    // Handle password update
    if (!empty($new_password)) {
        if ($new_password === $confirm_password) {
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $db_connection->prepare("UPDATE users SET password_hash = :password_hash WHERE user_id = :user_id");
            $stmt->execute(['password_hash' => $password_hash, 'user_id' => $user_id]);
        } else {
            $error_message = "Passwords do not match.";
        }
    }

    // Update profile
    if (empty($error_message)) {
        $stmt = $db_connection->prepare("
            UPDATE users
            SET full_name = :full_name, email = :email, bio = :bio, link_1_title = :link_1_title, link_1_url = :link_1_url, link_2_title = :link_2_title, link_2_url = :link_2_url, profile_pic = :profile_pic
            WHERE user_id = :user_id
        ");
        $stmt->execute([
            'full_name'     => $full_name,
            'email'         => $email,
            'bio'           => $bio,
            'link_1_title'  => $link_1_title,
            'link_1_url'    => $link_1_url,
            'link_2_title'  => $link_2_title,
            'link_2_url'    => $link_2_url,
            'profile_pic'   => $profile_pic_path,
            'user_id'       => $user_id
        ]);

        $success_message = "Profile updated successfully.";
        $_SESSION['full_name'] = $full_name; // refresh session name if changed

        // Refresh $user_data immediately (so UI updates without logout)
        $user_data['full_name']     = $full_name;
        $user_data['email']         = $email;
        $user_data['bio']           = $bio;
        $user_data['profile_pic']   = $profile_pic_path;
        $user_data['link_1_title']  = $link_1_title;
        $user_data['link_1_url']    = $link_1_url;
        $user_data['link_2_title']  = $link_2_title;
        $user_data['link_2_url']    = $link_2_url;
    }
}

require __DIR__ . '/../templates/profile_template.php';
