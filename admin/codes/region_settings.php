<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include('../config/dbcon.php');
include('inc/countries.php');

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    $_SESSION['error'] = "Please log in to access this page.";
    header("HTTP/1.1 302 Found");
    header("Location: ../signin.php");
    exit(0);
}

// Validate auth_id
if (!isset($_POST['auth_id']) || empty($_POST['auth_id']) || $_POST['auth_id'] != $_SESSION['id']) {
    $_SESSION['error'] = "Unauthorized action.";
    header("HTTP/1.1 302 Found");
    header("Location: ../region_settings.php");
    exit(0);
}

$auth_id = mysqli_real_escape_string($con, $_POST['auth_id']);

// Check countries array
if (!isset($countries) || empty($countries)) {
    error_log("region_settings.php - Countries array is not set or empty");
    $_SESSION['error'] = "Country list unavailable. Please contact support.";
    header("HTTP/1.1 302 Found");
    header("Location: ../region_settings.php");
    exit(0);
}

// Add Region
if (isset($_POST['add_region'])) {
    error_log("region_settings.php - POST Data: " . print_r($_POST, true));
    $country = mysqli_real_escape_string($con, $_POST['country']);
    $currency = mysqli_real_escape_string($con, $_POST['currency']);
    $Channel = mysqli_real_escape_string($con, $_POST['Channel']);
    $Channel_name = mysqli_real_escape_string($con, $_POST['Channel_name']);
    $Channel_number = mysqli_real_escape_string($con, $_POST['Channel_number']);
    $chnl_value = mysqli_real_escape_string($con, $_POST['chnl_value'] ?? '');
    $chnl_name_value = mysqli_real_escape_string($con, $_POST['chnl_name_value'] ?? '');
    $chnl_number_value = mysqli_real_escape_string($con, $_POST['chnl_number_value'] ?? '');
    $payment_amount = floatval($_POST['payment_amount']);
    $rate = floatval($_POST['rate']);

    // Validate inputs
    if (empty($country) || empty($currency) || empty($Channel) || empty($Channel_name) || empty($Channel_number) || $payment_amount <= 0 || $rate <= 0) {
        $_SESSION['error'] = "All required fields must be filled with valid values.";
        header("HTTP/1.1 302 Found");
        header("Location: ../region_settings.php");
        exit(0);
    }

    // Validate Channel length
    if (strlen($Channel) > 50) {
        $_SESSION['error'] = "Channel must not exceed 50 characters.";
        header("HTTP/1.1 302 Found");
        header("Location: ../region_settings.php");
        exit(0);
    }

    // Validate country
    if (!in_array($country, $countries)) {
        $_SESSION['error'] = "Invalid country selected.";
        header("HTTP/1.1 302 Found");
        header("Location: ../region_settings.php");
        exit(0);
    }

    // Validate currency format
    if (!preg_match('/^[A-Z]{3}$/', $currency)) {
        $_SESSION['error'] = "Currency must be a 3-letter code (e.g., NGN).";
        header("HTTP/1.1 302 Found");
        header("Location: ../region_settings.php");
        exit(0);
    }

    // Check if country already exists
    $check_query = "SELECT id FROM region_settings WHERE country = '$country' LIMIT 1";
    $check_run = mysqli_query($con, $check_query);
    if (!$check_run) {
        error_log("region_settings.php - Check query error: " . mysqli_error($con));
        $_SESSION['error'] = "Database error. Please try again.";
        header("HTTP/1.1 302 Found");
        header("Location: ../region_settings.php");
        exit(0);
    }
    if (mysqli_num_rows($check_run) > 0) {
        $_SESSION['error'] = "Region settings for this country already exist.";
        header("HTTP/1.1 302 Found");
        header("Location: ../region_settings.php");
        exit(0);
    }

    // Insert new region
    $insert_query = "INSERT INTO region_settings (country, currency, Channel, Channel_name, Channel_number, chnl_value, chnl_name_value, chnl_number_value, payment_amount, rate, auth_id) 
                     VALUES ('$country', '$currency', '$Channel', '$Channel_name', '$Channel_number', '$chnl_value', '$chnl_name_value', '$chnl_number_value', '$payment_amount', '$rate', '$auth_id')";
    if (mysqli_query($con, $insert_query)) {
        $_SESSION['success'] = "Region added successfully.";
    } else {
        $_SESSION['error'] = "Failed to add region: " . mysqli_error($con);
        error_log("region_settings.php - Insert query error: " . mysqli_error($con));
    }
    header("HTTP/1.1 302 Found");
    header("Location: ../region_settings.php");
    exit(0);
}

// Update Region
if (isset($_POST['update_region'])) {
    $region_id = mysqli_real_escape_string($con, $_POST['region_id']);
    $country = mysqli_real_escape_string($con, $_POST['country']);
    $currency = mysqli_real_escape_string($con, $_POST['currency']);
    $Channel = mysqli_real_escape_string($con, $_POST['Channel']);
    $Channel_name = mysqli_real_escape_string($con, $_POST['Channel_name']);
    $Channel_number = mysqli_real_escape_string($con, $_POST['Channel_number']);
    $chnl_value = mysqli_real_escape_string($con, $_POST['chnl_value'] ?? '');
    $chnl_name_value = mysqli_real_escape_string($con, $_POST['chnl_name_value'] ?? '');
    $chnl_number_value = mysqli_real_escape_string($con, $_POST['chnl_number_value'] ?? '');
    $payment_amount = floatval($_POST['payment_amount']);
    $rate = floatval($_POST['rate']);

    // Validate inputs
    if (empty($country) || empty($currency) || empty($Channel) || empty($Channel_name) || empty($Channel_number) || $payment_amount <= 0 || $rate <= 0) {
        $_SESSION['error'] = "All required fields must be filled with valid values.";
        header("HTTP/1.1 302 Found");
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }

    // Validate Channel length
    if (strlen($Channel) > 50) {
        $_SESSION['error'] = "Channel must not exceed 50 characters.";
        header("HTTP/1.1 302 Found");
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }

    // Validate country
    if (!in_array($country, $countries)) {
        $_SESSION['error'] = "Invalid country selected.";
        header("HTTP/1.1 302 Found");
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }

    // Validate currency format
    if (!preg_match('/^[A-Z]{3}$/', $currency)) {
        $_SESSION['error'] = "Currency must be a 3-letter code (e.g., NGN).";
        header("HTTP/1.1 302 Found");
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }

    // Check if country already exists for another record
    $check_query = "SELECT id FROM region_settings WHERE country = '$country' AND id != '$region_id' LIMIT 1";
    $check_run = mysqli_query($con, $check_query);
    if (!$check_run) {
        error_log("region_settings.php - Check query error: " . mysqli_error($con));
        $_SESSION['error'] = "Database error. Please try again.";
        header("HTTP/1.1 302 Found");
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }
    if (mysqli_num_rows($check_run) > 0) {
        $_SESSION['error'] = "Region settings for this country already exist.";
        header("HTTP/1.1 302 Found");
        header("Location: ../edit-region.php?id=$region_id");
        exit(0);
    }

    // Update region
    $update_query = "UPDATE region_settings SET 
                     country = '$country', 
                     currency = '$currency', 
                     Channel = '$Channel', 
                     Channel_name = '$Channel_name', 
                     Channel_number = '$Channel_number', 
                     chnl_value = '$chnl_value', 
                     chnl_name_value = '$chnl_name_value', 
                     chnl_number_value = '$chnl_number_value', 
                     payment_amount = '$payment_amount', 
                     rate = '$rate', 
                     auth_id = '$auth_id'
                     WHERE id = '$region_id'";
    if (mysqli_query($con, $update_query)) {
        $_SESSION['success'] = "Region updated successfully.";
        header("HTTP/1.1 302 Found");
        header("Location: ../region_settings.php");
    } else {
        $_SESSION['error'] = "Failed to update region: " . mysqli_error($con);
        error_log("region_settings.php - Update query error: " . mysqli_error($con));
        header("HTTP/1.1 302 Found");
        header("Location: ../edit-region.php?id=$region_id");
    }
    exit(0);
}

// Delete Region
if (isset($_POST['delete'])) {
    $region_id = mysqli_real_escape_string($con, $_POST['delete']);
    $delete_query = "DELETE FROM region_settings WHERE id = '$region_id'";
    if (mysqli_query($con, $delete_query)) {
        $_SESSION['success'] = "Region deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete region: " . mysqli_error($con);
        error_log("region_settings.php - Delete query error: " . mysqli_error($con));
    }
    header("HTTP/1.1 302 Found");
    header("Location: ../region_settings.php");
    exit(0);
}

// Invalid request
$_SESSION['error'] = "Invalid request.";
header("HTTP/1.1 302 Found");
header("Location: ../region_settings.php");
exit(0);
?>
