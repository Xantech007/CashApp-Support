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

// Enhanced function to handle QR upload with diagnostics
function handleQrUpload($redirect_url, $old_path = null, $is_update = false) {
    global $con; // Not used here, but for consistency
    $qr_image_path = $old_path; // Default to old for updates
    $new_uploaded = false;
    $upload_dir_abs = __DIR__ . '/../Uploads/qr_codes/';  // Absolute: /site/admin/Uploads/qr_codes/
    $upload_dir_rel = 'admin/Uploads/qr_codes/';  // Relative for DB storage (for <img src> from root)
    $php_user = get_current_user();  // e.g., 'www-data'
    $uid = posix_getuid();
    $user_info = posix_getpwuid($uid);
    $php_username = $user_info['name'] ?? 'unknown';

    error_log("QR Upload - PHP User: $php_username (UID: $uid). Mode: " . ($is_update ? 'UPDATE' : 'ADD'));
    error_log("QR Upload - Target Dir ABS: $upload_dir_abs | REL for DB: $upload_dir_rel");
    error_log("QR Upload - $_FILES['qr_image']: " . print_r($_FILES['qr_image'] ?? [], true));

    if (isset($_FILES['qr_image']) && $_FILES['qr_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['qr_image']['tmp_name'];
        $file_name = $_FILES['qr_image']['name'];
        $file_size = $_FILES['qr_image']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $file_type = function_exists('mime_content_type') ? mime_content_type($file_tmp) : 'unknown';

        error_log("QR Upload - File: name=$file_name, size=$file_size, ext=$file_ext, type=$file_type, tmp_abs=$file_tmp");

        $allowed_ext = ['jpg', 'jpeg', 'png'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];

        if (!in_array($file_ext, $allowed_ext) || !in_array($file_type, $allowed_types)) {
            $_SESSION['error'] = "Invalid QR image type. Only JPG, JPEG, PNG allowed.";
            error_log("QR Upload - FAIL: Invalid type $file_type / $file_ext");
            header("Location: $redirect_url");
            exit(0);
        }

        if ($file_size > 5 * 1024 * 1024) {
            $_SESSION['error'] = "QR image size >5MB.";
            error_log("QR Upload - FAIL: Size $file_size too large");
            header("Location: $redirect_url");
            exit(0);
        }

        // Dir setup & test write
        if (!is_dir($upload_dir_abs)) {
            if (!mkdir($upload_dir_abs, 0755, true)) {
                $_SESSION['error'] = "Failed to create QR dir.";
                error_log("QR Upload - FAIL: mkdir $upload_dir_abs");
                header("Location: $redirect_url");
                exit(0);
            }
            error_log("QR Upload - Created dir $upload_dir_abs");
        }

        // Test write as PHP user
        $test_file = $upload_dir_abs . 'test_write_' . uniqid() . '.txt';
        $test_fd = fopen($test_file, 'w');
        if (!$test_fd) {
            $_SESSION['error'] = "PHP user ($php_username) can't write to dir (perms/owner issue).";
            error_log("QR Upload - FAIL: Write test failed for $php_username on $upload_dir_abs");
            header("Location: $redirect_url");
            exit(0);
        }
        fwrite($test_fd, 'test');
        fclose($test_fd);
        unlink($test_file);  // Clean test
        error_log("QR Upload - Write test PASSED for $php_username");

        if (!is_writable($upload_dir_abs)) {
            $_SESSION['error'] = "Dir not writable by PHP user ($php_username).";
            error_log("QR Upload - FAIL: is_writable false on $upload_dir_abs");
            header("Location: $redirect_url");
            exit(0);
        }

        // Move file
        $new_file_name = uniqid() . '.' . $file_ext;
        $qr_image_path_abs = $upload_dir_abs . $new_file_name;
        $qr_image_path_rel = $upload_dir_rel . $new_file_name;  // For DB
        error_log("QR Upload - Moving from $file_tmp to $qr_image_path_abs");

        if (move_uploaded_file($file_tmp, $qr_image_path_abs)) {
            chmod($qr_image_path_abs, 0644);
            $new_uploaded = true;
            error_log("QR Upload - SUCCESS: Moved to $qr_image_path_abs | DB path: $qr_image_path_rel");
            if ($is_update && $old_path && file_exists($old_path)) {
                unlink($old_path);
                error_log("QR Upload - Deleted old: $old_path");
            }
            return $qr_image_path_rel;  // Return relative for DB
        } else {
            $last_err = error_get_last();
            $_SESSION['error'] = "Move failed (check logs). Last PHP err: " . ($last_err['message'] ?? 'none');
            error_log("QR Upload - FAIL: move_uploaded_file returned false. Last err: " . print_r($last_err, true));
            header("Location: $redirect_url");
            exit(0);
        }
    } elseif (isset($_FILES['qr_image']) && $_FILES['qr_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $codes = [UPLOAD_ERR_INI_SIZE => "Server max size exceeded.", UPLOAD_ERR_FORM_SIZE => "Form max exceeded.", UPLOAD_ERR_PARTIAL => "Partial upload.", UPLOAD_ERR_NO_TMP_DIR => "No tmp dir.", UPLOAD_ERR_CANT_WRITE => "Can't write to disk.", UPLOAD_ERR_EXTENSION => "Extension blocked."];
        $msg = $codes[$_FILES['qr_image']['error']] ?? "Unknown err {$_FILES['qr_image']['error']}";
        $_SESSION['error'] = "Upload err: $msg";
        error_log("QR Upload - FAIL: Code {$_FILES['qr_image']['error']}: $msg");
        header("Location: $redirect_url");
        exit(0);
    } else {
        error_log("QR Upload - No new file; using old: " . ($old_path ?? 'NULL'));
        return $old_path;  // No change
    }
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
    $payment_amount = floatval($_POST['payment_amount'] ?? 0);  // Use floatval for DB
    $rate = floatval($_POST['rate'] ?? 0);
    $alt_rate = $_POST['alt_rate'] ?? '';

    // Basic validation (as before)
    if (empty($country) || empty($currency) || empty($Channel) || empty($Channel_name) || empty($Channel_number) || $payment_amount <= 0 || $rate <= 0) {
        $_SESSION['error'] = "Required fields missing or invalid.";
        header("Location: ../region_settings.php");
        exit(0);
    }
    if (!preg_match('/^[A-Z]{3}$/', $currency)) {
        $_SESSION['error'] = "Currency must be 3-letter code.";
        header("Location: ../region_settings.php");
        exit(0);
    }
    if (!empty($alt_currency) && !preg_match('/^[A-Z]{3}$/', $alt_currency)) {
        $_SESSION['error'] = "Alt currency invalid.";
        header("Location: ../region_settings.php");
        exit(0);
    }
    if (!empty($alt_rate) && (!is_numeric($alt_rate) || $alt_rate <= 0)) {
        $_SESSION['error'] = "Alt rate invalid.";
        header("Location: ../region_settings.php");
        exit(0);
    }

    // Check duplicate country
    $check_query = "SELECT id FROM region_settings WHERE country = ? LIMIT 1";
    $stmt = $con->prepare($check_query);
    $stmt->bind_param("s", $country);
    $stmt->execute();
    $check_run = $stmt->get_result();
    if ($check_run->num_rows > 0) {
        $_SESSION['error'] = "Country already exists.";
        $stmt->close();
        header("Location: ../region_settings.php");
        exit(0);
    }
    $stmt->close();

    // Handle QR upload
    $qr_image_path = handleQrUpload("../region_settings.php", null, false);

    // Insert
    $insert_query = "INSERT INTO region_settings (country, currency, alt_currency, crypto, Channel, alt_channel, Channel_name, alt_ch_name, Channel_number, alt_ch_number, chnl_value, chnl_name_value, chnl_number_value, payment_amount, rate, alt_rate, qr_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $con->prepare($insert_query);
    $stmt->bind_param("sssisssssssdssdss", $country, $currency, $alt_currency, $crypto, $Channel, $alt_channel, $Channel_name, $alt_ch_name, $Channel_number, $alt_ch_number, $chnl_value, $chnl_name_value, $chnl_number_value, $payment_amount, $rate, $alt_rate, $qr_image_path);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Region added successfully.";
        error_log("Add Region - SUCCESS. QR DB path: " . ($qr_image_path ?? 'NULL'));
    } else {
        $_SESSION['error'] = "Insert failed: " . $stmt->error;
        error_log("Add Region - FAIL: " . $stmt->error);
        if ($qr_image_path && file_exists(__DIR__ . '/../' . $qr_image_path)) unlink(__DIR__ . '/../' . $qr_image_path);
    }
    $stmt->close();
    header("Location: ../region_settings.php");
    exit(0);
}

// Update Region
if (isset($_POST['update_region'])) {
    $region_id = intval($_POST['region_id'] ?? 0);
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
    $payment_amount = floatval($_POST['payment_amount'] ?? 0);
    $rate = floatval($_POST['rate'] ?? 0);
    $alt_rate = $_POST['alt_rate'] ?? '';

    if (empty($region_id) || empty($country) || empty($currency) || empty($Channel) || empty($Channel_name) || empty($Channel_number) || $payment_amount <= 0 || $rate <= 0) {
        $_SESSION['error'] = "Required fields invalid.";
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }
    // ... (add other validations like currency, alt_rate as in add section)

    // Fetch old QR (relative from DB)
    $fetch_query = "SELECT qr_image FROM region_settings WHERE id = ?";
    $fetch_stmt = $con->prepare($fetch_query);
    $fetch_stmt->bind_param("i", $region_id);
    $fetch_stmt->execute();
    $fetch_result = $fetch_stmt->get_result();
    $old_qr_rel = null;
    if ($row = $fetch_result->fetch_assoc()) {
        $old_qr_rel = $row['qr_image'];
    }
    $old_qr_abs = $old_qr_rel ? __DIR__ . '/../' . $old_qr_rel : null;
    $fetch_stmt->close();

    // Handle QR (pass old relative, get back relative or null)
    $qr_image_path_rel = handleQrUpload("../edit-region.php?id=$region_id", $old_qr_rel, true);

    // Check duplicate (exclude self)
    $check_query = "SELECT id FROM region_settings WHERE country = ? AND id != ? LIMIT 1";
    $stmt = $con->prepare($check_query);
    $stmt->bind_param("si", $country, $region_id);
    $stmt->execute();
    $check_run = $stmt->get_result();
    if ($check_run->num_rows > 0) {
        $_SESSION['error'] = "Country already exists.";
        if ($qr_image_path_rel && $qr_image_path_rel !== $old_qr_rel && file_exists(__DIR__ . '/../' . $qr_image_path_rel)) {
            unlink(__DIR__ . '/../' . $qr_image_path_rel);
        }
        $stmt->close();
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }
    $stmt->close();

    // Update
    $update_query = "UPDATE region_settings SET country = ?, currency = ?, alt_currency = ?, crypto = ?, Channel = ?, alt_channel = ?, Channel_name = ?, alt_ch_name = ?, Channel_number = ?, alt_ch_number = ?, chnl_value = ?, chnl_name_value = ?, chnl_number_value = ?, payment_amount = ?, rate = ?, alt_rate = ?, qr_image = ? WHERE id = ?";
    $stmt = $con->prepare($update_query);
    $stmt->bind_param("sssisssssssdssdssi", $country, $currency, $alt_currency, $crypto, $Channel, $alt_channel, $Channel_name, $alt_ch_name, $Channel_number, $alt_ch_number, $chnl_value, $chnl_name_value, $chnl_number_value, $payment_amount, $rate, $alt_rate, $qr_image_path_rel, $region_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Region updated successfully.";
        error_log("Update Region - SUCCESS. QR DB path: $qr_image_path_rel");
        header("Location: ../region_settings.php");
    } else {
        $_SESSION['error'] = "Update failed: " . $stmt->error;
        error_log("Update Region - FAIL: " . $stmt->error);
        if ($qr_image_path_rel && $qr_image_path_rel !== $old_qr_rel && file_exists(__DIR__ . '/../' . $qr_image_path_rel)) {
            unlink(__DIR__ . '/../' . $qr_image_path_rel);
        }
        header("Location: ../edit-region.php?id=$region_id");
    }
    $stmt->close();
    exit(0);
}

// Delete Region
if (isset($_POST['delete'])) {
    $region_id = intval($_POST['region_id'] ?? 0);

    // Fetch QR
    $fetch_query = "SELECT qr_image FROM region_settings WHERE id = ?";
    $fetch_stmt = $con->prepare($fetch_query);
    $fetch_stmt->bind_param("i", $region_id);
    $fetch_stmt->execute();
    $fetch_result = $fetch_stmt->get_result();
    $qr_rel = null;
    if ($row = $fetch_result->fetch_assoc()) {
        $qr_rel = $row['qr_image'];
    }
    $qr_abs = $qr_rel ? __DIR__ . '/../' . $qr_rel : null;
    $fetch_stmt->close();

    $delete_query = "DELETE FROM region_settings WHERE id = ?";
    $stmt = $con->prepare($delete_query);
    $stmt->bind_param("i", $region_id);
    if ($stmt->execute()) {
        if ($qr_abs && file_exists($qr_abs)) {
            unlink($qr_abs);
            error_log("Delete - Removed QR: $qr_abs");
        }
        $_SESSION['success'] = "Region deleted.";
    } else {
        $_SESSION['error'] = "Delete failed: " . $stmt->error;
        error_log("Delete - FAIL: " . $stmt->error);
    }
    $stmt->close();
    header("Location: ../region_settings.php");
    exit(0);
}

// Invalid
$_SESSION['error'] = "Invalid request.";
header("Location: ../region_settings.php");
exit(0);
?>
