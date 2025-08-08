<?php
session_start();
include('inc/header.php');
include('inc/navbar.php');
include('inc/sidebar.php');
include('../config/dbcon.php'); // Include database connection
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>All Deposits</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard">Home</a></li>
                <li class="breadcrumb-item">Users</li>
                <li class="breadcrumb-item active">All Deposits</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <div class="card">
        <div class="card-body">
            <!-- Bordered Table -->
            <div class="table-responsive">
                <table class="table table-borderless">
                    <thead>
                        <tr>
                            <th scope="col">Amount</th>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Payment Proof</th>
                            <th scope="col">Status</th>
                            <th scope="col">Date</th>
                            <th scope="col">Edit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch all deposits
                        $query = "SELECT amount, currency, name, email, image, status, created_at FROM deposits";
                        $query_run = mysqli_query($con, $query);
                        if (mysqli_num_rows($query_run) > 0) {
                            foreach ($query_run as $data) {
                                // Sanitize data to prevent XSS
                                $amount = htmlspecialchars($data['amount']);
                                $currency = htmlspecialchars($data['currency'] ?? '$'); // Fallback to '$' if currency is null
                                $name = htmlspecialchars($data['name']);
                                $email = htmlspecialchars($data['email']);
                                $image = htmlspecialchars($data['image']);
                                $status = $data['status'];
                                $created_at = date('d-M-Y', strtotime($data['created_at']));
                        ?>
                                <tr>
                                    <td><?= $currency ?> <?= number_format($amount, 2) ?></td>
                                    <td><?= $name ?></td>
                                    <td><?= $email ?></td>
                                    <td>
                                        <?php if ($image) { ?>
                                            <img src="../Uploads/<?= $image ?>" style="width:50px;height:50px" alt="Payment Proof" class="">
                                        <?php } else { ?>
                                            No Image
                                        <?php } ?>
                                    </td>
                                    <?php
                                    if ($status == 0) { ?>
                                        <td><span class="badge bg-warning text-light">Pending</span></td>
                                    <?php } elseif ($status == 1) { ?>
                                        <td><span class="badge bg-danger text-light">Rejected</span></td>
                                    <?php } else { ?>
                                        <td><span class="badge bg-success text-light">Completed</span></td>
                                    <?php } ?>
                                    <td><?= $created_at ?></td>
                                    <td>
                                        <a href="edit-user.php?email=<?= urlencode($email) ?>" class="btn btn-light">Edit</a>
                                    </td>
                                </tr>
                        <?php
                            }
                        } else { ?>
                            <tr>
                                <td colspan="7">No deposits found.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <!-- End Bordered Table -->
        </div>
    </div>
</main><!-- End #main -->

<?php include('inc/footer.php'); ?>
</html>
