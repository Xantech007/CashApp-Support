<?php
session_start();
include('../../config/dbcon.php');

// Check if user is authorized (e.g., admin)
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "Unauthorized access";
    header("Location: ../login");
    exit(0);
}

if (isset($_POST['complete'])) {
    try {
        // Sanitize input
        $id = filter_var($_POST['complete'], FILTER_SANITIZE_NUMBER_INT);
        
        // Validate ID
        if (!is_numeric($id) || $id <= 0) {
            $_SESSION['error'] = "Invalid withdrawal ID";
            header("Location: ../manage-withdrawals");
            exit(0);
        }

        // Prepare statement to prevent SQL injection
        $query = "UPDATE withdrawals SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND status = ?";
        $stmt = mysqli_prepare($con, $query);
        
        if ($stmt === false) {
            throw new Exception("Failed to prepare statement: " . mysqli_error($con));
        }

        // Bind parameters (status = 1 for completed, ID, and check for pending status = 0)
        $new_status = 1;
        $pending_status = 0;
        mysqli_stmt_bind_param($stmt, "iii", $new_status, $id, $pending_status);
        
        // Execute query
        if (mysqli_stmt_execute($stmt)) {
            // Check if any rows were affected
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                // Log the action (optional, adjust based on your logging system)
                $admin_id = $_SESSION['admin_id'];
                $log_query = "INSERT INTO admin_logs (admin_id, action, details, created_at) 
                            VALUES (?, 'Withdrawal Completed', ?, NOW())";
                $log_stmt = mysqli_prepare($con, $log_query);
                $details = "Completed withdrawal ID: $id";
                mysqli_stmt_bind_param($log_stmt, "is", $admin_id, $details);
                mysqli_stmt_execute($log_stmt);
                mysqli_stmt_close($log_stmt);

                $_SESSION['success'] = "Withdrawal completed successfully";
            } else {
                $_SESSION['error'] = "Withdrawal not found or already completed";
            }
        } else {
            throw new Exception("Query execution failed: " . mysqli_stmt_error($stmt));
        }

        mysqli_stmt_close($stmt);
    } catch (Exception $e) {
        // Log error (adjust based on your error logging system)
        error_log("Withdrawal completion error: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred while processing the withdrawal";
    }

    header("Location: ../manage-withdrawals");
    exit(0);
} else {
    $_SESSION['error'] = "Invalid request";
    header("Location: ../manage-withdrawals");
    exit(0);
}

// Close database connection
mysqli_close($con);
?>
