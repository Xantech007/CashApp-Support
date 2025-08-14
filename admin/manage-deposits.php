<?php
session_start();
include('inc/header.php');
include('inc/navbar.php');
include('inc/sidebar.php');
include('../config/dbcon.php'); // Include database connection
include('inc/countries.php'); // Include countries array
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
                            <th scope="col">Time</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch all deposits with user country
                        $query = "SELECT d.amount, d.currency, d.name, d.email, d.image, d.status, d.created_at, u.id AS user_id, u.country AS user_country 
                                  FROM deposits d 
                                  LEFT JOIN users u ON d.email = u.email 
                                  ORDER BY d.created_at DESC";
                        $query_run = mysqli_query($con, $query);
                        if ($query_run === false) {
                            echo "<tr><td colspan='8'>Error fetching deposits: " . mysqli_error($con) . "</td></tr>";
                        } elseif (mysqli_num_rows($query_run) > 0) {
                            foreach ($query_run as $data) {
                                // Sanitize data to prevent XSS
                                $amount = htmlspecialchars($data['amount']);
                                $currency = htmlspecialchars($data['currency'] ?? '$');
                                $name = htmlspecialchars($data['name']);
                                $email = htmlspecialchars($data['email'] ?? 'No Email');
                                $image = htmlspecialchars($data['image']);
                                $status = $data['status'];
                                $user_id = htmlspecialchars($data['user_id'] ?? '');
                                $user_country = htmlspecialchars($data['user_country'] ?? '');

                                // Add 5 hours to the created_at timestamp
                                $dateTime = new DateTime($data['created_at']);
                                $dateTime->modify('+5 hours');
                                $created_at = $dateTime->format('d-M-Y');
                                $time = $dateTime->format('H:i:s');
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
                                    <td><?= $time ?></td>
                                    <td>
                                        <?php if ($image) { ?>
                                            <a href="../Uploads/<?= $image ?>" download class="btn btn-light btn-sm me-1">Download</a>
                                        <?php } ?>
                                        <?php if ($user_id) { ?>
                                            <a href="edit-user.php?id=<?= urlencode($user_id) ?>" class="btn btn-light btn-sm me-1">Edit</a>
                                        <?php } else { ?>
                                            <span class="text-muted">No User</span>
                                        <?php } ?>
                                        <?php
                                        // Show "Add Button" only if user country matches admin's country
                                        if ($user_id && $user_country && $user_country === $_SESSION['admin_country']) { ?>
                                            <button type="button" class="btn btn-primary btn-sm me-1" data-bs-toggle="modal" data-bs-target="#addButtonModal" data-user-id="<?= $user_id ?>" data-email="<?= $email ?>">Add Button</button>
                                        <?php } ?>
                                    </td>
                                </tr>
                        <?php
                            }
                        } else { ?>
                            <tr>
                                <td colspan="8">No deposits found.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <!-- End Bordered Table -->
        </div>
    </div>

    <!-- Modal for Adding Action Button -->
    <div class="modal fade" id="addButtonModal" tabindex="-1" aria-labelledby="addButtonModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addButtonModalLabel">Add Action Button</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addButtonForm" action="save_action_button.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="user_id">
                        <input type="hidden" name="email" id="email">
                        <div class="mb-3">
                            <label for="country" class="form-label">Country</label>
                            <select class="form-select" id="country" name="country" required>
                                <option value="">Select Country</option>
                                <?php foreach ($countries as $country) { ?>
                                    <option value="<?= htmlspecialchars($country) ?>" <?= ($country === $_SESSION['admin_country']) ? 'selected' : '' ?>><?= htmlspecialchars($country) ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="button_name" class="form-label">Button Name</label>
                            <input type="text" class="form-control" id="button_name" name="button_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="button_msg" class="form-label">Button Message</label>
                            <textarea class="form-control" id="button_msg" name="button_msg" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Button</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main><!-- End #main -->

<?php include('inc/footer.php'); ?>

<!-- JavaScript to Pass Data to Modal -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    var addButtonModal = document.getElementById('addButtonModal');
    addButtonModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var userId = button.getAttribute('data-user-id');
        var email = button.getAttribute('data-email');
        var modal = this;
        modal.querySelector('#user_id').value = userId;
        modal.querySelector('#email').value = email;
    });
});
</script>

</html>
