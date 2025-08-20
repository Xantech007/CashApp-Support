<?php
session_start();
include('../config/dbcon.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)$_SESSION['user_id'];
    $email = $_POST['email'] ?? $_SESSION['email'];

    if (isset($_POST['update-profile'])) {
        $name = $_POST['name'];
        $country = $_POST['country'];
        $address = $_POST['address'];
        $old_filename = $_POST['old_image'];
        $image = $_FILES['image']['name'] ?? '';
        $image_size = $_FILES['image']['size'] ?? 0;

        // Validate inputs
        if (empty($name) || empty($country) || empty($email)) {
            $_SESSION['error'] = "Name, country, and email are required.";
            header("Location: ../users/users-profile");
            exit(0);
        }

        // Check if email is already taken by another user
        $check_email_query = "SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1";
        $stmt = $con->prepare($check_email_query);
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $_SESSION['error'] = "Email is already in use by another account.";
            header("Location: ../users/users-profile");
            exit(0);
        }

        $update_filename = $old_filename;

        // Handle image upload
        if ($image) {
            $image_ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'png', 'jpeg'];

            if (!in_array($image_ext, $allowed)) {
                $_SESSION['error'] = "You cannot upload a file of this type!";
                header("Location: ../users/users-profile");
                exit(0);
            }

            if ($image_size >= 2500000) {
                $_SESSION['error'] = "Image size should not exceed 2MB!";
                header("Location: ../users/users-profile");
                exit(0);
            }

            $update_filename = time() . '.' . $image_ext;
            $target_path = '../Uploads/profile-picture/' . $update_filename;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                $_SESSION['error'] = "Failed to upload image.";
                header("Location: ../users/users-profile");
                exit(0);
            }

            // Delete old image if it exists
            if ($old_filename && file_exists('../Uploads/profile-picture/' . $old_filename)) {
                unlink('../Uploads/profile-picture/' . $old_filename);
            }
        }

        // Update profile
        $query = "UPDATE users SET name = ?, country = ?, address = ?, email = ?, image = ? WHERE id = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("sssssi", $name, $country, $address, $email, $update_filename, $user_id);

        if ($stmt->execute()) {
            $_SESSION['name'] = $name;
            $_SESSION['country'] = $country;
            $_SESSION['address'] = $address;
            $_SESSION['email'] = $email;
            $_SESSION['image'] = $update_filename;
            $_SESSION['success'] = "Profile updated successfully!";
            header("Location: ../users/users-profile");
            exit(0);
        } else {
            $_SESSION['error'] = "Failed to update profile.";
            error_log("user-profile.php - Profile update error: " . $stmt->error);
            header("Location: ../users/users-profile");
            exit(0);
        }
    } elseif (isset($_POST['change-password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Validate inputs
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $_SESSION['error'] = "All password fields are required.";
            header("Location: ../users/users-profile");
            exit(0);
        }

        if ($new_password !== $confirm_password) {
            $_SESSION['error'] = "New password and confirm password do not match.";
            header("Location: ../users/users-profile");
            exit(0);
        }

        if (strlen($new_password) < 8 || !preg_match("/[A-Za-z0-9]/", $new_password)) {
            $_SESSION['error'] = "New password must be at least 8 characters long and contain letters or numbers.";
            header("Location: ../users/users-profile");
            exit(0);
        }

        // Verify current password
        $query = "SELECT password FROM users WHERE id = ? LIMIT 1";
        $stmt = $con->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $_SESSION['error'] = "User not found.";
            header("Location: ../users/users-profile");
            exit(0);
        }

        $user = $result->fetch_assoc();
        if (!password_verify($current_password, $user['password'])) {
            $_SESSION['error'] = "Current password is incorrect.";
            header("Location: ../users/users-profile");
            exit(0);
        }

        // Hash new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update password
        $query = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("si", $hashed_password, $user_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Password changed successfully.";
            header("Location: ../users/users-profile");
            exit(0);
        } else {
            $_SESSION['error'] = "Failed to change password.";
            error_log("user-profile.php - Password change error: " . $stmt->error);
            header("Location: ../users/users-profile");
            exit(0);
        }
    } else {
        $_SESSION['error'] = "Invalid form submission.";
        header("Location: ../users/users-profile");
        exit(0);
    }
} else {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: ../users/users-profile");
    exit(0);
}

// Close database connection
mysqli_close($con);
?>
