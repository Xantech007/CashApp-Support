<?php
include('config/dbcon.php');

// Workaround for InfinityFree's aes.js
if (strpos($_SERVER['HTTP_USER_AGENT'], 'FacebookExternalHit') !== false || 
    strpos($_SERVER['HTTP_USER_AGENT'], 'TwitterBot') !== false || 
    strpos($_SERVER['HTTP_USER_AGENT'], 'LinkedInBot') !== false) {
    header('Content-Type: text/html; charset=UTF-8');
    readfile('static-version.html');
    exit;
}

// Default values for meta tags (for homepage or fallback)
$page_title = "Dollar Cash - Seamless Financial Transactions";
$page_description = "Send and receive money instantly with Cash App, the easiest way to manage your finances.";
$page_image = "http://dollarcash.online/Uploads/logo/preview-image.jpg"; // Update to 1200x630 image
$page_url = "http://dollarcash.online"; // Homepage URL

// Dynamic meta tags for specific pages (e.g., blog posts, services)
if (isset($_GET['page_id'])) {
    $page_id = $_GET['page_id'];
    $query = "SELECT title, description, image FROM pages WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $page_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $page_title = $row['title'];
        $page_description = $row['description'];
        $page_image = "http://dollarcash.online/Uploads/" . $row['image'];
        $page_url = "http://dollarcash.online/?page_id=" . $page_id;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">
    <title><?php echo htmlspecialchars($page_title); ?></title>

    <!-- favicon -->
    <link rel="shortcut icon" href="Uploads/logo/logo1.png" type="image/x-icon">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?>" />
    <meta property="og:description" content="<?php echo htmlspecialchars($page_description); ?>" />
    <meta property="og:image" content="<?php echo htmlspecialchars($page_image); ?>" />
    <meta property="og:image:secure_url" content="<?php echo htmlspecialchars($page_image); ?>" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:image:alt" content="Cash App preview image" />
    <meta property="og:url" content="<?php echo htmlspecialchars($page_url); ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="Cash App" />

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?php echo htmlspecialchars($page_title); ?>" />
    <meta name="twitter:description" content="<?php echo htmlspecialchars($page_description); ?>" />
    <meta name="twitter:image" content="<?php echo htmlspecialchars($page_image); ?>" />
    <meta name="twitter:site" content="@YourCashAppHandle" /> <!-- Replace or remove -->

    <!-- bootstrap -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <!-- Plugin css -->
    <link rel="stylesheet" href="assets/css/plugin.css">
    <!-- Flaticon -->
    <link rel="stylesheet" href="assets/css/flaticon.css">
    <!-- stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dark.css">
    <!-- responsive -->
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>

<body>
    <!-- preloader area start -->
    <div class="preloader" id="preloader">
        <div class="loader loader-1">
            <div class="loader-outter"></div>
            <div class="loader-inner"></div>
        </div>
    </div>
    <!-- preloader area end -->
