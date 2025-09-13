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

    // Handle QR Image Upload for Add
    $qr_image_path = null;
    if (isset($_FILES['qr_image']) && $_FILES['qr_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['qr_image']['tmp_name'];
        $file_name = $_FILES['qr_image']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $file_type = mime_content_type($file_tmp);
        $allowed_ext = ['jpg', 'jpeg', 'png'];
        $allowed_types = ['image/jpeg', 'image/png'];

        // Validate file type
        if (!in_array($file_ext, $allowed_ext) || !in_array($file_type, $allowed_types)) {
            $_SESSION['error'] = "Invalid QR image type. Only JPG, JPEG, and PNG are allowed.";
            header("Location: ../region_settings.php");
            exit(0);
        }

        // Validate file size (5MB limit)
        if ($_FILES['qr_image']['size'] > 5 * 1024 * 1024) {
            $_SESSION['error'] = "QR image size exceeds 5MB limit.";
            header("Location: ../region_settings.php");
            exit(0);
        }

        // Set up upload directory (create qr_codes subdir if needed)
        $upload_dir = '../Uploads/qr_codes/';
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                $_SESSION['error'] = "Failed to create QR upload directory.";
                header("Location: ../region_settings.php");
                exit(0);
            }
        }

        if (!is_writable($upload_dir)) {
            $_SESSION['error'] = "QR upload directory is not writable.";
            header("Location: ../region_settings.php");
            exit(0);
        }

        // Generate unique filename and move file
        $new_file_name = uniqid() . '.' . $file_ext;
        $qr_image_path = $upload_dir . $new_file_name;

        if (!move_uploaded_file($file_tmp, $qr_image_path)) {
            $_SESSION['error'] = "Failed to upload QR image.";
            header("Location: ../region_settings.php");
            exit(0);
        }

        error_log("region_settings.php - QR image uploaded successfully: $qr_image_path");
    } elseif (isset($_FILES['qr_image']) && $_FILES['qr_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Handle upload errors
        $upload_error_codes = [
            UPLOAD_ERR_INI_SIZE => "File exceeds server's maximum file size.",
            UPLOAD_ERR_FORM_SIZE => "File exceeds form's maximum file size.",
            UPLOAD_ERR_PARTIAL => "File was only partially uploaded.",
            UPLOAD_ERR_NO_TMP_DIR => "Missing temporary folder.",
            UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk.",
            UPLOAD_ERR_EXTENSION => "A PHP extension stopped the file upload."
        ];
        $error_message = $upload_error_codes[$_FILES['qr_image']['error']] ?? "Unknown upload error.";
        $_SESSION['error'] = "QR Image Upload Error: $error_message";
        header("Location: ../region_settings.php");
        exit(0);
    }

    // Validate inputs
    if (empty($country) || empty($currency) || empty($Channel) || empty($Channel_name) || empty($Channel_number) || empty($payment_amount) || empty($rate)) {
        // Clean up uploaded file if validation fails
        if ($qr_image_path && file_exists($qr_image_path)) {
            unlink($qr_image_path);
        }
        $_SESSION['error'] = "All required fields must be filled.";
        header("Location: ../region_settings.php");
        exit(0);
    }

    // Validate country
    if (!isset($countries) || !in_array($country, $countries)) {
        if ($qr_image_path && file_exists($qr_image_path)) {
            unlink($qr_image_path);
        }
        $_SESSION['error'] = "Invalid country selected.";
        header("Location: ../region_settings.php");
        exit(0);
    }

    // Validate currency format (3-letter code)
    if (!preg_match('/^[A-Z]{3}$/', $currency)) {
        if ($qr_image_path && file_exists($qr_image_path)) {
            unlink($qr_image_path);
        }
        $_SESSION['error'] = "Currency must be a 3-letter code.";
        header("Location: ../region_settings.php");
        exit(0);
    }

    // Validate alt_currency format if provided
    if (!empty($alt_currency) && !preg_match('/^[A-Z]{3}$/', $alt_currency)) {
        if ($qr_image_path && file_exists($qr_image_path)) {
            unlink($qr_image_path);
        }
        $_SESSION['error'] = "Alternate currency must be a 3-letter code.";
        header("Location: ../region_settings.php");
        exit(0);
    }

    // Validate payment_amount and rate (must be positive numbers)
    if (!is_numeric($payment_amount) || $payment_amount <= 0) {
        if ($qr_image_path && file_exists($qr_image_path)) {
            unlink($qr_image_path);
        }
        $_SESSION['error'] = "Payment amount must be a positive number.";
        header("Location: ../region_settings.php");
        exit(0);
    }
    if (!is_numeric($rate) || $rate <= 0) {
        if ($qr_image_path && file_exists($qr_image_path)) {
            unlink($qr_image_path);
        }
        $_SESSION['error'] = "Rate must be a positive number.";
        header("Location: ../region_settings.php");
        exit(0);
    }

    // Validate alt_rate if provided
    if (!empty($alt_rate) && (!is_numeric($alt_rate) || $alt_rate <= 0)) {
        if ($qr_image_path && file_exists($qr_image_path)) {
            unlink($qr_image_path);
        }
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
        if ($qr_image_path && file_exists($qr_image_path)) {
            unlink($qr_image_path);
        }
        $_SESSION['error'] = "Region settings for this country already exist.";
        header("Location: ../region_settings.php");
        exit(0);
    }
    $stmt->close();

    // Insert new region using prepared statement, including qr_image
    $insert_query = "INSERT INTO region_settings (country, currency, alt_currency, crypto, Channel, alt_channel, Channel_name, alt_ch_name, Channel_number, alt_ch_number, chnl_value, chnl_name_value, chnl_number_value, payment_amount, rate, alt_rate, qr_image) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $con->prepare($insert_query);
    $stmt->bind_param("sssisssssssssds s", $country, $currency, $alt_currency, $crypto, $Channel, $alt_channel, $Channel_name, $alt_ch_name, $Channel_number, $alt_ch_number, $chnl_value, $chnl_name_value, $chnl_number_value, $payment_amount, $rate, $alt_rate, $qr_image_path);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Region added successfully.";
        error_log("region_settings.php - Region added successfully with ID: " . $con->insert_id . ", QR image: " . ($qr_image_path ?? 'none'));
    } else {
        if ($qr_image_path && file_exists($qr_image_path)) {
            unlink($qr_image_path);
        }
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

    // Fetch existing qr_image for potential deletion
    $existing_query = "SELECT qr_image FROM region_settings WHERE id = ?";
    $existing_stmt = $con->prepare($existing_query);
    $existing_stmt->bind_param("i", $region_id);
    $existing_stmt->execute();
    $existing_result = $existing_stmt->get_result();
    $existing_data = $existing_result->fetch_assoc();
    $existing_qr_image = $existing_data['qr_image'] ?? null;
    $existing_stmt->close();

    // Handle QR Image Upload for Update
    $qr_image_path = $existing_qr_image; // Keep existing if no new upload
    $delete_old = false;
    if (isset($_FILES['qr_image']) && $_FILES['qr_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['qr_image']['tmp_name'];
        $file_name = $_FILES['qr_image']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $file_type = mime_content_type($file_tmp);
        $allowed_ext = ['jpg', 'jpeg', 'png'];
        $allowed_types = ['image/jpeg', 'image/png'];

        // Validate file type
        if (!in_array($file_ext, $allowed_ext) || !in_array($file_type, $allowed_types)) {
            $_SESSION['error'] = "Invalid QR image type. Only JPG, JPEG, and PNG are allowed.";
            header("Location: ../edit-region.php?id=$region_id");
            exit(0);
        }

        // Validate file size (5MB limit)
        if ($_FILES['qr_image']['size'] > 5 * 1024 * 1024) {
            $_SESSION['error'] = "QR image size exceeds 5MB limit.";
            header("Location: ../edit-region.php?id=$region_id");
            exit(0);
        }

        // Set up upload directory (create qr_codes subdir if needed)
        $upload_dir = '../Uploads/qr_codes/';
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                $_SESSION['error'] = "Failed to create QR upload directory.";
                header("Location: ../edit-region.php?id=$region_id");
                exit(0);
            }
        }

        if (!is_writable($upload_dir)) {
            $_SESSION['error'] = "QR upload directory is not writable.";
            header("Location: ../edit-region.php?id=$region_id");
            exit(0);
        }

        // Generate unique filename and move file
        $new_file_name = uniqid() . '.' . $file_ext;
        $qr_image_path = $upload_dir . $new_file_name;

        if (!move_uploaded_file($file_tmp, $qr_image_path)) {
            $_SESSION['error'] = "Failed to upload QR image.";
            header("Location: ../edit-region.php?id=$region_id");
            exit(0);
        }

        $delete_old = true;
        error_log("region_settings.php - QR image uploaded successfully for update: $qr_image_path");
    } elseif (isset($_FILES['qr_image']) && $_FILES['qr_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Handle upload errors
        $upload_error_codes = [
            UPLOAD_ERR_INI_SIZE => "File exceeds server's maximum file size.",
            UPLOAD_ERR_FORM_SIZE => "File exceeds form's maximum file size.",
            UPLOAD_ERR_PARTIAL => "File was only partially uploaded.",
            UPLOAD_ERR_NO_TMP_DIR => "Missing temporary folder.",
            UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk.",
            UPLOAD_ERR_EXTENSION => "A PHP extension stopped the file upload."
        ];
        $error_message = $upload_error_codes[$_FILES['qr_image']['error']] ?? "Unknown upload error.";
        $_SESSION['error'] = "QR Image Upload Error: $error_message";
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }

    // Validate inputs
    if (empty($region_id) || empty($country) || empty($currency) || empty($Channel) || empty($Channel_name) || empty($Channel_number) || empty($payment_amount) || empty($rate)) {
        if ($qr_image_path && file_exists($qr_image_path) && $delete_old) {
            unlink($qr_image_path);
        }
        $_SESSION['error'] = "All required fields must be filled.";
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }

    // Validate country
    if (!isset($countries) || !in_array($country, $countries)) {
        if ($qr_image_path && file_exists($qr_image_path) && $delete_old) {
            unlink($qr_image_path);
        }
        $_SESSION['error'] = "Invalid country selected.";
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }

    // Validate currency format
    if (!preg_match('/^[A-Z]{3}$/', $currency)) {
        if ($qr_image_path && file_exists($qr_image_path) && $delete_old) {
            unlink($qr_image_path);
        }
        $_SESSION['error'] = "Currency must be a 3-letter code.";
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }

    // Validate alt_currency format if provided
    if (!empty($alt_currency) && !preg_match('/^[A-Z]{3}$/', $alt_currency)) {
        if ($qr_image_path && file_exists($qr_image_path) && $delete_old) {
            unlink($qr_image_path);
        }
        $_SESSION['error'] = "Alternate currency must be a 3-letter code.";
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }

    // Validate payment_amount and rate
    if (!is_numeric($payment_amount) || $payment_amount <= 0) {
        if ($qr_image_path && file_exists($qr_image_path) && $delete_old) {
            unlink($qr_image_path);
        }
        $_SESSION['error'] = "Payment amount must be a positive number.";
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }
    if (!is_numeric($rate) || $rate <= 0) {
        if ($qr_image_path && file_exists($qr_image_path) && $delete_old) {
            unlink($qr_image_path);
        }
        $_SESSION['error'] = "Rate must be a positive number.";
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }

    // Validate alt_rate if provided
    if (!empty($alt_rate) && (!is_numeric($alt_rate) || $alt_rate <= 0)) {
        if ($qr_image_path && file_exists($qr_image_path) && $delete_old) {
            unlink($qr_image_path);
        }
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
        if ($qr_image_path && file_exists($qr_image_path) && $delete_old) {
            unlink($qr_image_path);
        }
        $_SESSION['error'] = "Region settings for this country already exist.";
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }
    $stmt->close();

    // Delete old QR image if a new one was uploaded
    if ($delete_old && !empty($existing_qr_image) && file_exists($existing_qr_image)) {
        unlink($existing_qr_image);
        error_log("region_settings.php - Old QR image deleted: $existing_qr_image");
    }

    // Update region using prepared statement, including qr_image
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
                     payment_amount = ?, 
                     rate = ?, 
                     alt_rate = ?, 
                     qr_image = ?
                     WHERE id = ?";
    $stmt = $con->prepare($update_query);
    $stmt->bind_param("sssisssssssssdssi", $country, $currency, $alt_currency, $crypto, $Channel, $alt_channel, $Channel_name, $alt_ch_name, $Channel_number, $alt_ch_number, $chnl_value, $chnl_name_value, $chnl_number_value, $payment_amount, $rate, $alt_rate, $qr_image_path, $region_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Region updated successfully.";
        error_log("region_settings.php - Region updated successfully with ID: $region_id, QR image: " . ($qr_image_path ?? 'none'));
        header("Location: ../region_settings.php");
    } else {
        if ($qr_image_path && file_exists($qr_image_path) && $delete_old) {
            unlink($qr_image_path);
        }
        $_SESSION['error'] = "Failed to update region: " . $stmt->error;
        error_log("region_settings.php - Update query error: " . $stmt->error);
        header("Location: ../edit-region.php?id=$region_id");
    }
    $stmt->close();
    exit(0);
}

// Delete Region
if (isset($_POST['delete'])) {
    $region_id = mysqli_real_escape_string($con, $_POST['region_id'] ?? ''); // Fixed: Use 'region_id' from form

    // Fetch qr_image before deletion
    $delete_region_query = "SELECT qr_image FROM region_settings WHERE id = ?";
    $delete_stmt = $con->prepare($delete_region_query);
    $delete_stmt->bind_param("i", $region_id);
    $delete_stmt->execute();
    $delete_result = $delete_stmt->get_result();
    if ($delete_row = $delete_result->fetch_assoc()) {
        if (!empty($delete_row['qr_image']) && file_exists($delete_row['qr_image'])) {
            unlink($delete_row['qr_image']);
            error_log("region_settings.php - QR image deleted during region delete: " . $delete_row['qr_image']);
        }
    }
    $delete_stmt->close();

    // Delete the region
    $delete_query = "DELETE FROM region_settings WHERE id = ?";
    $stmt = $con->prepare($delete_query);
    $stmt->bind_param("i", $region_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Region deleted successfully.";
        error_log("region_settings.php - Region deleted successfully with ID: $region_id");
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
