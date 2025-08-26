<?php
session_start();
include('../config/dbcon.php');

if (isset($_POST['withdraw'])) {
    // Sanitize inputs
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $amount = mysqli_real_escape_string($con, $_POST['amount']);
    $balance = mysqli_real_escape_string($con, $_POST['balance']);
    $network = mysqli_real_escape_string($con, $_POST['network']);
    $momo_name = mysqli_real_escape_string($con, $_POST['momo_name']);
    $momo_number = mysqli_real_escape_string($con, $_POST['momo_number']);

    // Check if the user's account is verified
    $verify_query = "SELECT verify FROM users WHERE email = ? LIMIT 1";
    $stmt = $con->prepare($verify_query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $verify_result = $stmt->get_result();

    if ($verify_result && $verify_result->num_rows > 0) {
        $user = $verify_result->fetch_assoc();
        if ($user['verify'] == 0) {
            $_SESSION['error'] = "Verify Your Account and Try Again.";
            header("Location: ../users/withdrawals");
            exit(0);
        } elseif ($user['verify'] == 1) {
            $_SESSION['error'] = "Verification Under Review, Try Again Later.";
            header("Location: ../users/withdrawals");
            exit(0);
        } elseif ($user['verify'] != 2) {
            $_SESSION['error'] = "Invalid verification status.";
            header("Location: ../users/withdrawals");
            exit(0);
        }
    } else {
        $_SESSION['error'] = "User not found.";
        header("Location: ../users/withdrawals");
        exit(0);
    }
    $stmt->close();

    // Validate inputs
    if (empty($network) || empty($momo_name) || empty($momo_number)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: ../users/withdrawals");
        exit(0);
    }

    if ($amount < 50) {
        $_SESSION['error'] = "Minimum withdrawal is set at $50";
        header("Location: ../users/withdrawals");
        exit(0);
    }

    if ($amount > $balance) {
        $_SESSION['error'] = "Request failed due to insufficient balance!";
        header("Location: ../users/withdrawals");
        exit(0);
    }

    // Fetch currency and rate from payment_details
    $payment_query = "SELECT currency, rate FROM payment_details LIMIT 1";
    $payment_result = $con->query($payment_query);

    if ($payment_result && $payment_result->num_rows > 0) {
        $payment = $payment_result->fetch_assoc();
        $currency = $payment['currency'];
        $rate = $payment['rate'];
    } else {
        $_SESSION['error'] = "Failed to fetch payment details.";
        header("Location: ../users/withdrawals");
        exit(0);
    }

    // Calculate total amount in GHS
    $total = $amount * $rate;

    // Insert withdrawal request using prepared statement
    $query = "INSERT INTO withdrawals (email, amount, network, momo_name, momo_number, status, created_at) 
              VALUES (?, ?, ?, ?, ?, '0', NOW())";
    $stmt = $con->prepare($query);
    $stmt->bind_param("sdsss", $email, $total, $network, $momo_name, $momo_number);

    if ($stmt->execute()) {
        // Update the user's balance
        $new_balance = $balance - $amount;
        $update_query = "UPDATE users SET balance = ? WHERE email = ?";
        $update_stmt = $con->prepare($update_query);
        $update_stmt->bind_param("ds", $new_balance, $email);

        if ($update_stmt->execute()) {
            $_SESSION['success'] = "$currency$total Withdrawn and Sent to $momo_name. Check Your MOMO Account.";
            header("Location: ../users/withdrawals");
            exit(0);
        } else {
            $_SESSION['error'] = "Failed to update balance.";
            header("Location: ../users/withdrawals");
            exit(0);
        }
        $update_stmt->close();
    } else {
        $_SESSION['error'] = "Failed to submit withdrawal request.";
        header("Location: ../users/withdrawals");
        exit(0);
    }
    $stmt->close();
}

if (isset($_POST['delete'])) {
    $id = mysqli_real_escape_string($con, $_POST['delete']);

    // Use prepared statement for deletion
    $delete_query = "DELETE FROM withdrawals WHERE id = ? LIMIT 1";
    $stmt = $con->prepare($delete_query);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Deleted Successfully";
        header("Location: ../users/withdrawals");
        exit(0);
    } else {
        $_SESSION['error'] = "Failed to delete withdrawal request.";
        header("Location: ../users/withdrawals");
        exit(0);
    }
    $stmt->close();
}
?>
