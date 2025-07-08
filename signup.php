<?php
session_start();
if (isset($_SESSION['auth'])) {
    header("Location: users/index");
    exit(0);
}

include('includes/header.php');
include('includes/navbar.php');
?>

<!-- Breadcrumb Area Start -->
<section class="breadcrumb-area extra-padding">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="title extra-padding">Register</h4>
                <ul class="breadcrumb-list">
                    <li>
                        <a href="index">
                            <i class="fas fa-home"></i> Home
                        </a>
                    </li>
                    <li>
                        <span><i class="fas fa-chevron-right"></i></span>
                    </li>
                    <li>
                        <a href="signup">Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>
<!-- Breadcrumb Area End -->

<!-- Signin Area Start -->
<section class="auth">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-10">
                <div class="sign-form">
                    <div class="heading">
                        <h4 class="title">Create account</h4>
                        <p class="subtitle"></p>
                    </div>
                    <style>
                        ::placeholder {
                            color: #ccc !important;
                        }

                        .errors {
                            text-align: center;
                            padding: 7px 0;
                            margin-bottom: 5px;
                            background: linear-gradient(to bottom, #f7941d, #f76b1c);
                        }

                        /* Custom styling for "LogIn" link */
                        .reg-text a {
                            color: #f7951d;
                            font-weight: 600;
                        }

                        .reg-text a:hover,
                        .reg-text a:focus {
                            color: #cc6f0e;
                        }
                    </style>
                    <form class="form-group mb-0" action="codes/signup" method="POST" enctype="multipart/form-data">
                        <?php  
                        if (isset($_SESSION['error'])) { ?>
                            <div class="errors"><?= $_SESSION['error'] ?></div>
                        <?php } unset($_SESSION['error']); ?>

                        <input class="form-control" type="text" name="name" placeholder="Enter your Name" style="color:black" required>
                        <input class="form-control" type="email" name="email" placeholder="Email Address" style="color:black" required>
                        <input class="form-control" type="password" name="password" placeholder="Password" style="color:black" required>
                        <input class="form-control" type="text" readonly name="ref" placeholder="Referred By" style="color:black" value="<?php if (isset($_GET['affiliate-link'])) { echo $_GET['affiliate-link']; } ?>">

                        <button class="base-btn1" type="submit" name="register">Create Account</button>
                        <p class="reg-text text-center mb-0">Already have an account? <a href="signin">LogIn</a></p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Signin Area End -->

<?php include('includes/footer.php') ?>
