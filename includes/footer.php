	
    <!-- Footer Area Start -->
    <footer class="footer" id="footer">
	

    <div class="copy-bg">
        <div class="container">
            <div class="row">
                <div class="col-lg-5">
                    <div class="left-area">
                        <?php
                        $rights = "SELECT c_rights FROM settings";
                        $rights_query = mysqli_query($con, $rights);

                        $row = mysqli_fetch_array($rights_query);
                        $rights = $row['c_rights'];
                        ?>
                        <p>
                            <?= $rights ?>
                        </p>
                    </div>
                </div>
                <div class="col-lg-7">
                    <ul class="social-links">
                        <li>
                            <a href="#" data-toggle="tooltip" data-placement="top" title="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                        </li>
                        <li>
                            <a href="#" data-toggle="tooltip" data-placement="top" title="Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                        </li>
                        <li>
                            <a href="#" data-toggle="tooltip" data-placement="top" title="Linkedin">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </li>
                        <li>
                            <a href="#" data-toggle="tooltip" data-placement="top" title="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                        </li>
                        <li>
                            <a href="#" data-toggle="tooltip" data-placement="top" title="Pinterest">
                                <i class="fab fa-pinterest-p"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer> 
<!-- Footer Area End -->





<!-- jquery -->
<script src="assets/js/jquery.js"></script>
<!-- popper -->
<script src="assets/js/popper.min.js"></script>
<!-- bootstrap -->
<script src="assets/js/bootstrap.min.js"></script>
<!-- plugin js-->
<script src="assets/js/plugin.js"></script>
<!-- main -->
<script src="assets/js/main.js"></script>

</body>



</html>
