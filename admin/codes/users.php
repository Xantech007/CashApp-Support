<?php
session_start();
include('../../config/dbcon.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_user'])) {
        $id = (int)$_POST['update_user'];
        $email = $_POST['email'];
        $bonus = (float)$_POST['referal_bonus'];
        $balance = (float)$_POST['balance'];
        $message = $_POST['message'] ?? '';
        $password = !empty($_POST['password']) ? $_POST['password'] : '';

        // Validate inputs
        if (empty($id) || empty($email) || !is_numeric($bonus) || !is_numeric($balance)) {
            $_SESSION['error'] = "All required fields must be valid.";
            error_log("users.php - Invalid input for update_user: ID=$id, Email=$email, Bonus=$bonus, Balance=$balance");
            header("Location: ../edit-user?id=" . urlencode($id));
            exit(0);
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Invalid email format.";
            error_log("users.php - Invalid email format: Email=$email");
            header("Location: ../edit-user?id=" . urlencode($id));
            exit(0);
        }

        // Validate numeric values
        if ($balance < 0 || $bonus < 0) {
            $_SESSION['error'] = "Balance and referral bonus cannot be negative.";
            error_log("users.php - Negative values: Balance=$balance, Bonus=$bonus");
            header("Location: ../edit-user?id=" . urlencode($id));
            exit(0);
        }

        // Validate password (if provided)
        if (!empty($password) && (strlen($password) < 8 || !preg_match("/[A-Za-z0-9]/", $password))) {
            $_SESSION['error'] = "Password must be at least 8 characters long and contain letters or numbers.";
            error_log("users.php - Invalid password format: ID=$id");
            header("Location: ../edit-user?id=" . urlencode($id));
            exit(0);
        }

        // Check if email is already taken by another user
        $check_email_query = "SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1";
        $stmt = $con->prepare($check_email_query);
        $stmt->bind_param("si", $email, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $_SESSION['error'] = "Email already exists.";
            error_log("users.php - Email already exists: Email=$email, ID=$id");
            header("Location: ../edit-user?id=" . urlencode($id));
            exit(0);
        }

        // Prepare the update query
        $query = "UPDATE users SET email = ?, balance = ?, referal_bonus = ?, message = ?";
        $params = [$email, $balance, $bonus, $message];
        $types = "sdds";

        // Handle password update if provided
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query .= ", password = ?";
            $params[] = $hashed_password;
            $types .= "s";
        }

        $query .= " WHERE id = ?";
        $params[] = $id;
        $types .= "i";

        // Execute the prepared statement
        $stmt = $con->prepare($query);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            $_SESSION['success'] = "User updated successfully.";
            error_log("users.php - User updated: ID=$id, Email=$email, Bonus=$bonus, Balance=$balance, Message=$message" . (!empty($password) ? ", Password updated" : ""));
            header("Location: ../edit-user?id=" . urlencode($id));
            exit(0);
        } else {
            $_SESSION['error'] = "Failed to update user.";
            error_log("users.php - Update query error: " . $stmt->error);
            header("Location: ../edit-user?id=" . urlencode($id));
            exit(0);
        }
    } elseif (isset($_POST['delete_user'])) {
        $id = (int)$_POST['delete_user'];
        $profile_pic = $_POST['profile_pic'] ?? '';

        // Validate input
        if (empty($id)) {
            $_SESSION['error'] = "Invalid user ID.";
            error_log("users.php - Missing user ID for delete_user");
            header("Location: ../manage-users.php");
            exit(0);
        }

        // Delete user
        $delete = "DELETE FROM users WHERE id = ?";
        $stmt = $con->prepare($delete);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            if (!empty($profile_pic) && file_exists("../../Uploads/profile-picture/" . $profile_pic)) {
                unlink("../../Uploads/profile-picture/" . $profile_pic);
            }
            $_SESSION['success'] = "User deleted successfully.";
            error_log("users.php - User deleted: ID=$id");
            header("Location: ../manage-users.php");
            exit(0);
        } else {
            $_SESSION['error'] = "Failed to delete user.";
            error_log("users.php - Delete query error: " . $stmt->error);
            header("Location: ../manage-users.php");
            exit(0);
        }
    } elseif (isset($_POST['update_verify_status'])) {
        $user_id = (int)$_POST['user_id'];
        $verify_status = $_POST['verify_status'];

        // Validate input
        if (empty($user_id) || !in_array($verify_status, ['0', '1', '2'])) {
            $_SESSION['error'] = "Invalid user ID or verification status.";
            error_log("users.php - Invalid input for update_verify_status: User ID=$user_id, Verify Status=$verify_status");
            header("Location: ../manage-users.php");
            exit(0);
        }

        // Update verification status
        $query = "UPDATE users SET verify = ? WHERE id = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("si", $verify_status, $user_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Verification status updated successfully.";
            error_log("users.php - Verification status updated: User ID=$user_id, Verify Status=$verify_status");
            header("Location: ../manage-users.php");
            exit(0);
        } else {
            $_SESSION['error'] = "Failed to update verification status.";
            error_log("users.php - Update verify status query error: " . $stmt->error);
            header("Location: ../manage-users.php");
            exit(0);
        }
    }
} else {
    $_SESSION['error'] = "Invalid request method.";
    error_log("users.php - Invalid request method");
    header("Location: ../manage-users.php");
    exit(0);
}

// Close database connection
mysqli_close($con);
?>
