<?php
session_start();
include('../config/dbcon.php');
include('inc/header.php');
include('inc/navbar.php');
include('inc/sidebar.php');
?>

<main id="main" class="main">

    <div class="pagetitle">
        <h1>Edit User Details</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard">Home</a></li>
                <li class="breadcrumb-item">Users</li>
                <li class="breadcrumb-item active">Edit User</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <style>
        .add-btn {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin: 15px 0;
        }
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
    </style>

    <div class="container">
        <div class="row">
            <div class="card" style="padding:10px">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                <form action="codes/users.php" method="POST">
                    <?php
                    if (isset($_GET['id'])) {
                        $id = mysqli_real_escape_string($con, $_GET['id']);
                        
                        $query = "SELECT * FROM users WHERE id='$id' LIMIT 1";
                        $query_run = mysqli_query($con, $query);

                        if ($query_run && mysqli_num_rows($query_run) > 0) {
                            $row = mysqli_fetch_array($query_run);
                            $name = $row['name'];
                            $id = $row['id'];
                            $balance = $row['balance'];
                            $email = $row['email'];
                            $wallet = $row['btc_wallet'];
                            $referral = $row['refered_by'];
                            $country = $row['country'];
                            $bonus = $row['referal_bonus'];
                            $message = $row['message'] ?? ''; // Default to empty string if NULL
                        } else {
                            echo '<div class="alert alert-danger">User not found.</div>';
                            error_log("edit_user.php - User not found for id: $id");
                            exit;
                        }
                    } else {
                        echo '<div class="alert alert-danger">No user ID provided.</div>';
                        error_log("edit_user.php - No user ID provided in GET request");
                        exit;
                    }
                    ?>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="name" class="mb-2">Name</label>
                            <input type="text" id="name" class="form-control" value="<?= htmlspecialchars($name) ?>" readonly>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="email" class="mb-2">Email</label>
                            <input name="email" type="email" id="email" class="form-control" required value="<?= htmlspecialchars($email) ?>">
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="country" class="mb-2">Country</label>
                            <input type="text" id="country" class="form-control" value="<?= htmlspecialchars($country) ?>" readonly>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="wallet" class="mb-2">Bitcoin Wallet</label>
                            <input type="text" id="wallet" class="form-control" value="<?= htmlspecialchars($wallet) ?>" readonly>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="balance" class="mb-2">Balance</label>
                            <input name="balance" type="number" id="balance" class="form-control" required value="<?= htmlspecialchars($balance) ?>">
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="referal_bonus" class="mb-2">Referral Bonus</label>
                            <input name="referal_bonus" type="number" id="referal_bonus" class="form-control" required value="<?= htmlspecialchars($bonus) ?>">
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="password" class="mb-2">New Password (leave blank to keep unchanged)</label>
                            <input name="password" type="password" id="password" class="form-control" placeholder="Enter new password">
                        </div>
                        <!-- Show Notification Section -->
                        <div class="col-md-12 form-group mb-3">
                            <label for="message" class="mb-2">Notification Message</label>
                            <textarea name="message" id="message" class="form-control" rows="4" placeholder="Enter notification message for this user"><?= htmlspecialchars($message) ?></textarea>
                        </div>
                    </div>
                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($id) ?>">
                    <button type="submit" class="btn btn-secondary" name="update_user" value="<?= htmlspecialchars($id) ?>">Update</button>
                </form>
            </div>
        </div>
        <div class="add-btn">
            <a href="manage-users" class="btn btn-secondary">Back</a>
        </div>
    </div>

</main><!-- End #main -->

<?php include('inc/footer.php'); ?>
</html>
