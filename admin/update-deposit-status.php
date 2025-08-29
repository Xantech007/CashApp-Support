<?php
session_start();
include('../config/dbcon.php');

// Check if the request is AJAX and the user is an admin (add your own admin check logic)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deposit_id']) && isset($_POST['approval_status'])) {
    $deposit_id = (int)$_POST['deposit_id'];
    $approval_status = trim($_POST['approval_status']);
    $valid_statuses = ['pending', 'approved', 'rejected'];

    // Validate the status
    if (!in_array($approval_status, $valid_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status value.']);
        exit;
    }

    // Update the deposit status
    $update_query = "UPDATE deposits SET approval_status = ?, updated_at = NOW() WHERE id = ?";
    $stmt = mysqli_prepare($con, $update_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "si", $approval_status, $deposit_id);
        if (mysqli_stmt_execute($stmt)) {
            // If status is 'approved', check if all installments are approved to update user verification
            if ($approval_status === 'approved') {
                // Get deposit details
                $deposit_query = "SELECT email, payment_plan, installment_number FROM deposits WHERE id = ?";
                $deposit_stmt = mysqli_prepare($con, $deposit_query);
                if ($deposit_stmt) {
                    mysqli_stmt_bind_param($deposit_stmt, "i", $deposit_id);
                    mysqli_stmt_execute($deposit_stmt);
                    $result = mysqli_stmt_get_result($deposit_stmt);
                    $deposit_data = mysqli_fetch_assoc($result);
                    $email = $deposit_data['email'];
                    $payment_plan = $deposit_data['payment_plan'];
                    mysqli_stmt_close($deposit_stmt);

                    // Check if all installments are approved
                    $total_paid_query = "SELECT COUNT(DISTINCT installment_number) as approved_installments 
                                        FROM deposits 
                                        WHERE email = ? AND payment_plan = ? AND approval_status = 'approved'";
                    $total_stmt = mysqli_prepare($con, $total_paid_query);
                    if ($total_stmt) {
                        mysqli_stmt_bind_param($total_stmt, "si", $email, $payment_plan);
                        mysqli_stmt_execute($total_stmt);
                        $result = mysqli_stmt_get_result($total_stmt);
                        $total_data = mysqli_fetch_assoc($result);
                        $approved_installments = $total_data['approved_installments'];
                        mysqli_stmt_close($total_stmt);

                        // Update user verification if all installments are approved
                        if ($approved_installments >= $payment_plan) {
                            $update_verify_query = "UPDATE users SET verify = 1, verify_time = NOW() WHERE email = ?";
                            $update_stmt = mysqli_prepare($con, $update_verify_query);
                            if ($update_stmt) {
                                mysqli_stmt_bind_param($update_stmt, "s", $email);
                                mysqli_stmt_execute($update_stmt);
                                mysqli_stmt_close($update_stmt);
                            }
                        }
                    }
                }
            }
            echo json_encode(['success' => true, 'message' => 'Status updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update status.']);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>
