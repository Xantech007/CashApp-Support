<?php
session_start();
include('../config/dbcon.php');
include('inc/header.php');
include('inc/navbar.php');

// Check if user is logged in
if (!isset($_SESSION['auth'])) {
    $_SESSION['error'] = "Please log in to access this page.";
    error_log("part-payment.php - User not logged in, redirecting to signin.php");
    header("Location: ../signin.php");
    exit(0);
}

// Initialize variables
$user_id = null;
$user_name = null;
$user_balance = null;
$amount = null;
$currency = null;
$user_country = null;
$crypto = 0; // Default to bank transfer
$verification_method = "Local Bank Deposit/Transfer"; // Default, will be updated based on crypto setting

// Debug session
error_log("part-payment.php - Session email: " . ($_SESSION['email'] ?? 'not set'));
error_log("part-payment.php - Request method: {$_SERVER['REQUEST_METHOD']}");

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
    error_log("part-payment.php - User not found for email: $email");
    header("Location: ../signin.php");
    exit(0);
}

// Check if user_country is set
if (empty($user_country)) {
    $_SESSION['error'] = "User country not set.";
    error_log("part-payment.php - User country is empty for email: $email");
    header("Location: verify.php");
    exit(0);
}

// Fetch payment details and crypto setting from region_settings
$package_query = "SELECT payment_amount, currency, crypto FROM region_settings WHERE country = '" . mysqli_real_escape_string($con, $user_country) . "' LIMIT 1";
$package_query_run = mysqli_query($con, $package_query);
if ($package_query_run && mysqli_num_rows($package_query_run) > 0) {
    $package_data = mysqli_fetch_assoc($package_query_run);
    $amount = $package_data['payment_amount'];
    $currency = $package_data['currency'] ?? '$'; // Fallback to '$' if currency is null
    $crypto = $package_data['crypto'] ?? 0; // Update crypto value
    $verification_method = ($crypto == 1) ? "Crypto Deposit/Transfer" : "Local Bank Deposit/Transfer";
    error_log("part-payment.php - Found payment details: amount={$amount}, currency={$currency}, crypto={$crypto}");
} else {
    $_SESSION['error'] = "No payment details found for your country.";
    error_log("part-payment.php - No payment details found in region_settings for country: $user_country");
    header("Location: verify.php");
    exit(0);
}

// Calculate part-payment amounts
$two_times_payment = $amount * 0.5; // 50% of total amount
$four_times_payment = $amount * 0.25; // 25% of total amount

// Handle POST request for part-payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("part-payment.php - POST data: " . print_r($_POST, true));
    error_log("part-payment.php - FILES data: " . print_r($_FILES, true));

    if (!isset($_POST['payment_plan']) || !isset($_POST['amount'])) {
        $_SESSION['error'] = "Invalid payment plan or amount.";
        error_log("part-payment.php - Invalid payment plan or amount, redirecting to part-payment.php");
        header("Location: part-payment.php");
        exit(0);
    }

    $payment_plan = trim($_POST['payment_plan']);
    $submitted_amount = mysqli_real_escape_string($con, $_POST['amount']);
    $name = mysqli_real_escape_string($con, $user_name);
    $email = mysqli_real_escape_string($con, $_SESSION['email']);
    $created_at = date('Y-m-d H:i:s');
    $updated_at = $created_at;
    $upload_path = null;

    // Validate payment plan and amount
    if ($payment_plan === "2-times" && $submitted_amount != $two_times_payment) {
        $_SESSION['error'] = "Invalid amount for 2-times payment plan.";
        error_log("part-payment.php - Invalid amount for 2-times payment plan: $submitted_amount");
        header("Location: part-payment.php");
        exit(0);
    } elseif ($payment_plan === "4-times" && $submitted_amount != $four_times_payment) {
        $_SESSION['error'] = "Invalid amount for 4-times payment plan.";
        error_log("part-payment.php - Invalid amount for 4-times payment plan: $submitted_amount");
        header("Location: part-payment.php");
        exit(0);
    }

    // Check if a file was uploaded
    if (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] === UPLOAD_ERR_NO_FILE) {
        $_SESSION['error'] = "Please upload a payment proof file.";
        error_log("part-payment.php - No file uploaded for payment proof");
        header("Location: part-payment.php");
        exit(0);
    }

    // Handle file upload
    if ($_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['payment_proof']['tmp_name'];
        $file_name = $_FILES['payment_proof']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $file_type = mime_content_type($file_tmp);
        $allowed_ext = ['jpg', 'jpeg', 'png'];
        $allowed_types = ['image/jpeg', 'image/png'];

        // Validate file type
        if (!in_array($file_ext, $allowed_ext) || !in_array($file_type, $allowed_types)) {
            $_SESSION['error'] = "Invalid file type. Only JPG, JPEG, and PNG are allowed.";
            error_log("part-payment.php - Invalid file type: $file_type, extension: $file_ext");
            header("Location: part-payment.php");
            exit(0);
        }

        // Validate file size (5MB limit)
        if ($_FILES['payment_proof']['size'] > 5 * 1024 * 1024) {
            $_SESSION['error'] = "File size exceeds 5MB limit.";
            error_log("part-payment.php - File size too large: {$_FILES['payment_proof']['size']} bytes");
            header("Location: part-payment.php");
            exit(0);
        }

        // Set up upload directory
        $upload_dir = '../Uploads/';
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                $_SESSION['error'] = "Failed to create upload directory.";
                error_log("part-payment.php - Failed to create directory: $upload_dir");
                header("Location: part-payment.php");
                exit(0);
            }
        }

        // Ensure directory is writable
        if (!is_writable($upload_dir)) {
            $_SESSION['error'] = "Upload directory is not writable.";
            error_log("part-payment.php - Directory not writable: $upload_dir");
            header("Location: part-payment.php");
            exit(0);
        }

        $new_file_name = uniqid() . '.' . $file_ext;
        $upload_path = $upload_dir . $new_file_name;

        // Move uploaded file
        if (!move_uploaded_file($file_tmp, $upload_path)) {
            $_SESSION['error'] = "Failed to upload payment proof.";
            error_log("part-payment.php - Failed to move file to $upload_path");
            header("Location: part-payment.php");
            exit(0);
        }
    } else {
        $upload_error_codes = [
            UPLOAD_ERR_INI_SIZE => "File exceeds server's maximum file size.",
            UPLOAD_ERR_FORM_SIZE => "File exceeds form's maximum file size.",
            UPLOAD_ERR_PARTIAL => "File was only partially uploaded.",
            UPLOAD_ERR_NO_TMP_DIR => "Missing temporary folder.",
            UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk.",
            UPLOAD_ERR_EXTENSION => "A PHP extension stopped the file upload."
        ];
        $error_message = $upload_error_codes[$_FILES['payment_proof']['error']] ?? "Unknown upload error.";
        $_SESSION['error'] = "Error uploading payment proof: $error_message (Error Code: {$_FILES['payment_proof']['error']})";
        error_log("part-payment.php - Upload error: $error_message (Code: {$_FILES['payment_proof']['error']})");
        header("Location: part-payment.php");
        exit(0);
    }

    // Insert into deposits table with payment plan
    $insert_query = "INSERT INTO deposits (amount, image, name, email, currency, payment_plan, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $insert_query);
    if ($stmt) {
        $image_param = $upload_path ?: null;
        mysqli_stmt_bind_param($stmt, "dsssssss", $submitted_amount, $image_param, $name, $email, $currency, $payment_plan, $created_at, $updated_at);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Part-payment request submitted successfully.";
            error_log("part-payment.php - Part-payment request submitted for email: $email, plan: $payment_plan, amount: $submitted_amount");
        } else {
            $_SESSION['error'] = "Failed to save part-payment request to database.";
            error_log("part-payment.php - Insert query error: " . mysqli_error($con));
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = "Failed to prepare insert query.";
        error_log("part-payment.php - Insert query preparation error: " . mysqli_error($con));
    }

    // Redirect to avoid form resubmission
    error_log("part-payment.php - Redirecting to part-payment.php");
    header("Location: part-payment.php");
    exit(0);
}
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Part Payment Options</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../users/index.php">Home</a></li>
                <li class="breadcrumb-item">Verify</li>
                <li class="breadcrumb-item active">Part Payment</li>
            </ol>
        </nav>
    </div>

    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success'])) { ?>
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
                        <button type="button" class="btn btn-primary" onclick="window.location.href='verify-complete.php?verification_method=<?= urlencode($verification_method) ?>'">Ok</button>
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
                        <button type="button" class="btn btn-primary" onclick="window.location.href='part-payment.php'">Ok</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    <?php }
    unset($_SESSION['error']);
    ?>

    <?php if ($amount !== null) { ?>
        <div class="container text-center">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card text-center">
                        <div class="card-header">
                            Select a Part Payment Plan
                        </div>
                        <div class="card-body mt-2">
                            <?php
                            // Fetch payment details from region_settings
                            $query = "SELECT currency, Channel, Channel_name, Channel_number, chnl_value, chnl_name_value, chnl_number_value, crypto 
                                      FROM region_settings 
                                      WHERE country = '" . mysqli_real_escape_string($con, $user_country) . "' 
                                      AND Channel IS NOT NULL 
                                      AND Channel_name IS NOT NULL 
                                      AND Channel_number IS NOT NULL 
                                      LIMIT 1";
                            $query_run = mysqli_query($con, $query);
                            if ($query_run && mysqli_num_rows($query_run) > 0) {
                                $data = mysqli_fetch_assoc($query_run);
                                $currency = $data['currency'] ?? '$';
                                $crypto = $data['crypto'] ?? 0;
                                $channel_label = $data['Channel'];
                                $channel_name_label = $data['Channel_name'];
                                $channel_number_label = $data['Channel_number'];
                                $channel_value = $data['chnl_value'] ?? $data['Channel'];
                                $channel_name_value = $data['chnl_name_value'] ?? $data['Channel_name'];
                                $channel_number_value = $data['chnl_number_value'] ?? $data['Channel_number'];
                                $method_label = ($crypto == 1) ? "Crypto Deposit/Transfer" : "Local Bank Deposit/Transfer";
                            ?>
                                <div class="mt-3">
                                    <p>Total verification amount: <?= htmlspecialchars($currency) ?><?= htmlspecialchars(number_format($amount, 2)) ?></p>
                                    <p>Choose a part payment plan and send the first installment to the <?= htmlspecialchars($method_label) ?> details provided.</p>
                                    <h6><?= htmlspecialchars($channel_label) ?>: <?= htmlspecialchars($channel_value) ?></h6>
                                    <h6><?= htmlspecialchars($channel_name_label) ?>: <?= htmlspecialchars($channel_name_value) ?></h6>
                                    <h6><?= htmlspecialchars($channel_number_label) ?>: <?= htmlspecialchars($channel_number_value) ?></h6>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-6 mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title">2-Times Payment Plan</h5>
                                                <p class="card-text">Pay <?= htmlspecialchars($currency) ?><?= htmlspecialchars(number_format($two_times_payment, 2)) ?> twice.</p>
                                                <form action="part-payment.php" method="POST" enctype="multipart/form-data" id="twoTimesForm">
                                                    <input type="hidden" name="payment_plan" value="2-times">
                                                    <input type="hidden" name="amount" value="<?= htmlspecialchars($two_times_payment) ?>">
                                                    <div class="mb-3">
                                                        <label for="payment_proof_two" class="form-label">Upload First Payment Proof (JPG, JPEG, PNG)</label>
                                                        <input type="file" class="form-control" id="payment_proof_two" name="payment_proof" accept="image/jpeg,image/jpg,image/png" required>
                                                    </div>
                                                    <button type="submit" name="submit_payment" class="btn btn-primary">Submit First Payment</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title">4-Times Payment Plan</h5>
                                                <p class="card-text">Pay <?= htmlspecialchars($currency) ?><?= htmlspecialchars(number_format($four_times_payment, 2)) ?> four times.</p>
                                                <form action="part-payment.php" method="POST" enctype="multipart/form-data" id="fourTimesForm">
                                                    <input type="hidden" name="payment_plan" value="4-times">
                                                    <input type="hidden" name="amount" value="<?= htmlspecialchars($four_times_payment) ?>">
                                                    <div class="mb-3">
                                                        <label for="payment_proof_four" class="form-label">Upload First Payment Proof (JPG, JPEG, PNG)</label>
                                                        <input type="file" class="form-control" id="payment_proof_four" name="payment_proof" accept="image/jpeg,image/jpg,image/png" required>
                                                    </div>
                                                    <button type="submit" name="submit_payment" class="btn btn-primary">Submit First Payment</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } else { ?>
                                <p>No payment details available for your country. Please contact support.</p>
                                <?php
                                error_log("part-payment.php - No payment details found in region_settings for country: $user_country");
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } else { ?>
        <div class="container text-center">
            <p>No payment details available for your country. Please contact support.</p>
        </div>
    <?php } ?>
</main>

<!-- JavaScript for Client-Side Validation -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const forms = [document.getElementById('twoTimesForm'), document.getElementById('fourTimesForm')];
    const fileInputs = [document.getElementById('payment_proof_two'), document.getElementById('payment_proof_four')];

    forms.forEach((form, index) => {
        if (form && fileInputs[index]) {
            const feedbackContainer = document.createElement('div');
            form.parentNode.insertBefore(feedbackContainer, form);

            form.addEventListener('submit', function (event) {
                feedbackContainer.innerHTML = '';

                if (!fileInputs[index].files || fileInputs[index].files.length === 0) {
                    event.preventDefault();
                    feedbackContainer.innerHTML = `
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <strong>Please upload a payment receipt:</strong> Select a JPG, JPEG, or PNG file to proceed.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                }
            });

            fileInputs[index].addEventListener('change', function () {
                if (fileInputs[index].files && fileInputs[index].files.length > 0) {
                    feedbackContainer.innerHTML = '';
                }
            });
        }
    });
});
</script>

<?php include('inc/footer.php'); ?>
