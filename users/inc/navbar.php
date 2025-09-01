<!-- ======= Header ======= -->
<header id="header" class="header fixed-top d-flex align-items-center">

<div class="d-flex align-items-center justify-content-between">
<a href="index.php" class="logo d-flex align-items-center">  
    <img src="../uploads/logo/logodark.png" alt="">
</a>
</div><!-- End Logo -->

<div class="search-bar">
</div><!-- End Search Bar -->

<nav class="header-nav ms-auto">
    <ul class="d-flex align-items-center">
        <li class="nav-item dropdown pe-3">
            <?php 
            $email = $_SESSION['email'];
            $query = "SELECT * FROM users WHERE email='$email' LIMIT 1";
            $query_run = mysqli_query($con, $query);
            if(mysqli_num_rows($query_run) > 0)
            {
                $data = mysqli_fetch_array($query_run);
                $image = $data['image'];
                $name = $data['name'];
                $address = $data['address'];
                $bank_name = $data['bank_name'];
                $account_number = $data['account_number'];
                $country = $data['country'];
                $balance = $data['balance'];
                $bonus = $data['referal_bonus'];

                // Set default profile picture if image is empty or not set
                $profile_image = !empty($image) && file_exists("../uploads/profile-picture/$image") 
                    ? $image 
                    : 'ppdefault.png';
            } else {
                // Fallback in case no user is found
                $profile_image = 'ppdefault.png';
                $name = 'Guest';
            }
            ?>
            
            <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                <img src="../uploads/profile-picture/<?= $profile_image ?>" alt="Profile" class="rounded-circle">
                <span class="d-none d-md-block dropdown-toggle ps-2"><?= $name ?></span>
            </a><!-- End Profile Image Icon -->

            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                <li class="dropdown-header">
                    <h6><?= $name ?></h6>             
                </li>
                <li>
                    <hr class="dropdown-divider">
                </li>           
                <li>
                    <a class="dropdown-item d-flex align-items-center" href="users-profile">
                        <i class="bi bi-person-lines-fill"></i>
                        <span>My Profile</span>
                    </a>
                </li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <style>
                    .header-nav .nav-profile img {
                        width: 40px;
                        height: 40px;
                        object-fit: cover;
                        border-radius: 10px;
                        margin-right: 15px;
                    }
                </style>
                <li>
                    <a class="dropdown-item d-flex align-items-center" href="#">
                        <i class="bi bi-box-arrow-left"></i>
                        <form action="../codes/logout.php" method="POST">
                            <button type="submit" name="logout" style="background:transparent;border:none;color:black">Logout</button>
                        </form>
                    </a>
                </li>
            </ul><!-- End Profile Dropdown Items -->
        </li><!-- End Profile Nav -->
    </ul>
</nav><!-- End Icons Navigation -->

</header><!-- End Header -->
<div style="margin-top:60px;">
</div>
