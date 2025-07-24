<?php
session_start();
include('../../config/dbcon.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle user update (matches frontend form fields: email, balance, referal_bonus)
    if (isset($_POST['update_user'])) {
        $id = mysqli_real_escape_string($con, $_POST['update_user']);
        $email = mysqli_real_escape_string($con, $_POST['email']);
        $bonus = mysqli_real_escape_string($con, $_POST['referal_bonus']);
        $balance = mysqli_real_escape_string($con, $_POST['balance']);

        // Validate inputs
        if (empty($id) || empty($email) || !is_numeric($bonus) || !is_numeric($balance)) {
            $_SESSION['error'] = "All fields are required and must be valid.";
            error_log("users.php - Invalid input for update_user: ID=$id, Email=$email, Bonus=$bonus, Balance=$balance");
            header("Location: ../edit-user?id=" . urlencode($id));
            exit;
        }

        // Update query (matches frontend fields: email, balance, referal_bonus)
        $query = "UPDATE users SET balance='$balance', referal_bonus='$bonus', email='$email' WHERE id='$id' LIMIT 1";
        $query_run = mysqli_query($con, $query);

        if ($query_run) {
            $_SESSION['success'] = "Updated successfully";
            error_log("users.php - User updated: ID=$id, Email=$email, Bonus=$bonus, Balance=$balance");
            header("Location: ../edit-user?id=" . urlencode($id));
            exit;
        } else {
            $_SESSION['error'] = "Failed to update user.";
            error_log("users.php - Update query error: " . mysqli_error($con));
            header("Location: ../edit-user?id=" . urlencode($id));
            exit;
        }
    }
    // Handle user deletion
    elseif (isset($_POST['delete_user'])) {
        $id = mysqli_real_escape_string($con, $_POST['delete_user']);
        $profile_pic = mysqli_real_escape_string($con, $_POST['profile_pic']);

        // Validate input
        if (empty($id)) {
            $_SESSION['error'] = "Invalid user ID.";
            error_log("users.php - Missing user ID for delete_user");
            header("Location: ../manage-users.php");
            exit;
        }

        $delete = "DELETE FROM users WHERE id='$id'";
        $delete_query = mysqli_query($con, $delete);

        if ($delete_query) {
            if (!empty($profile_pic) && file_exists("../../Uploads/profile-picture/" . $profile_pic)) {
                unlink("../../Uploads/profile-picture/" . $profile_pic);
            }
            $_SESSION['success'] = "Deleted successfully";
            error_log("users.php - User deleted: ID=$id");
            header("Location: ../manage-users.php");
            exit;
        } else {
            $_SESSION['error'] = "Failed to delete user.";
            error_log("users.php - Delete query error: " . mysqli_error($con));
            header("Location: ../manage-users.php");
            exit;
        }
    }
    // Handle verification status update
    elseif (isset($_POST['update_verify_status'])) {
        $user_id = mysqli_real_escape_string($con, $_POST['user_id']);
        $verify_status = mysqli_real_escape_string($con, $_POST['verify_status']);

        // Validate input
        if (empty($user_id) || !in_array($verify_status, ['0', '1', '2'])) {
            $_SESSION['error'] = "Invalid user ID or verification status.";
            error_log("users.php - Invalid input for update_verify_status: User ID=$user_id, Verify Status=$verify_status");
            header("Location: ../manage-users.php");
            exit;
        }

        $query = "UPDATE users SET verify='$verify_status' WHERE id='$user_id' LIMIT 1";
        $query_run = mysqli_query($con, $query);

        if ($query_run) {
            $_SESSION['success'] = "Verification status updated successfully.";
            error_log("users.php - Verification status updated: User ID=$user_id, Verify Status=$verify_status");
            header("Location: ../manage-users.php");
            exit;
        } else {
            $_SESSION['error'] = "Failed to update verification status.";
            error_log("users.php - Update verify status query error: " . mysqli_error($con));
            header("Location: ../manage-users.php");
            exit;
        }
    }
} else {
    $_SESSION['error'] = "Invalid request.";
    error_log("users.php - Invalid request method");
    header("Location: ../manage-users.php");
    exit;
}
?>
