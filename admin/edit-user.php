<?php
session_start();
include('inc/header.php');
include('inc/navbar.php');
include('inc/sidebar.php');
?>

<main id="main" class="main">
  <div class="pagetitle">
      <h1>Edit User Details</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="dashbaord">Home</a></li>
          <li class="breadcrumb-item">Users</li>
          <li class="breadcrumb-item active">Deposit</li>
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
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
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
                        $referral = $row['refered_by'];                       
                        $country = $row['country'];                       
                        $bonus = $row['referal_bonus'];                                         
                    } else {
                        echo "<div class='alert alert-danger'>User not found.</div>";
                        exit;
                    }
                } else {
                    echo "<div class='alert alert-danger'>Invalid user ID.</div>";
                    exit;
                }
                ?>
                <div class="row">
                <div class="col-md-6 form-group mb-3">
                    <label for="" class="mb-2">Name</label>
                    <input type="text" class="form-control" required value="<?= htmlspecialchars($name) ?>" readonly>
                </div>                                  
                <div class="col-md-6 form-group mb-3">
                    <label for="" class="mb-2">Email</label>
                    <input name="email" type="text" class="form-control" required value="<?= htmlspecialchars($email) ?>">
                </div>                                  
                <div class="col-md-6 form-group mb-3">
                    <label for="" class="mb-2">Country</label>
                    <input type="text" class="form-control" required value="<?= htmlspecialchars($country) ?>" readonly>
                </div> 
                <div class="col-md-6 form-group mb-3">
                    <label for="" class="mb-2">Balance</label>
                    <input name="balance" type="number" class="form-control" required value="<?= htmlspecialchars(isset($balance) && is_numeric($balance) ? $balance : 0) ?>">
                </div> 
                <div class="col-md-6 form-group mb-3">
                    <label for="" class="mb-2">Referral Bonus</label>
                    <input name="referal_bonus" type="number" class="form-control" required value="<?= htmlspecialchars(isset($bonus) && is_numeric($bonus) ? $bonus : 0) ?>">
                </div> 
                <button type="submit" class="btn btn-secondary" name="update_user" value="<?= htmlspecialchars($id) ?>">Update</button>  
                </div>  
            </form>
        </div>    
    </div>       
    <div class="add-btn">
        <a href="manage-users" class="btn btn-secondary">Back</a>
    </div>
</main><!-- End #main -->

<?php include('inc/footer.php'); ?>
