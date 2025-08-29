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

// Get user country to ensure consistency
$email = mysqli_real_escape_string($con, $_SESSION['email']);
$user_query = "SELECT country FROM users WHERE email = '$email' LIMIT 1";
$user_query_run = mysqli_query($con, $user_query);
if ($user_query_run && mysqli_num_rows($user_query_run) > 0) {
    $user_data = mysqli_fetch_assoc($user_query_run);
    $user_country = $user_data['country'];
} else {
    $_SESSION['error'] = "User not found.";
    error_log("part-payment.php - User not found for email: $email");
    header("Location: ../signin.php");
    exit(0);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['payment_plan'])) {
        $payment_plan = trim($_POST['payment_plan']);
        if (in_array($payment_plan, ['1', '2', '4'])) {
            // Store selected payment plan in session
            $_SESSION['payment_plan'] = $payment_plan;
            error_log("part-payment.php - Selected payment plan: $payment_plan, redirecting to verify-complete.php");
            header("Location: verify-complete.php?verification_method=" . urlencode($_GET['verification_method'] ?? 'Local Bank Deposit/Transfer'));
            exit(0);
        } else {
            $_SESSION['error'] = "Invalid payment plan selected.";
            error_log("part-payment.php - Invalid payment plan: $payment_plan");
        }
    }
}
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Select Payment Plan</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../users/index.php">Home</a></li>
                <li class="breadcrumb-item">Verify</li>
                <li class="breadcrumb-item active">Payment Plan</li>
            </ol>
        </nav>
    </div>

    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['error'])) { ?>
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

    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-header">
                        Choose a Payment Plan
                    </div>
                    <div class="card-body mt-2">
                        <p>Select how many installments you would like to pay for the verification amount.</p>
                        <form action="part-payment.php" method="POST" id="paymentPlanForm">
                            <div class="d-flex flex-column align-items-center mt-3">
                                <button type="submit" name="payment_plan" value="1" class="btn btn-primary mb-2 w-50">One Time Payment</button>
                                <button type="submit" name="payment_plan" value="2" class="btn btn-primary mb-2 w-50">2 Times Payment</button>
                                <button type="submit" name="payment_plan" value="4" class="btn btn-primary mb-2 w-50">4 Times Payment</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include('inc/footer.php'); ?>
