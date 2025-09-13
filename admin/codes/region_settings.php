<?php
session_start();
include('../../config/dbcon.php');
include('../inc/countries.php');

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    $_SESSION['error'] = "Unauthorized access.";
    header("Location: ../login.php");
    exit(0);
}

$user_id = $_SESSION['id'];

// Handle Add Region
if (isset($_POST['add_region'])) {
    $country = mysqli_real_escape_string($con, $_POST['country']);
    $currency = mysqli_real_escape_string($con, $_POST['currency']);
    $alt_currency = !empty($_POST['alt_currency']) ? mysqli_real_escape_string($con, $_POST['alt_currency']) : null;
    $crypto = isset($_POST['crypto']) ? 1 : 0;
    $Channel = mysqli_real_escape_string($con, $_POST['Channel']);
    $alt_channel = !empty($_POST['alt_channel']) ? mysqli_real_escape_string($con, $_POST['alt_channel']) : null;
    $Channel_name = mysqli_real_escape_string($con, $_POST['Channel_name']);
    $alt_ch_name = !empty($_POST['alt_ch_name']) ? mysqli_real_escape_string($con, $_POST['alt_ch_name']) : null;
    $Channel_number = mysqli_real_escape_string($con, $_POST['Channel_number']);
    $alt_ch_number = !empty($_POST['alt_ch_number']) ? mysqli_real_escape_string($con, $_POST['alt_ch_number']) : null;
    $chnl_value = !empty($_POST['chnl_value']) ? mysqli_real_escape_string($con, $_POST['chnl_value']) : null;
    $chnl_name_value = !empty($_POST['chnl_name_value']) ? mysqli_real_escape_string($con, $_POST['chnl_name_value']) : null;
    $chnl_number_value = !empty($_POST['chnl_number_value']) ? mysqli_real_escape_string($con, $_POST['chnl_number_value']) : null;
    $payment_amount = floatval($_POST['payment_amount']);
    $rate = floatval($_POST['rate']);
    $alt_rate = !empty($_POST['alt_rate']) ? mysqli_real_escape_string($con, $_POST['alt_rate']) : null;
    $qr_image_path = null;

    // Handle QR Image Upload
    if (isset($_FILES['qr_image']) && $_FILES['qr_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/qr_images/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_name = time() . '_' . basename($_FILES['qr_image']['name']);
        $target_path = $upload_dir . $file_name;

        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (in_array($_FILES['qr_image']['type'], $allowed_types) && $_FILES['qr_image']['size'] <= $max_size) {
            if (move_uploaded_file($_FILES['qr_image']['tmp_name'], $target_path)) {
                $qr_image_path = $target_path;
            } else {
                $_SESSION['error'] = "Failed to upload QR image.";
                header("Location: ../admin/region_settings.php");
                exit(0);
            }
        } else {
            $_SESSION['error'] = "Invalid QR image: File type not allowed or size exceeds 5MB.";
            header("Location: ../admin/region_settings.php");
            exit(0);
        }
    }

    $query = "INSERT INTO region_settings (country, currency, alt_currency, crypto, Channel, alt_channel, Channel_name, alt_ch_name, Channel_number, alt_ch_number, chnl_value, chnl_name_value, chnl_number_value, qr_image, payment_amount, rate, alt_rate, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $con->prepare($query);
    $stmt->bind_param("ssssissssssssssssi", $country, $currency, $alt_currency, $crypto, $Channel, $alt_channel, $Channel_name, $alt_ch_name, $Channel_number, $alt_ch_number, $chnl_value, $chnl_name_value, $chnl_number_value, $qr_image_path, $payment_amount, $rate, $alt_rate, $user_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Region added successfully.";
    } else {
        $_SESSION['error'] = "Failed to add region: " . $con->error;
        // Clean up uploaded file if any
        if ($qr_image_path && file_exists($qr_image_path)) {
            unlink($qr_image_path);
        }
    }
    $stmt->close();
    header("Location: ../admin/region_settings.php");
    exit(0);
}

// Handle Update Region
if (isset($_POST['update_region'])) {
    $region_id = intval($_POST['region_id']);
    $country = mysqli_real_escape_string($con, $_POST['country']);
    $currency = mysqli_real_escape_string($con, $_POST['currency']);
    $alt_currency = !empty($_POST['alt_currency']) ? mysqli_real_escape_string($con, $_POST['alt_currency']) : null;
    $crypto = isset($_POST['crypto']) ? 1 : 0;
    $Channel = mysqli_real_escape_string($con, $_POST['Channel']);
    $alt_channel = !empty($_POST['alt_channel']) ? mysqli_real_escape_string($con, $_POST['alt_channel']) : null;
    $Channel_name = mysqli_real_escape_string($con, $_POST['Channel_name']);
    $alt_ch_name = !empty($_POST['alt_ch_name']) ? mysqli_real_escape_string($con, $_POST['alt_ch_name']) : null;
    $Channel_number = mysqli_real_escape_string($con, $_POST['Channel_number']);
    $alt_ch_number = !empty($_POST['alt_ch_number']) ? mysqli_real_escape_string($con, $_POST['alt_ch_number']) : null;
    $chnl_value = !empty($_POST['chnl_value']) ? mysqli_real_escape_string($con, $_POST['chnl_value']) : null;
    $chnl_name_value = !empty($_POST['chnl_name_value']) ? mysqli_real_escape_string($con, $_POST['chnl_name_value']) : null;
    $chnl_number_value = !empty($_POST['chnl_number_value']) ? mysqli_real_escape_string($con, $_POST['chnl_number_value']) : null;
    $payment_amount = floatval($_POST['payment_amount']);
    $rate = floatval($_POST['rate']);
    $alt_rate = !empty($_POST['alt_rate']) ? mysqli_real_escape_string($con, $_POST['alt_rate']) : null;

    // Fetch current QR image path
    $check_query = "SELECT qr_image FROM region_settings WHERE id = ?";
    $check_stmt = $con->prepare($check_query);
    $check_stmt->bind_param("i", $region_id);
    $check_stmt->execute();
    $current_row = $check_stmt->get_result()->fetch_assoc();
    $current_qr_image = $current_row['qr_image'] ?? null;
    $check_stmt->close();

    $qr_image_path = $current_qr_image;

    // Handle QR Image Upload
    if (isset($_FILES['qr_image']) && $_FILES['qr_image']['error'] === UPLOAD_ERR_OK) {
        // Delete old image if exists
        if ($current_qr_image && file_exists($current_qr_image)) {
            unlink($current_qr_image);
        }

        $upload_dir = '../uploads/qr_images/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_name = time() . '_' . basename($_FILES['qr_image']['name']);
        $target_path = $upload_dir . $file_name;

        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (in_array($_FILES['qr_image']['type'], $allowed_types) && $_FILES['qr_image']['size'] <= $max_size) {
            if (move_uploaded_file($_FILES['qr_image']['tmp_name'], $target_path)) {
                $qr_image_path = $target_path;
            } else {
                $_SESSION['error'] = "Failed to upload QR image.";
                header("Location: ../admin/edit-region.php?id=" . $region_id);
                exit(0);
            }
        } else {
            $_SESSION['error'] = "Invalid QR image: File type not allowed or size exceeds 5MB.";
            header("Location: ../admin/edit-region.php?id=" . $region_id);
            exit(0);
        }
    }

    $query = "UPDATE region_settings SET country = ?, currency = ?, alt_currency = ?, crypto = ?, Channel = ?, alt_channel = ?, Channel_name = ?, alt_ch_name = ?, Channel_number = ?, alt_ch_number = ?, chnl_value = ?, chnl_name_value = ?, chnl_number_value = ?, qr_image = ?, payment_amount = ?, rate = ?, alt_rate = ? WHERE id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("ssssissssssssssssi", $country, $currency, $alt_currency, $crypto, $Channel, $alt_channel, $Channel_name, $alt_ch_name, $Channel_number, $alt_ch_number, $chnl_value, $chnl_name_value, $chnl_number_value, $qr_image_path, $payment_amount, $rate, $alt_rate, $region_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Region updated successfully.";
    } else {
        $_SESSION['error'] = "Failed to update region: " . $con->error;
        // Restore old image if upload failed and old was deleted
        if ($qr_image_path !== $current_qr_image && !empty($_FILES['qr_image']['name'])) {
            if ($current_qr_image && !file_exists($current_qr_image)) {
                // This case shouldn't happen, but just in case
            }
        }
    }
    $stmt->close();
    header("Location: ../admin/region_settings.php");
    exit(0);
}

// Handle Delete Region
if (isset($_POST['delete'])) {
    $region_id = intval($_POST['region_id']);

    // Fetch current QR image path
    $check_query = "SELECT qr_image FROM region_settings WHERE id = ?";
    $check_stmt = $con->prepare($check_query);
    $check_stmt->bind_param("i", $region_id);
    $check_stmt->execute();
    $current_row = $check_stmt->get_result()->fetch_assoc();
    $current_qr_image = $current_row['qr_image'] ?? null;
    $check_stmt->close();

    // Delete QR image if exists
    if ($current_qr_image && file_exists($current_qr_image)) {
        unlink($current_qr_image);
    }

    $query = "DELETE FROM region_settings WHERE id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $region_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Region deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete region: " . $con->error;
    }
    $stmt->close();
    header("Location: ../admin/region_settings.php");
    exit(0);
}

// If no action, redirect
$_SESSION['error'] = "No action specified.";
header("Location: ../admin/region_settings.php");
exit(0);
?>
