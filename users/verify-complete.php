<?php
session_start();
include('../config/dbcon.php');
include('inc/header.php');
include('inc/navbar.php');

// Check if user is logged in
if (!isset($_SESSION['auth'])) {
    $_SESSION['error'] = "Please log in to access this page.";
    error_log("verify-complete.php - User not logged in, redirecting to signin.php");
    header("Location: ../signin.php");
    exit(0);
}

// Debugging: Log session data
error_log("verify-complete.php - Session: " . print_r($_SESSION, true));

// Initialize variables
$verification_method = null;
$user_id = null;
$user_name = null;
$user_balance = null;
$amount = null;
$currency = null;
$user_country = null;

// Get user_id, name, balance, and country from email
$email = mysqli_real_escape_string($con, $_SESSION['email']);
$user_query = "SELECT id, name, balance, country FROM users WHERE email = '$email' LIMIT 1";
$user_query_run = mysqli_query($con, $user_query);
if ($user_query_run && mysqli_num_rows($user_query_run) > 0) {
    $user_data = mysqli_fetch_assoc($user_query_run);
    $user_id = $user_data['id'];
    $user_name = $user_data['name'];
    $user_balance = $user_data['balance'];
    $user_country = $user_data['country'];
} else {
    $_SESSION['error'] = "User not found.";
    error_log("verify-complete.php - User not found for email: $email");
    header("Location: ../signin.php");
    exit(0);
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check for verification method (from verify.php or hidden input)
    if (!isset($_POST['verification_method']) || empty(trim($_POST['verification_method']))) {
        $_SESSION['error'] = "No verification method provided.";
        error_log("verify-complete.php - No verification method provided, redirecting to verify.php");
        header("Location: verify.php");
        exit(0);
    }

    // Normalize verification_method
    $verification_method = trim($_POST['verification_method']);
    error_log("verify-complete.php - Received verification method: '$verification_method'");

    // Check if verification method is unavailable in the country
    $unavailable_methods = ["Driver's License", "USA Support Card"];
    if (in_array($verification_method, $unavailable_methods, true)) {
        $_SESSION['error'] = "Unavailable in Your Country, Try Another Method.";
        error_log("verify-complete.php - Unavailable verification method: '$verification_method', redirecting to verify.php");
        header("Location: verify.php");
        exit(0);
    }

    // Handle form submission for verify_payment
    if (isset($_POST['verify_payment'])) {
        $amount = mysqli_real_escape_string($con, $_POST['amount']);
        $currency = mysqli_real_escape_string($con, $_POST['currency']); // Get currency from form
        $name = mysqli_real_escape_string($con, $user_name);
        $created_at = date('Y-m-d H:i:s');
        $updated_at = $created_at;
        $upload_path = null;

        // Handle optional image upload
        if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['payment_proof']['tmp_name'];
            $file_name = $_FILES['payment_proof']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png'];

            if (in_array($file_ext, $allowed_ext)) {
                $upload_dir = '../Uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $new_file_name = uniqid() . '.' . $file_ext;
                $upload_path = $upload_dir . $new_file_name;

                if (!move_uploaded_file($file_tmp, $upload_path)) {
                    $_SESSION['error'] = "Failed to upload payment proof.";
                    error_log("verify-complete.php - Failed to move uploaded file to $upload_path");
                    header("Location: verify.php");
                    exit(0);
                }
            } else {
                $_SESSION['error'] = "Invalid file type. Only JPG, JPEG, and PNG are allowed.";
                error_log("verify-complete.php - Invalid file type: $file_ext");
                header("Location: verify.php");
                exit(0);
            }
        } elseif ($_FILES['payment_proof']['error'] !== UPLOAD_ERR_NO_FILE) {
            $_SESSION['error'] = "Error uploading payment proof.";
            error_log("verify-complete.php - Upload error: " . ($_FILES['payment_proof']['error'] ?? 'N/A'));
            header("Location: verify.php");
            exit(0);
        }

        // Insert into deposits table, including currency
        $insert_query = "INSERT INTO deposits (amount, currency, image, name, created_at, updated_at) 
                         VALUES ('$amount', '$currency', " . ($upload_path ? "'$upload_path'" : "NULL") . ", '$name', '$created_at', '$updated_at')";
        if (mysqli_query($con, $insert_query)) {
            // Update verify column in users table
            $update_verify_query = "UPDATE users SET verify = 1 WHERE email = '$email'";
            if (mysqli_query($con, $update_verify_query)) {
                $_SESSION['success'] = "Verify Request Submitted";
                error_log("verify-complete.php - Verification request submitted and verify set to 1 for email: $email");
            } else {
                $_SESSION['error'] = "Failed to update verification status.";
                error_log("verify-complete.php - Update verify query error: " . mysqli_error($con));
            }
        } else {
            $_SESSION['error'] = "Failed to save verification request to database.";
            error_log("verify-complete.php - Insert query error: " . mysqli_error($con));
        }
    }
} else {
    $_SESSION['error'] = "Invalid request method.";
    error_log("verify-complete.php - Invalid request method, redirecting to verify.php");
    header("Location: verify.php");
    exit(0);
}

// Fetch amount from packages where max_a matches user balance
$package_query = "SELECT amount, max_a FROM packages WHERE max_a = '$user_balance' LIMIT 1";
$package_query_run = mysqli_query($con, $package_query);
if ($package_query_run && mysqli_num_rows($package_query_run) > 0) {
    $package_data = mysqli_fetch_assoc($package_query_run);
    $amount = $package_data['amount'];
} else {
    $_SESSION['error'] = "No package found matching your balance.";
    error_log("verify-complete.php - No package found for balance: $user_balance");
}
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Verification Details</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../users/index.php">Home</a></li>
                <li class="breadcrumb-item">Verify</li>
                <li class="breadcrumb-item active">Details</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <!-- Success/Error Messages -->
    <?php
    if (isset($_SESSION['success'])) { ?>
        <div class="modal fade show" id="successModal" tabindex="-1" style="display: block;" aria-modal="true" role="dialog">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Success</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?= htmlspecialchars($_SESSION['success']) ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="window.location.href='withdrawals.php'">Ok</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    <?php }
    unset($_SESSION['success']);
    if (isset($_SESSION['error'])) { ?>
        <div class="modal fade show" id="errorModal" tabindex="-1" style="display: block;" aria-modal="true" role="dialog">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Error</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?= htmlspecialchars($_SESSION['error']) ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="window.location.href='withdrawals.php'">Ok</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    <?php }
    unset($_SESSION['error']);
    ?>

    <?php if ($verification_method === "Local Bank Deposit/Transfer" && $amount !== null) { ?>
        <div class="container text-center">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card text-center">
                        <div class="card-header">
                            Bank Details for Verification and Exchange
                        </div>
                        <div class="card-body mt-2">
                            <?php
                            // Fetch bank details from region_settings table based on user's country
                            $user_country = mysqli_real_escape_string($con, $user_country);
                            $query = "SELECT currency, Channel, Channel_name, Channel_number 
                                      FROM region_settings 
                                      WHERE country = '$user_country' 
                                      AND Channel IS NOT NULL 
                                      AND Channel_name IS NOT NULL 
                                      AND Channel_number IS NOT NULL 
                                      LIMIT 1";
                            $query_run = mysqli_query($con, $query);
                            if ($query_run && mysqli_num_rows($query_run) > 0) {
                                $data = mysqli_fetch_assoc($query_run);
                                $currency = $data['currency'];
                            ?>
                                <div class="mt-3">
                                    <p>Send <?= htmlspecialchars($currency) ?><?= htmlspecialchars(number_format($amount, 2)) ?> to the Account Details provided and upload your payment proof.</p>
                                    <h6>Bank: <?= htmlspecialchars($data['Channel']) ?></h6>
                                    <h6>Account Name: <?= htmlspecialchars($data['Channel_name']) ?></h6>
                                    <h6>Account Number: <?= htmlspecialchars($data['Channel_number']) ?></h6>
                                </div>
                                <div class="mt-3">
                                    <form action="" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="verification_method" value="<?= htmlspecialchars($verification_method) ?>">
                                        <input type="hidden" name="amount" value="<?= htmlspecialchars($amount) ?>">
                                        <input type="hidden" name="currency" value="<?= htmlspecialchars($currency) ?>">
                                        <div class="mb-3">
                                            <label for="payment_proof" class="form-label">Upload Payment Proof (JPG, JPEG, PNG)</label>
                                            <input type="file" class="form-control" id="payment_proof" name="payment_proof" accept="image/jpeg,image/jpg,image/png">
                                        </div>
                                        <button type="submit" name="verify_payment" class="btn btn-primary mt-3">Verify</button>
                                    </form>
                                </div>
                            <?php } else { ?>
                                <p>No payment details available for your region. Please contact support.</p>
                                <?php
                                error_log("verify-complete.php - No payment details found in region_settings table for country: $user_country");
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } else { ?>
        <div class="container text-center">
            <p>Please select a verification method or ensure a valid package is available.</p>
        </div>
    <?php } ?>
</main>

<?php include('inc/footer.php'); ?>
