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
            header('Location: manage_action_buttons.php');
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
            header('Location: manage_action_buttons.php');
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
        header('Location: manage_action_buttons.php');
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
        <h1>Manage Action Buttons</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard">Home</a></li>
                <li class="breadcrumb-item">Settings</li>
                <li class="breadcrumb-item active">Manage Action Buttons</li>
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

    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><?= $edit_button ? 'Edit Button' : 'Add New Button' ?></h5>
            <!-- Form for Adding/Editing Button -->
            <form method="POST" action="manage_action_buttons.php">
                <input type="hidden" name="id" value="<?= $edit_button ? htmlspecialchars($edit_button['id']) : '' ?>">
                <div class="row mb-3">
                    <label for="country" class="col-sm-2 col-form-label">Country</label>
                    <div class="col-sm-10">
                        <select class="form-select" id="country" name="country" required>
                            <option value="">Select Country</option>
                            <?php foreach ($countries as $country): ?>
                                <option value="<?= htmlspecialchars($country) ?>" <?= $edit_button && $edit_button['country'] == $country ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($country) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="button_name" class="col-sm-2 col-form-label">Button Name</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="button_name" name="button_name" value="<?= $edit_button ? htmlspecialchars($edit_button['button_name']) : '' ?>" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="button_msg" class="col-sm-2 col-form-label">Button Message</label>
                    <div class="col-sm-10">
                        <textarea class="form-control" id="button_msg" name="button_msg" rows="4" required><?= $edit_button ? htmlspecialchars($edit_button['button_msg']) : '' ?></textarea>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-10 offset-sm-2">
                        <button type="submit" name="<?= $edit_button ? 'update_button' : 'add_button' ?>" class="btn btn-primary">
                            <?= $edit_button ? 'Update Button' : 'Add Button' ?>
                        </button>
                        <?php if ($edit_button): ?>
                            <a href="manage_action_buttons.php" class="btn btn-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table to Display Action Buttons -->
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
                                        <a href="manage_action_buttons.php?edit=<?= urlencode($id) ?>" class="btn btn-light btn-sm me-1">Edit</a>
                                        <a href="manage_action_buttons.php?delete=<?= urlencode($id) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this button?');">Delete</a>
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
</main><!-- End #main -->

<?php include('inc/footer.php'); ?>
</html>
