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
                        <p><?= $rights ?></p>
                    </div>
                </div>
                <div class="col-lg-7">
                    <ul class="social-links">
                        <li><a href="#" data-toggle="tooltip" data-placement="top" title="Facebook"><i class="fab fa-facebook-f"></i></a></li>
                        <li><a href="#" data-toggle="tooltip" data-placement="top" title="Twitter"><i class="fab fa-twitter"></i></a></li>
                        <li><a href="#" data-toggle="tooltip" data-placement="top" title="Linkedin"><i class="fab fa-linkedin-in"></i></a></li>
                        <li><a href="#" data-toggle="tooltip" data-placement="top" title="Instagram"><i class="fab fa-instagram"></i></a></li>
                        <li><a href="#" data-toggle="tooltip" data-placement="top" title="Pinterest"><i class="fab fa-pinterest-p"></i></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification Container -->
    <div id="toast-container" style="position: fixed; bottom: 20px; right: 20px; z-index: 1000;"></div>
</footer>
<!-- Footer Area End -->

<!-- CSS for Toast Notification -->
<style>
.toast {
    background: #28a745;
    color: white;
    padding: 15px 20px;
    border-radius: 5px;
    margin-bottom: 10px;
    opacity: 0;
    transition: opacity 0.5s;
    max-width: 300px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}
.toast.show {
    opacity: 1;
}
</style>

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

<script>
function showToast(name, amount) {
    const toast = $('<div class="toast">').text(`${name} has successfully withdrawn $${amount} from CASHAPP PROJECT SUPPORT PROGRAM.`);
    $('#toast-container').append(toast);
    
    setTimeout(() => {
        toast.addClass('show');
        setTimeout(() => {
            toast.removeClass('show');
            setTimeout(() => {
                toast.remove();
            }, 500);
        }, 3000);
    }, 100);
}

function fetchRandomWithdrawal() {
    $.ajax({
        url: 'fetch_withdrawal.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.name && data.amount) {
                showToast(data.name, data.amount);
            }
        },
        error: function() {
            console.log('Error fetching withdrawal data');
        }
    });
}

function startNotifications() {
    fetchRandomWithdrawal();
    setInterval(fetchRandomWithdrawal, Math.floor(Math.random() * (15000 - 5000 + 1)) + 5000);
}

$(document).ready(function() {
    startNotifications();
});
</script>

</body>
</html>
