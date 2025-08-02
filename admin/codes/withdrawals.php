<?php
session_start();
include('../../config/dbcon.php');

// Check if the database connection is established
if (!$con) {
    $_SESSION['error'] = "Database connection failed.";
    header("Location: ../manage-withdrawals.php");
    exit(0);
}

// Check if the form was submitted
if (isset($_POST['complete']) && !empty($_POST['complete'])) {
    // Validate CSRF token (assuming a token is included in the form)
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Invalid CSRF token.";
        header("Location: ../manage-withdrawals.php");
        exit(0);
    }

    // Sanitize and validate the withdrawal ID
    $id = filter_var($_POST['complete'], FILTER_VALIDATE_INT);
    if ($id === false || $id <= 0) {
        $_SESSION['error'] = "Invalid withdrawal ID.";
        header("Location: ../manage-withdrawals.php");
        exit(0);
    }

    // Use prepared statement to update the withdrawal status
    $query = "UPDATE withdrawals SET status = ? WHERE id = ? LIMIT 1";
    $stmt = $con->prepare($query);
    if ($stmt) {
        $status = 1;
        $stmt->bind_param("ii", $status, $id);
        $success = $stmt->execute();
        $stmt->close();

        if ($success) {
            $_SESSION['success'] = "Withdrawal marked as completed successfully.";
        } else {
            $_SESSION['error'] = "Failed to update withdrawal status.";
        }
    } else {
        $_SESSION['error'] = "Database query preparation failed.";
    }
} else {
    $_SESSION['error'] = "Invalid request.";
}

// Close the database connection
$con->close();

// Redirect to the withdrawals management page
header("Location: ../manage-withdrawals.php");
exit(0);
?>
