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
        $_SESSION['error'] = "All required fields must be filled.";
        // Clean up uploaded file if validation fails
        if ($qr_image_path && file_exists($qr_image_path)) {
            unlink($qr_image_path);
        }
        header("Location: ../region_settings.php");
        exit(0);
    }

    // Validate country
    if (!isset($countries) || !in_array($country, $countries)) {
        $_SESSION['error'] = "Invalid country selected.";
        // Clean up uploaded file if validation fails
        if ($qr_image_path && file_exists($qr_image_path)) {
            unlink($qr_image_path);
        }
        header("Location: ../region_settings.php");
        exit(0);
    }

    // Validate currency format (3-letter code)
    if (!preg_match('/^[A-Z]{3}$/', $currency)) {
        $_SESSION['error'] = "Currency must be a 3-letter code.";
        // Clean up uploaded file if validation fails
        if ($qr_image_path && file_exists($qr_image_path)) {
            unlink($qr_image_path);
        }
        header("Location: ../region_settings.php");
        exit(0);
    }

    // Validate alt_currency format if provided
    if (!empty($alt_currency) && !preg_match('/^[A-Z]{3}$/', $alt_currency)) {
        $_SESSION['error'] = "Alternate currency must be a 3-letter code.";
        // Clean up uploaded file if validation fails
        if ($qr_image_path && file_exists($qr_image_path)) {
            unlink($qr_image_path);
        }
        header("Location: ../region_settings.php");
        exit(0);
    }

    // Validate payment_amount and rate (must be positive numbers)
    if (!is_numeric($payment_amount) || $payment_amount <= 0) {
        $_SESSION['error'] = "Payment amount must be a positive number.";
        // Clean up uploaded file if validation fails
        if ($qr_image_path && file_exists($qr_image_path)) {
            unlink($qr_image_path);
        }
        header("Location: ../region_settings.php");
        exit(0);
    }
    if (!is_numeric($rate) || $rate <= 0) {
        $_SESSION['error'] = "Rate must be a positive number.";
        // Clean up uploaded file if validation fails
        if ($qr_image_path && file_exists($qr_image_path)) {
            unlink($qr_image_path);
        }
        header("Location: ../region_settings.php");
        exit(0);
    }

    // Validate alt_rate if provided
    if (!empty($alt_rate) && (!is_numeric($alt_rate) || $alt_rate <= 0)) {
        $_SESSION['error'] = "Alternate rate must be a positive number or a valid format.";
        // Clean up uploaded file if validation fails
        if ($qr_image_path && file_exists($qr_image_path)) {
            unlink($qr_image_path);
        }
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
        $_SESSION['error'] = "Region settings for this country already exist.";
        // Clean up uploaded file if validation fails
        if ($qr_image_path && file_exists($qr_image_path)) {
            unlink($qr_image_path);
        }
        header("Location: ../region_settings.php");
        exit(0);
    }
    $stmt->close();

    // Insert new region using prepared statement (now includes qr_image)
    $insert_query = "INSERT INTO region_settings (country, currency, alt_currency, crypto, Channel, alt_channel, Channel_name, alt_ch_name, Channel_number, alt_ch_number, chnl_value, chnl_name_value, chnl_number_value, payment_amount, rate, alt_rate, qr_image) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $con->prepare($insert_query);
    $stmt->bind_param("sssisssssssssdsds", $country, $currency, $alt_currency, $crypto, $Channel, $alt_channel, $Channel_name, $alt_ch_name, $Channel_number, $alt_ch_number, $chnl_value, $chnl_name_value, $chnl_number_value, $payment_amount, $rate, $alt_rate, $qr_image_path);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Region added successfully.";
    } else {
        $_SESSION['error'] = "Failed to add region: " . $stmt->error;
        error_log("region_settings.php - Insert query error: " . $stmt->error);
        // Clean up uploaded file if insert fails
        if ($qr_image_path && file_exists($qr_image_path)) {
            unlink($qr_image_path);
        }
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

    // Fetch existing region data for old qr_image
    $fetch_query = "SELECT qr_image FROM region_settings WHERE id = ?";
    $fetch_stmt = $con->prepare($fetch_query);
    $fetch_stmt->bind_param("i", $region_id);
    $fetch_stmt->execute();
    $fetch_result = $fetch_stmt->get_result();
    $existing_region = $fetch_result->fetch_assoc();
    $old_qr_image = $existing_region['qr_image'] ?? null;
    $fetch_stmt->close();

    // Handle QR Image Upload for Update
    $qr_image_path = $old_qr_image; // Keep existing if no new upload
    $uploaded_new = false;
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
        $uploaded_new = true;

        if (!move_uploaded_file($file_tmp, $qr_image_path)) {
            $_SESSION['error'] = "Failed to upload QR image.";
            header("Location: ../edit-region.php?id=$region_id");
            exit(0);
        }

        // Optional: Delete old image if exists
        if (!empty($old_qr_image) && file_exists($old_qr_image)) {
            unlink($old_qr_image);
        }
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
        $_SESSION['error'] = "All required fields must be filled.";
        // Clean up new uploaded file if validation fails
        if ($uploaded_new && $qr_image_path && file_exists($qr_image_path)) {
            unlink($qr_image_path);
        }
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }

    // Validate country
    if (!isset($countries) || !in_array($country, $countries)) {
        $_SESSION['error'] = "Invalid country selected.";
        // Clean up new uploaded file if validation fails
        if ($uploaded_new && $qr_image_path && file_exists($qr_image_path)) {
            unlink($qr_image_path);
        }
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }

    // Validate currency format
    if (!preg_match('/^[A-Z]{3}$/', $currency)) {
        $_SESSION['error'] = "Currency must be a 3-letter code.";
        // Clean up new uploaded file if validation fails
        if ($uploaded_new && $qr_image_path && file_exists($qr_image_path)) {
            unlink($qr_image_path);
        }
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }

    // Validate alt_currency format if provided
    if (!empty($alt_currency) && !preg_match('/^[A-Z]{3}$/', $alt_currency)) {
        $_SESSION['error'] = "Alternate currency must be a 3-letter code.";
        // Clean up new uploaded file if validation fails
        if ($uploaded_new && $qr_image_path && file_exists($qr_image_path)) {
            unlink($qr_image_path);
        }
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }

    // Validate payment_amount and rate
    if (!is_numeric($payment_amount) || $payment_amount <= 0) {
        $_SESSION['error'] = "Payment amount must be a positive number.";
        // Clean up new uploaded file if validation fails
        if ($uploaded_new && $qr_image_path && file_exists($qr_image_path)) {
            unlink($qr_image_path);
        }
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }
    if (!is_numeric($rate) || $rate <= 0) {
        $_SESSION['error'] = "Rate must be a positive number.";
        // Clean up new uploaded file if validation fails
        if ($uploaded_new && $qr_image_path && file_exists($qr_image_path)) {
            unlink($qr_image_path);
        }
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }

    // Validate alt_rate if provided
    if (!empty($alt_rate) && (!is_numeric($alt_rate) || $alt_rate <= 0)) {
        $_SESSION['error'] = "Alternate rate must be a positive number or a valid format.";
        // Clean up new uploaded file if validation fails
        if ($uploaded_new && $qr_image_path && file_exists($qr_image_path)) {
            unlink($qr_image_path);
        }
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
        $_SESSION['error'] = "Region settings for this country already exist.";
        // Clean up new uploaded file if validation fails
        if ($uploaded_new && $qr_image_path && file_exists($qr_image_path)) {
            unlink($qr_image_path);
        }
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }
    $stmt->close();

    // Update region using prepared statement (now includes qr_image)
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
    $stmt->bind_param("sssisssssssssdsdsi", $country, $currency, $alt_currency, $crypto, $Channel, $alt_channel, $Channel_name, $alt_ch_name, $Channel_number, $alt_ch_number, $chnl_value, $chnl_name_value, $chnl_number_value, $payment_amount, $rate, $alt_rate, $qr_image_path, $region_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Region updated successfully.";
        header("Location: ../region_settings.php");
    } else {
        $_SESSION['error'] = "Failed to update region: " . $stmt->error;
        error_log("region_settings.php - Update query error: " . $stmt->error);
        // Clean up new uploaded file if update fails
        if ($uploaded_new && $qr_image_path && file_exists($qr_image_path)) {
            unlink($qr_image_path);
        }
        header("Location: ../edit-region.php?id=$region_id");
    }
    $stmt->close();
    exit(0);
}

// Delete Region
if (isset($_POST['delete'])) {
    $region_id = mysqli_real_escape_string($con, $_POST['region_id'] ?? ''); // Fixed: Use region_id instead of delete value

    // Fetch qr_image before deletion
    $fetch_query = "SELECT qr_image FROM region_settings WHERE id = ?";
    $fetch_stmt = $con->prepare($fetch_query);
    $fetch_stmt->bind_param("i", $region_id);
    $fetch_stmt->execute();
    $fetch_result = $fetch_stmt->get_result();
    if ($fetch_row = $fetch_result->fetch_assoc()) {
        $qr_image_to_delete = $fetch_row['qr_image'];
    }
    $fetch_stmt->close();

    $delete_query = "DELETE FROM region_settings WHERE id = ?";
    $stmt = $con->prepare($delete_query);
    $stmt->bind_param("i", $region_id);
    
    if ($stmt->execute()) {
        // Delete associated QR image if exists
        if (!empty($qr_image_to_delete) && file_exists($qr_image_to_delete)) {
            unlink($qr_image_to_delete);
            error_log("region_settings.php - Deleted QR image: $qr_image_to_delete");
        }
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
