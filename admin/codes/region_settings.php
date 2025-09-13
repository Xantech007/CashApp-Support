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

// Function to handle QR upload (reusable for add/update)
function handleQrUpload($con, $redirect_url, $cleanup_on_fail = true) {
    $qr_image_path = null;
    $upload_dir = __DIR__ . '/../Uploads/qr_codes/';  // Absolute from codes/: e.g., /site/admin/Uploads/qr_codes/
    
    error_log("QR Upload - Starting. Upload dir (absolute): " . $upload_dir);
    error_log("QR Upload - FILES: " . print_r($_FILES['qr_image'] ?? 'No file key', true));

    if (isset($_FILES['qr_image']) && $_FILES['qr_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['qr_image']['tmp_name'];
        $file_name = $_FILES['qr_image']['name'];
        $file_size = $_FILES['qr_image']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $file_type = mime_content_type($file_tmp);
        
        error_log("QR Upload - File details: name=$file_name, size=$file_size, ext=$file_ext, type=$file_type, tmp=$file_tmp");

        $allowed_ext = ['jpg', 'jpeg', 'png'];
        $allowed_types = ['image/jpeg', 'image/png'];

        if (!in_array($file_ext, $allowed_ext) || !in_array($file_type, $allowed_types)) {
            $_SESSION['error'] = "Invalid QR image type. Only JPG, JPEG, and PNG are allowed.";
            error_log("QR Upload - Invalid type: $file_type / $file_ext");
            header("Location: $redirect_url");
            exit(0);
        }

        if ($file_size > 5 * 1024 * 1024) {
            $_SESSION['error'] = "QR image size exceeds 5MB limit.";
            error_log("QR Upload - Size too large: $file_size");
            header("Location: $redirect_url");
            exit(0);
        }

        // Ensure dir exists and writable
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                $_SESSION['error'] = "Failed to create QR upload directory.";
                error_log("QR Upload - mkdir failed: $upload_dir");
                header("Location: $redirect_url");
                exit(0);
            }
            error_log("QR Upload - Created dir: $upload_dir");
        }

        if (!is_writable($upload_dir)) {
            $_SESSION['error'] = "QR upload directory is not writable (check permissions).";
            error_log("QR Upload - Dir not writable: $upload_dir");
            header("Location: $redirect_url");
            exit(0);
        }

        // Generate unique filename
        $new_file_name = uniqid() . '.' . $file_ext;
        $qr_image_path = $upload_dir . $new_file_name;
        error_log("QR Upload - Attempting move to: $qr_image_path");

        // Move file
        if (move_uploaded_file($file_tmp, $qr_image_path)) {
            error_log("QR Upload - SUCCESS: File moved to $qr_image_path");
            chmod($qr_image_path, 0644);  // Set readable perms
            return $qr_image_path;
        } else {
            $_SESSION['error'] = "Failed to upload QR image (move failed - check logs).";
            error_log("QR Upload - move_uploaded_file FAILED. Tmp: $file_tmp, Dest: $qr_image_path. PHP errors: " . print_r(error_get_last(), true));
            if ($cleanup_on_fail) {
                // No file to clean, but log
            }
            header("Location: $redirect_url");
            exit(0);
        }
    } elseif (isset($_FILES['qr_image']) && $_FILES['qr_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Handle other upload errors
        $upload_error_codes = [
            UPLOAD_ERR_INI_SIZE => "File exceeds server's maximum file size.",
            UPLOAD_ERR_FORM_SIZE => "File exceeds form's maximum file size.",
            UPLOAD_ERR_PARTIAL => "File was only partially uploaded.",
            UPLOAD_ERR_NO_TMP_DIR => "Missing temporary folder.",
            UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk.",
            UPLOAD_ERR_EXTENSION => "A PHP extension stopped the file upload."
        ];
        $error_message = $upload_error_codes[$_FILES['qr_image']['error']] ?? "Unknown upload error (code: {$_FILES['qr_image']['error']}).";
        $_SESSION['error'] = "QR Image Upload Error: $error_message";
        error_log("QR Upload - Error code {$_FILES['qr_image']['error']}: $error_message");
        header("Location: $redirect_url");
        exit(0);
    } else {
        error_log("QR Upload - No file uploaded (optional, skipping).");
        return null;
    }
}

// Add Region
if (isset($_POST['add_region'])) {
    // ... (keep all your existing variable assignments and validations as-is)

    // Handle QR after basic validation but before duplicate check
    $qr_image_path = handleQrUpload($con, "../region_settings.php", true /* cleanup */);

    // ... (keep existing validations, but add cleanup for QR if fail)
    // In each validation failure block, add:
    // if ($qr_image_path && file_exists($qr_image_path)) { unlink($qr_image_path); }

    // For duplicate check failure:
    if ($check_run->num_rows > 0) {
        $_SESSION['error'] = "Region settings for this country already exist.";
        if ($qr_image_path && file_exists($qr_image_path)) { unlink($qr_image_path); }
        header("Location: ../region_settings.php");
        exit(0);
    }

    // Insert (updated query)
    $insert_query = "INSERT INTO region_settings (country, currency, alt_currency, crypto, Channel, alt_channel, Channel_name, alt_ch_name, Channel_number, alt_ch_number, chnl_value, chnl_name_value, chnl_number_value, payment_amount, rate, alt_rate, qr_image) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $con->prepare($insert_query);
    $stmt->bind_param("sssisssssssssdsds", $country, $currency, $alt_currency, $crypto, $Channel, $alt_channel, $Channel_name, $alt_ch_name, $Channel_number, $alt_ch_number, $chnl_value, $chnl_name_value, $chnl_number_value, $payment_amount, $rate, $alt_rate, $qr_image_path);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Region added successfully.";
        error_log("Add Region - SUCCESS with QR: " . ($qr_image_path ?? 'NULL'));
    } else {
        $_SESSION['error'] = "Failed to add region: " . $stmt->error;
        error_log("Add Region - Insert error: " . $stmt->error);
        if ($qr_image_path && file_exists($qr_image_path)) { unlink($qr_image_path); }
    }
    $stmt->close();
    header("Location: ../region_settings.php");
    exit(0);
}

// Update Region
if (isset($_POST['update_region'])) {
    // ... (keep existing variable assignments)

    // Fetch old QR
    $fetch_query = "SELECT qr_image FROM region_settings WHERE id = ?";
    $fetch_stmt = $con->prepare($fetch_query);
    $fetch_stmt->bind_param("i", $region_id);
    $fetch_stmt->execute();
    $fetch_result = $fetch_stmt->get_result();
    $existing_region = $fetch_result->fetch_assoc();
    $old_qr_image = $existing_region['qr_image'] ?? null;
    $fetch_stmt->close();

    error_log("Update Region - Old QR: " . ($old_qr_image ?? 'NULL'));

    // Handle QR
    $qr_image_path = $old_qr_image;  // Default to old
    $new_uploaded = false;
    $temp_path = handleQrUpload($con, "../edit-region.php?id=$region_id", true /* cleanup new if fail */);
    if ($temp_path !== null) {
        $qr_image_path = $temp_path;
        $new_uploaded = true;
        // Delete old if new
        if ($old_qr_image && file_exists($old_qr_image)) {
            unlink($old_qr_image);
            error_log("Update Region - Deleted old QR: $old_qr_image");
        }
    }

    // ... (keep existing validations, add cleanup for new upload if fail)
    // In each failure: if ($new_uploaded && $qr_image_path && file_exists($qr_image_path)) { unlink($qr_image_path); }

    // For duplicate check failure:
    if ($check_run->num_rows > 0) {
        $_SESSION['error'] = "Region settings for this country already exist.";
        if ($new_uploaded && $qr_image_path && file_exists($qr_image_path)) { unlink($qr_image_path); }
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }

    // Update (updated query)
    $update_query = "UPDATE region_settings SET 
                     country = ?, currency = ?, alt_currency = ?, crypto = ?, Channel = ?, alt_channel = ?, 
                     Channel_name = ?, alt_ch_name = ?, Channel_number = ?, alt_ch_number = ?, 
                     chnl_value = ?, chnl_name_value = ?, chnl_number_value = ?, 
                     payment_amount = ?, rate = ?, alt_rate = ?, qr_image = ? 
                     WHERE id = ?";
    $stmt = $con->prepare($update_query);
    $stmt->bind_param("sssisssssssssdsdsi", $country, $currency, $alt_currency, $crypto, $Channel, $alt_channel, $Channel_name, $alt_ch_name, $Channel_number, $alt_ch_number, $chnl_value, $chnl_name_value, $chnl_number_value, $payment_amount, $rate, $alt_rate, $qr_image_path, $region_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Region updated successfully.";
        error_log("Update Region - SUCCESS with QR: $qr_image_path");
        header("Location: ../region_settings.php");
    } else {
        $_SESSION['error'] = "Failed to update region: " . $stmt->error;
        error_log("Update Region - Update error: " . $stmt->error);
        if ($new_uploaded && $qr_image_path && file_exists($qr_image_path)) { unlink($qr_image_path); }
        header("Location: ../edit-region.php?id=$region_id");
    }
    $stmt->close();
    exit(0);
}

// Delete Region (keep as-is, but with fetch for QR delete - already in your code)
if (isset($_POST['delete'])) {
    $region_id = mysqli_real_escape_string($con, $_POST['region_id'] ?? '');

    // Fetch qr_image before deletion
    $fetch_query = "SELECT qr_image FROM region_settings WHERE id = ?";
    $fetch_stmt = $con->prepare($fetch_query);
    $fetch_stmt->bind_param("i", $region_id);
    $fetch_stmt->execute();
    $fetch_result = $fetch_stmt->get_result();
    $qr_image_to_delete = null;
    if ($fetch_row = $fetch_result->fetch_assoc()) {
        $qr_image_to_delete = $fetch_row['qr_image'];
    }
    $fetch_stmt->close();

    $delete_query = "DELETE FROM region_settings WHERE id = ?";
    $stmt = $con->prepare($delete_query);
    $stmt->bind_param("i", $region_id);
    
    if ($stmt->execute()) {
        if (!empty($qr_image_to_delete) && file_exists($qr_image_to_delete)) {
            if (unlink($qr_image_to_delete)) {
                error_log("Delete Region - Deleted QR: $qr_image_to_delete");
            } else {
                error_log("Delete Region - Failed to delete QR file: $qr_image_to_delete");
            }
        }
        $_SESSION['success'] = "Region deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete region: " . $stmt->error;
        error_log("Delete Region - Query error: " . $stmt->error);
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
