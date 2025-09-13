<?php
session_start();
include('../../config/dbcon.php');
include('../inc/countries.php');

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    $_SESSION['error'] = "Please log in to access this page.";
    header("Location: ../signin.php");
    exit(0);
}

// Verify user authorization
$auth_id = mysqli_real_escape_string($con, $_POST['auth_id'] ?? '');
if ($auth_id != $_SESSION['id']) {
    $_SESSION['error'] = "Unauthorized action.";
    header("Location: ../region_settings.php");
    exit(0);
}

// Helper function to handle QR image upload
function handleQrImageUpload($con, $upload_dir = '../../Uploads/qr_codes/') {
    if (!isset($_FILES['qr_image']) || $_FILES['qr_image']['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // No file uploaded
    }

    $file = $_FILES['qr_image'];
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
    $max_size = 5 * 1024 * 1024; // 5MB

    // Log upload error if any
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = "Upload error: " . $file['error'];
        error_log("region_settings.php - Upload error: " . $file['error']);
        return false;
    }

    // Validate file type
    if (!in_array($file['type'], $allowed_types)) {
        $_SESSION['error'] = "Invalid file type. Only JPG, JPEG, and PNG are allowed.";
        return false;
    }

    // Validate file size
    if ($file['size'] > $max_size) {
        $_SESSION['error'] = "File size exceeds 5MB limit.";
        return false;
    }

    // Create upload directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            $_SESSION['error'] = "Failed to create upload directory.";
            error_log("region_settings.php - Failed to create directory: " . $upload_dir);
            return false;
        }
    }

    // Generate unique filename
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $unique_filename = uniqid() . '.' . $file_extension;
    $upload_path = $upload_dir . $unique_filename;

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        error_log("region_settings.php - File uploaded successfully: " . $upload_path);
        return $upload_path;
    } else {
        $_SESSION['error'] = "Failed to upload QR image.";
        error_log("region_settings.php - Failed to upload file: " . $file['name'] . " to " . $upload_path);
        return false;
    }
}

// Helper function to delete QR image
function deleteQrImage($qr_path) {
    if (!empty($qr_path) && file_exists($qr_path)) {
        $deleted = unlink($qr_path);
        if ($deleted) {
            error_log("region_settings.php - Deleted QR image: " . $qr_path);
        } else {
            error_log("region_settings.php - Failed to delete QR image: " . $qr_path);
        }
        return $deleted;
    }
    return true;
}

// Add Region
if (isset($_POST['add_region'])) {
    $country = mysqli_real_escape_string($con, $_POST['country'] ?? '');
    $currency = mysqli_real_escape_string($con, $_POST['currency'] ?? '');
    $alt_currency = mysqli_real_escape_string($con, $_POST['alt_currency'] ?? '');
    $crypto = isset($_POST['crypto']) ? 1 : 0;
    $Channel = mysqli_real_escape_string($con, $_POST['Channel'] ?? '');
    $alt_channel = mysqli_real_escape_string($con, $_POST['alt_channel'] ?? '');
    $Channel_name = mysqli_real_escape_string($con, $_POST['Channel_name'] ?? '');
    $alt_ch_name = mysqli_real_escape_string($con, $_POST['alt_ch_name'] ?? '');
    $Channel_number = mysqli_real_escape_string($con, $_POST['Channel_number'] ?? '');
    $alt_ch_number = mysqli_real_escape_string($con, $_POST['alt_ch_number'] ?? '');
    $chnl_value = mysqli_real_escape_string($con, $_POST['chnl_value'] ?? '');
    $chnl_name_value = mysqli_real_escape_string($con, $_POST['chnl_name_value'] ?? '');
    $chnl_number_value = mysqli_real_escape_string($con, $_POST['chnl_number_value'] ?? '');
    $payment_amount = mysqli_real_escape_string($con, $_POST['payment_amount'] ?? '');
    $rate = mysqli_real_escape_string($con, $_POST['rate'] ?? '');
    $alt_rate = mysqli_real_escape_string($con, $_POST['alt_rate'] ?? '');

    // Handle QR image upload
    $qr_image = handleQrImageUpload($con);
    if ($qr_image === false) {
        header("Location: ../region_settings.php");
        exit(0);
    }

    // Validate inputs
    if (empty($country) || empty($currency) || empty($Channel) || empty($Channel_name) || empty($Channel_number) || empty($payment_amount) || empty($rate)) {
        if ($qr_image) deleteQrImage($qr_image);
        $_SESSION['error'] = "All required fields must be filled.";
        header("Location: ../region_settings.php");
        exit(0);
    }

    // Validate country
    if (!isset($countries) || !in_array($country, $countries)) {
        if ($qr_image) deleteQrImage($qr_image);
        $_SESSION['error'] = "Invalid country selected.";
        header("Location: ../region_settings.php");
        exit(0);
    }

    // Validate currency format (3-letter code)
    if (!preg_match('/^[A-Z]{3}$/', $currency)) {
        if ($qr_image) deleteQrImage($qr_image);
        $_SESSION['error'] = "Currency must be a 3-letter code.";
        header("Location: ../region_settings.php");
        exit(0);
    }

    // Validate alt_currency format if provided
    if (!empty($alt_currency) && !preg_match('/^[A-Z]{3}$/', $alt_currency)) {
        if ($qr_image) deleteQrImage($qr_image);
        $_SESSION['error'] = "Alternate currency must be a 3-letter code.";
        header("Location: ../region_settings.php");
        exit(0);
    }

    // Validate payment_amount and rate (must be positive numbers)
    if (!is_numeric($payment_amount) || $payment_amount <= 0) {
        if ($qr_image) deleteQrImage($qr_image);
        $_SESSION['error'] = "Payment amount must be a positive number.";
        header("Location: ../region_settings.php");
        exit(0);
    }
    if (!is_numeric($rate) || $rate <= 0) {
        if ($qr_image) deleteQrImage($qr_image);
        $_SESSION['error'] = "Rate must be a positive number.";
        header("Location: ../region_settings.php");
        exit(0);
    }

    // Validate alt_rate if provided
    if (!empty($alt_rate) && (!is_numeric($alt_rate) || $alt_rate <= 0)) {
        if ($qr_image) deleteQrImage($qr_image);
        $_SESSION['error'] = "Alternate rate must be a positive number or a valid format.";
        header("Location: ../region_settings.php");
        exit(0);
    }

    // Check if country already exists
    $check_query = "SELECT id FROM region_settings WHERE country = ? LIMIT 1";
    $stmt = $con->prepare($check_query);
    $stmt->bind_param("s", $country);
    $stmt->execute();
    $check_run = $stmt->get_result();
    if ($check_run->num_rows > 0) {
        if ($qr_image) deleteQrImage($qr_image);
        $_SESSION['error'] = "Region settings for this country already exist.";
        header("Location: ../region_settings.php");
        exit(0);
    }
    $stmt->close();

    // Insert new region using prepared statement
    $insert_query = "INSERT INTO region_settings (country, currency, alt_currency, crypto, Channel, alt_channel, Channel_name, alt_ch_name, Channel_number, alt_ch_number, chnl_value, chnl_name_value, chnl_number_value, qr_image, payment_amount, rate, alt_rate) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $con->prepare($insert_query);
    $stmt->bind_param("sssissssssssssdss", $country, $currency, $alt_currency, $crypto, $Channel, $alt_channel, $Channel_name, $alt_ch_name, $Channel_number, $alt_ch_number, $chnl_value, $chnl_name_value, $chnl_number_value, $qr_image, $payment_amount, $rate, $alt_rate);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Region added successfully.";
    } else {
        if ($qr_image) deleteQrImage($qr_image);
        $_SESSION['error'] = "Failed to add region: " . $stmt->error;
        error_log("region_settings.php - Insert query error: " . $stmt->error);
    }
    $stmt->close();
    header("Location: ../region_settings.php");
    exit(0);
}

// Update Region
if (isset($_POST['update_region'])) {
    $region_id = mysqli_real_escape_string($con, $_POST['region_id'] ?? '');
    $country = mysqli_real_escape_string($con, $_POST['country'] ?? '');
    $currency = mysqli_real_escape_string($con, $_POST['currency'] ?? '');
    $alt_currency = mysqli_real_escape_string($con, $_POST['alt_currency'] ?? '');
    $crypto = isset($_POST['crypto']) ? 1 : 0;
    $Channel = mysqli_real_escape_string($con, $_POST['Channel'] ?? '');
    $alt_channel = mysqli_real_escape_string($con, $_POST['alt_channel'] ?? '');
    $Channel_name = mysqli_real_escape_string($con, $_POST['Channel_name'] ?? '');
    $alt_ch_name = mysqli_real_escape_string($con, $_POST['alt_ch_name'] ?? '');
    $Channel_number = mysqli_real_escape_string($con, $_POST['Channel_number'] ?? '');
    $alt_ch_number = mysqli_real_escape_string($con, $_POST['alt_ch_number'] ?? '');
    $chnl_value = mysqli_real_escape_string($con, $_POST['chnl_value'] ?? '');
    $chnl_name_value = mysqli_real_escape_string($con, $_POST['chnl_name_value'] ?? '');
    $chnl_number_value = mysqli_real_escape_string($con, $_POST['chnl_number_value'] ?? '');
    $payment_amount = mysqli_real_escape_string($con, $_POST['payment_amount'] ?? '');
    $rate = mysqli_real_escape_string($con, $_POST['rate'] ?? '');
    $alt_rate = mysqli_real_escape_string($con, $_POST['alt_rate'] ?? '');

    // Fetch existing QR image path
    $existing_qr_query = "SELECT qr_image FROM region_settings WHERE id = ?";
    $stmt_existing = $con->prepare($existing_qr_query);
    $stmt_existing->bind_param("i", $region_id);
    $stmt_existing->execute();
    $existing_result = $stmt_existing->get_result();
    $existing_qr_image = null;
    if ($existing_row = $existing_result->fetch_assoc()) {
        $existing_qr_image = $existing_row['qr_image'];
    }
    $stmt_existing->close();

    // Handle QR image upload
    $new_qr_image = handleQrImageUpload($con);
    if ($new_qr_image === false) {
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }

    $qr_image_to_save = $new_qr_image ?? $existing_qr_image;

    // Validate inputs
    if (empty($region_id) || empty($country) || empty($currency) || empty($Channel) || empty($Channel_name) || empty($Channel_number) || empty($payment_amount) || empty($rate)) {
        if ($new_qr_image) deleteQrImage($new_qr_image);
        $_SESSION['error'] = "All required fields must be filled.";
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }

    // Validate country
    if (!isset($countries) || !in_array($country, $countries)) {
        if ($new_qr_image) deleteQrImage($new_qr_image);
        $_SESSION['error'] = "Invalid country selected.";
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }

    // Validate currency format
    if (!preg_match('/^[A-Z]{3}$/', $currency)) {
        if ($new_qr_image) deleteQrImage($new_qr_image);
        $_SESSION['error'] = "Currency must be a 3-letter code.";
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }

    // Validate alt_currency format if provided
    if (!empty($alt_currency) && !preg_match('/^[A-Z]{3}$/', $alt_currency)) {
        if ($new_qr_image) deleteQrImage($new_qr_image);
        $_SESSION['error'] = "Alternate currency must be a 3-letter code.";
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }

    // Validate payment_amount and rate
    if (!is_numeric($payment_amount) || $payment_amount <= 0) {
        if ($new_qr_image) deleteQrImage($new_qr_image);
        $_SESSION['error'] = "Payment amount must be a positive number.";
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }
    if (!is_numeric($rate) || $rate <= 0) {
        if ($new_qr_image) deleteQrImage($new_qr_image);
        $_SESSION['error'] = "Rate must be a positive number.";
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }

    // Validate alt_rate if provided
    if (!empty($alt_rate) && (!is_numeric($alt_rate) || $alt_rate <= 0)) {
        if ($new_qr_image) deleteQrImage($new_qr_image);
        $_SESSION['error'] = "Alternate rate must be a positive number or a valid format.";
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }

    // Check if country already exists for another record
    $check_query = "SELECT id FROM region_settings WHERE country = ? AND id != ? LIMIT 1";
    $stmt = $con->prepare($check_query);
    $stmt->bind_param("si", $country, $region_id);
    $stmt->execute();
    $check_run = $stmt->get_result();
    if ($check_run->num_rows > 0) {
        if ($new_qr_image) deleteQrImage($new_qr_image);
        $_SESSION['error'] = "Region settings for this country already exist.";
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }
    $stmt->close();

    // If new image uploaded, delete old one
    if ($new_qr_image && $existing_qr_image && $new_qr_image != $existing_qr_image) {
        deleteQrImage($existing_qr_image);
    }

    // Update region using prepared statement
    $update_query = "UPDATE region_settings SET 
                     country = ?, 
                     currency = ?, 
                     alt_currency = ?,
                     crypto = ?, 
                     Channel = ?, 
                     alt_channel = ?,
                     Channel_name = ?, 
                     alt_ch_name = ?,
                     Channel_number = ?, 
                     alt_ch_number = ?,
                     chnl_value = ?, 
                     chnl_name_value = ?, 
                     chnl_number_value = ?, 
                     qr_image = ?, 
                     payment_amount = ?, 
                     rate = ?, 
                     alt_rate = ? 
                     WHERE id = ?";
    $stmt = $con->prepare($update_query);
    $stmt->bind_param("sssissssssssssdssi", $country, $currency, $alt_currency, $crypto, $Channel, $alt_channel, $Channel_name, $alt_ch_name, $Channel_number, $alt_ch_number, $chnl_value, $chnl_name_value, $chnl_number_value, $qr_image_to_save, $payment_amount, $rate, $alt_rate, $region_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Region updated successfully.";
        header("Location: ../region_settings.php");
    } else {
        if ($new_qr_image) deleteQrImage($new_qr_image);
        $_SESSION['error'] = "Failed to update region: " . $stmt->error;
        error_log("region_settings.php - Update query error: " . $stmt->error);
        header("Location: ../edit-region.php?id=$region_id");
    }
    $stmt->close();
    exit(0);
}

// Delete Region
if (isset($_POST['delete'])) {
    $region_id = mysqli_real_escape_string($con, $_POST['delete']);
    
    // Fetch QR image path before deletion
    $qr_query = "SELECT qr_image FROM region_settings WHERE id = ?";
    $stmt_qr = $con->prepare($qr_query);
    $stmt_qr->bind_param("i", $region_id);
    $stmt_qr->execute();
    $qr_result = $stmt_qr->get_result();
    $qr_image = null;
    if ($qr_row = $qr_result->fetch_assoc()) {
        $qr_image = $qr_row['qr_image'];
    }
    $stmt_qr->close();

    $delete_query = "DELETE FROM region_settings WHERE id = ?";
    $stmt = $con->prepare($delete_query);
    $stmt->bind_param("i", $region_id);
    
    if ($stmt->execute()) {
        // Delete associated QR image
        deleteQrImage($qr_image);
        $_SESSION['success'] = "Region deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete region: " . $stmt->error;
        error_log("region_settings.php - Delete query error: " . $stmt->error);
    }
    $stmt->close();
    header("Location: ../region_settings.php");
    exit(0);
}

// Invalid request
$_SESSION['error'] = "Invalid request.";
header("Location: ../region_settings.php");
exit(0);
?>
