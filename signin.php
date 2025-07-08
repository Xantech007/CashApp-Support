<!-- Signin Area Start -->
<section class="auth">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-10">
                <div class="sign-form">
                    <div class="heading">
                        <h4 class="title">
                            Hey, welcome back
                        </h4>
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
                            .reg-text a:active {
                                color: #d87a18 !important; /* Darker shade when clicked */
                            }
                        </style>
                        <p class="subtitle">
                            Sign in to continue
                        </p>
                    </div>
                    <form class="form-group mb-0" action="codes/signin" method="POST">
                        <?php  
                        if(isset($_SESSION['success']))
                        { ?>
                            <div class="success"><?= $_SESSION['success'] ?></div>
                        <?php } unset($_SESSION['success']) ?>
                        <?php  
                        if(isset($_SESSION['error']))
                        { ?>
                            <div class="error"><?= $_SESSION['error'] ?></div>
                        <?php } unset($_SESSION['error']) ?>
                        <input class="form-control" type="email" name="email" placeholder="Email" style="color: black;">
                        <input class="form-control" type="password" name="password" placeholder="Password" style="color: black;">
                        <div class="custom-control custom-checkbox d-flex">                        
                            <span class="ml-auto"><a href="forgot-pass">Forgot Password ?</a></span>
                        </div>
                        <button class="base-btn1" type="submit" name="login">Log In</button>
                        <p class="reg-text text-center mb-0">
                            Don't have an account? 
                            <a href="signup" style="color: #f7951d !important; text-decoration: none;">Register Now</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Signin Area End -->
