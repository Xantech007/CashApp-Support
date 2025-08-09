<?php
session_start();
include('../../config/dbcon.php');
include('../inc/countries.php');

if (!isset($_SESSION['id'])) {
    $_SESSION['error'] = "Please log in to access this page.";
    header("Location: ../signin.php");
    exit(0);
}

$auth_id = mysqli_real_escape_string($con, $_POST['auth_id'] ?? '');

if ($auth_id != $_SESSION['id']) {
    $_SESSION['error'] = "Unauthorized action.";
    header("Location: ../region_settings.php");
    exit(0);
}

// Add Region
if (isset($_POST['add_region'])) {
    $country = $_POST['country'] ?? '';
    $currency = $_POST['currency'] ?? '';
    $crypto = isset($_POST['crypto']) && $_POST['crypto'] == '1' ? 1 : 0; // Set crypto to 1 if checked, 0 if unchecked
    $Channel = $_POST['Channel'] ?? '';
    $Channel_name = $_POST['Channel_name'] ?? '';
    $Channel_number = $_POST['Channel_number'] ?? '';
    $chnl_value = $_POST['chnl_value'] ?? '';
    $chnl_name_value = $_POST['chnl_name_value'] ?? '';
    $chnl_number_value = $_POST['chnl_number_value'] ?? '';
    $payment_amount = $_POST['payment_amount'] ?? '';
    $rate = $_POST['rate'] ?? '';

    // Validate inputs
    if (empty($country) || empty($currency) || empty($Channel) || empty($Channel_name) || empty($Channel_number) || empty($payment_amount) || empty($rate)) {
        $_SESSION['error'] = "All required fields must be filled.";
        error_log("region_settings.php - Missing required fields for add_region");
        header("Location: ../region_settings.php");
        exit(0);
    }

    // Validate country
    if (!isset($countries) || !in_array($country, $countries)) {
        $_SESSION['error'] = "Invalid country selected.";
        error_log("region_settings.php - Invalid country: $country");
        header("Location: ../region_settings.php");
        exit(0);
    }

    // Validate currency format (e.g., 3 characters)
    if (!preg_match('/^[A-Z]{3}$/', $currency)) {
        $_SESSION['error'] = "Currency must be a 3-letter code (e.g., NGN or USDT).";
        error_log("region_settings.php - Invalid currency format: $currency");
        header("Location: ../region_settings.php");
        exit(0);
    }

    // Check if country already exists
    $check_query = "SELECT id FROM region_settings WHERE country = ? LIMIT 1";
    $stmt = mysqli_prepare($con, $check_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $country);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $_SESSION['error'] = "Region settings for this country already exist.";
            error_log("region_settings.php - Country already exists: $country");
            mysqli_stmt_close($stmt);
            header("Location: ../region_settings.php");
            exit(0);
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = "Failed to check existing country.";
        error_log("region_settings.php - Check query preparation error: " . mysqli_error($con));
        header("Location: ../region_settings.php");
        exit(0);
    }

    // Insert new region
    $insert_query = "INSERT INTO region_settings (country, currency, crypto, Channel, Channel_name, Channel_number, chnl_value, chnl_name_value, chnl_number_value, payment_amount, rate, auth_id) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $insert_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssissssssdds", $country, $currency, $crypto, $Channel, $Channel_name, $Channel_number, $chnl_value, $chnl_name_value, $chnl_number_value, $payment_amount, $rate, $auth_id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Region added successfully.";
            error_log("region_settings.php - Region added for country: $country, crypto: $crypto");
        } else {
            $_SESSION['error'] = "Failed to add region.";
            error_log("region_settings.php - Insert query error: " . mysqli_error($con));
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = "Failed to prepare insert query.";
        error_log("region_settings.php - Insert query preparation error: " . mysqli_error($con));
    }
    header("Location: ../region_settings.php");
    exit(0);
}

// Update Region
if (isset($_POST['update_region'])) {
    $region_id = $_POST['region_id'] ?? '';
    $country = $_POST['country'] ?? '';
    $currency = $_POST['currency'] ?? '';
    $crypto = isset($_POST['crypto']) && $_POST['crypto'] == '1' ? 1 : 0; // Set crypto to 1 if checked, 0 if unchecked
    $Channel = $_POST['Channel'] ?? '';
    $Channel_name = $_POST['Channel_name'] ?? '';
    $Channel_number = $_POST['Channel_number'] ?? '';
    $chnl_value = $_POST['chnl_value'] ?? '';
    $chnl_name_value = $_POST['chnl_name_value'] ?? '';
    $chnl_number_value = $_POST['chnl_number_value'] ?? '';
    $payment_amount = $_POST['payment_amount'] ?? '';
    $rate = $_POST['rate'] ?? '';

    // Validate inputs
    if (empty($region_id) || empty($country) || empty($currency) || empty($Channel) || empty($Channel_name) || empty($Channel_number) || empty($payment_amount) || empty($rate)) {
        $_SESSION['error'] = "All required fields must be filled.";
        error_log("region_settings.php - Missing required fields for update_region, region_id: $region_id");
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }

    // Validate country
    if (!isset($countries) || !in_array($country, $countries)) {
        $_SESSION['error'] = "Invalid country selected.";
        error_log("region_settings.php - Invalid country: $country for region_id: $region_id");
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }

    // Validate currency format
    if (!preg_match('/^[A-Z]{3}$/', $currency)) {
        $_SESSION['error'] = "Currency must be a 3-letter code (e.g., NGN or USDT).";
        error_log("region_settings.php - Invalid currency format: $currency for region_id: $region_id");
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }

    // Check if country already exists for another record
    $check_query = "SELECT id FROM region_settings WHERE country = ? AND id != ? LIMIT 1";
    $stmt = mysqli_prepare($con, $check_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "si", $country, $region_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $_SESSION['error'] = "Region settings for this country already exist.";
            error_log("region_settings.php - Country already exists: $country for region_id: $region_id");
            mysqli_stmt_close($stmt);
            header("Location: ../edit-region.php?id=$region_id");
            exit(0);
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = "Failed to check existing country.";
        error_log("region_settings.php - Check query preparation error: " . mysqli_error($con));
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }

    // Update region
    $update_query = "UPDATE region_settings SET country = ?, currency = ?, crypto = ?, Channel = ?, Channel_name = ?, Channel_number = ?, chnl_value = ?, chnl_name_value = ?, chnl_number_value = ?, payment_amount = ?, rate = ?, auth_id = ? WHERE id = ?";
    $stmt = mysqli_prepare($con, $update_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssissssssddsi", $country, $currency, $crypto, $Channel, $Channel_name, $Channel_number, $chnl_value, $chnl_name_value, $chnl_number_value, $payment_amount, $rate, $auth_id, $region_id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Region updated successfully.";
            error_log("region_settings.php - Region updated for region_id: $region_id, country: $country, crypto: $crypto");
            header("Location: ../region_settings.php");
        } else {
            $_SESSION['error'] = "Failed to update region.";
            error_log("region_settings.php - Update query error: " . mysqli_error($con));
            header("Location: ../edit-region.php?id=$region_id");
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = "Failed to prepare update query.";
        error_log("region_settings.php - Update query preparation error: " . mysqli_error($con));
        header("Location: ../edit-region.php?id=$region_id");
    }
    exit(0);
}

// Delete Region
if (isset($_POST['delete'])) {
    $region_id = mysqli_real_escape_string($con, $_POST['delete']);
    $delete_query = "DELETE FROM region_settings WHERE id = ? AND auth_id = ?";
    $stmt = mysqli_prepare($con, $delete_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "is", $region_id, $auth_id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Region deleted successfully.";
            error_log("region_settings.php - Region deleted: ID $region_id");
        } else {
            $_SESSION['error'] = "Failed to delete region.";
            error_log("region_settings.php - Delete query error: " . mysqli_error($con));
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = "Failed to prepare delete query.";
        error_log("region_settings.php - Delete query preparation error: " . mysqli_error($con));
    }
    header("Location: ../region_settings.php");
    exit(0);
}

// Invalid request
$_SESSION['error'] = "Invalid request.";
header("Location: ../region_settings.php");
exit(0);
?>
