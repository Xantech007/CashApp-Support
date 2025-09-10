<!-- Pop-up Notification for Withdrawals -->
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
    display: inline;
    color: #f2d516;
    transition: all .2s ease;
}
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript">
    // Function to fetch withdrawal data from the backend
    function fetchWithdrawals() {
        return $.ajax({
            url: 'fetch_withdrawals.php', // Backend script to query the database
            method: 'GET',
            dataType: 'json'
        });
    }

    // Function to display the notification
    function showNotification() {
        fetchWithdrawals().done(function(data) {
            if (data.length > 0) {
                // Pick a random withdrawal record
                var withdrawal = data[Math.floor(Math.random() * data.length)];
                var msg = '<b>' + withdrawal.name + '</b> successfully withdrew <a href="javascript:void(0);" onclick="javascript:void(0);">$ ' + parseFloat(withdrawal.amount).toFixed(2) + '</a> from CASHAPP PROJECT SUPPORT PROGRAM now.';
                
                // Update the pop-up message and show it
                $(".mgm .txt").html(msg);
                $(".mgm").stop(true).fadeIn(300);
                
                // Hide after 6 seconds
                window.setTimeout(function() {
                    $(".mgm").stop(true).fadeOut(300);
                }, 6000);
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.error('Failed to fetch withdrawal data: ' + textStatus + ', ' + errorThrown);
        });

        // Schedule the next notification (random interval between 8-40 seconds)
        var interval = Math.floor(Math.random() * (40000 - 8000 + 1) + 8000);
        setTimeout(showNotification, interval);
    }

    // Start the notification loop
    $(document).ready(function() {
        showNotification();
    });
</script>	
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
