<?php
session_start();
include('inc/header.php');
include('inc/navbar.php');
include('inc/sidebar.php');
include('../config/dbcon.php'); // Include database connection
include('inc/countries.php'); // Include the country list

// Handle form submission for adding or editing a button
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_button'])) {
        // Add new button
        $country = mysqli_real_escape_string($con, $_POST['country']);
        $button_name = mysqli_real_escape_string($con, $_POST['button_name']);
        $button_msg = mysqli_real_escape_string($con, $_POST['button_msg']);

        $query = "INSERT INTO action_button (country, button_name, button_msg) VALUES ('$country', '$button_name', '$button_msg')";
        if (mysqli_query($con, $query)) {
            $_SESSION['message'] = "Button added successfully!";
            header('Location: manage-deposits.php');
            exit();
        } else {
            $_SESSION['error'] = "Error adding button: " . mysqli_error($con);
        }
    } elseif (isset($_POST['update_button'])) {
        // Update existing button
        $id = mysqli_real_escape_string($con, $_POST['id']);
        $country = mysqli_real_escape_string($con, $_POST['country']);
        $button_name = mysqli_real_escape_string($con, $_POST['button_name']);
        $button_msg = mysqli_real_escape_string($con, $_POST['button_msg']);

        $query = "UPDATE action_button SET country='$country', button_name='$button_name', button_msg='$button_msg' WHERE id='$id'";
        if (mysqli_query($con, $query)) {
            $_SESSION['message'] = "Button updated successfully!";
            header('Location: manage-deposits.php');
            exit();
        } else {
            $_SESSION['error'] = "Error updating button: " . mysqli_error($con);
        }
    }
}

// Handle delete request
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($con, $_GET['delete']);
    $query = "DELETE FROM action_button WHERE id='$id'";
    if (mysqli_query($con, $query)) {
        $_SESSION['message'] = "Button deleted successfully!";
        header('Location: manage-deposits.php');
        exit();
    } else {
        $_SESSION['error'] = "Error deleting button: " . mysqli_error($con);
    }
}

// Fetch button data for editing
$edit_button = null;
if (isset($_GET['edit'])) {
    $id = mysqli_real_escape_string($con, $_GET['edit']);
    $query = "SELECT * FROM action_button WHERE id='$id'";
    $result = mysqli_query($con, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $edit_button = mysqli_fetch_assoc($result);
    }
}
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

    <!-- Display Messages -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Add Button Trigger Modal -->
    <div class="mb-3">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#buttonModal">
            Add Button
        </button>
    </div>

    <!-- Modal for Adding/Editing Button -->
    <div class="modal fade" id="buttonModal" tabindex="-1" aria-labelledby="buttonModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="buttonModalLabel"><?= $edit_button ? 'Edit Button' : 'Add New Button' ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="manage-deposits.php">
                        <input type="hidden" name="id" value="<?= $edit_button ? htmlspecialchars($edit_button['id']) : '' ?>">
                        <div class="mb-3">
                            <label for="country" class="form-label">Country</label>
                            <select class="form-select" id="country" name="country" required>
                                <option value="">Select Country</option>
                                <?php foreach ($countries as $country): ?>
                                    <option value="<?= htmlspecialchars($country) ?>" <?= $edit_button && $edit_button['country'] == $country ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($country) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="button_name" class="form-label">Button Name</label>
                            <input type="text" class="form-control" id="button_name" name="button_name" value="<?= $edit_button ? htmlspecialchars($edit_button['button_name']) : '' ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="button_msg" class="form-label">Button Message</label>
                            <textarea class="form-control" id="button_msg" name="button_msg" rows="4" required><?= $edit_button ? htmlspecialchars($edit_button['button_msg']) : '' ?></textarea>
                        </div>
                        <button type="submit" name="<?= $edit_button ? 'update_button' : 'add_button' ?>" class="btn btn-primary">
                            <?= $edit_button ? 'Update Button' : 'Add Button' ?>
                        </button>
                        <?php if ($edit_button): ?>
                            <a href="manage-deposits.php" class="btn btn-secondary">Cancel</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons Table -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Action Buttons List</h5>
            <div class="table-responsive">
                <table class="table table-borderless">
                    <thead>
                        <tr>
                            <th scope="col">Country</th>
                            <th scope="col">Button Name</th>
                            <th scope="col">Button Message</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM action_button ORDER BY country ASC";
                        $query_run = mysqli_query($con, $query);
                        if ($query_run === false) {
                            echo "<tr><td colspan='4'>Error fetching buttons: " . mysqli_error($con) . "</td></tr>";
                        } elseif (mysqli_num_rows($query_run) > 0) {
                            while ($row = mysqli_fetch_assoc($query_run)) {
                                $id = htmlspecialchars($row['id']);
                                $country = htmlspecialchars($row['country']);
                                $button_name = htmlspecialchars($row['button_name']);
                                $button_msg = htmlspecialchars($row['button_msg']);
                        ?>
                                <tr>
                                    <td><?= $country ?></td>
                                    <td><?= $button_name ?></td>
                                    <td><?= $button_msg ?></td>
                                    <td>
                                        <button type="button" class="btn btn-light btn-sm me-1" data-bs-toggle="modal" data-bs-target="#buttonModal" onclick="window.location.href='manage-deposits.php?edit=<?= urlencode($id) ?>'">Edit</button>
                                        <a href="manage-deposits.php?delete=<?= urlencode($id) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this button?');">Delete</a>
                                    </td>
                                </tr>
                        <?php
                            }
                        } else {
                            echo "<tr><td colspan='4'>No buttons found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Deposits Table -->
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
                        // Fetch all deposits, including those without email, in descending order by created_at
                        $query = "SELECT d.amount, d.currency, d.name, d.email, d.image, d.status, d.created_at, u.id AS user_id 
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
                                $currency = htmlspecialchars($data['currency'] ?? '$'); // Fallback to '$' if currency is null
                                $name = htmlspecialchars($data['name']);
                                $email = htmlspecialchars($data['email'] ?? 'No Email'); // Fallback for missing email
                                $image = htmlspecialchars($data['image']);
                                $status = $data['status'];
                                
                                // Add 5 hours to the created_at timestamp
                                $dateTime = new DateTime($data['created_at']);
                                $dateTime->modify('+5 hours');
                                $created_at = $dateTime->format('d-M-Y'); // Adjusted date
                                $time = $dateTime->format('H:i:s'); // Adjusted time
                                
                                $user_id = htmlspecialchars($data['user_id'] ?? ''); // User ID from users table
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
                                            <a href="edit-user.php?id=<?= urlencode($user_id) ?>" class="btn btn-light btn-sm">Edit</a>
                                        <?php } else { ?>
                                            <span class="text-muted">No User</span>
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
</main><!-- End #main -->

<?php include('inc/footer.php'); ?>
</html>
