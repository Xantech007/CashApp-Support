<?php
session_start();
// Generate CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include('inc/header.php');
include('inc/navbar.php');
include('inc/sidebar.php');
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Pending Withdrawals</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard">Home</a></li>
                <li class="breadcrumb-item">Users</li>
                <li class="breadcrumb-item active">Pending Withdrawals</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <!-- Display Success or Error Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <!-- Bordered Table -->
            <div class="table-responsive">
                <table class="table table-borderless">
                    <thead>
                        <tr>
                            <th scope="col">Amount</th>
                            <th scope="col">Channel</th>
                            <th scope="col">Channel Name</th>
                            <th scope="col">Channel Number</th>
                            <th scope="col">Status</th>
                            <th scope="col">Date</th>
                            <th scope="col">Complete Request</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        include('../config/dbcon.php'); // Include database connection
                        // Check if database connection is successful
                        if (!$con) {
                            echo '<tr><td colspan="7" class="text-center text-danger">Database connection failed.</td></tr>';
                        } else {
                            // Query withdrawals table only
                            $query = "SELECT id, amount, channel, channel_name, channel_number, status, created_at, email 
                                      FROM withdrawals 
                                      WHERE status = '0'";
                            $query_run = mysqli_query($con, $query);
                            if (mysqli_num_rows($query_run) > 0) {
                                foreach ($query_run as $data) {
                                    // Fetch currency from region_settings based on user's email
                                    $email = $data['email'];
                                    $user_query = "SELECT country FROM users WHERE email = ? LIMIT 1";
                                    $stmt = $con->prepare($user_query);
                                    $stmt->bind_param("s", $email);
                                    $stmt->execute();
                                    $user_result = $stmt->get_result();
                                    $currency = '$'; // Default currency
                                    if ($user_result && $user_result->num_rows > 0) {
                                        $user = $user_result->fetch_assoc();
                                        $country = $user['country'];
                                        $region_query = "SELECT currency FROM region_settings WHERE country = ? LIMIT 1";
                                        $region_stmt = $con->prepare($region_query);
                                        $region_stmt->bind_param("s", $country);
                                        $region_stmt->execute();
                                        $region_result = $region_stmt->get_result();
                                        if ($region_result && $region_result->num_rows > 0) {
                                            $region = $region_result->fetch_assoc();
                                            $currency = $region['currency'] ?? '$';
                                        }
                                        $region_stmt->close();
                                    }
                                    $stmt->close();
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($currency) ?><?= number_format($data['amount'], 2) ?></td>
                                        <td><?= htmlspecialchars($data['channel'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($data['channel_name'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($data['channel_number'] ?? 'N/A') ?></td>
                                        <td><span class="badge bg-warning text-light">Pending</span></td>
                                        <td><?= date('d-M-Y', strtotime($data['created_at'])) ?></td>
                                        <td>
                                            <form action="codes/withdrawals.php" method="POST">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                <button class="btn btn-light" type="submit" name="complete" value="<?= htmlspecialchars($data['id']) ?>">Complete</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="7" class="text-center">No pending withdrawals found.</td>
                                </tr>
                                <?php
                            }
                            $con->close();
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <!-- End Bordered Table -->
        </div>
    </div>
</main><!-- End #main -->

<?php include('inc/footer.php'); ?>
</html>
