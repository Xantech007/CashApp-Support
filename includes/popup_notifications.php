<!-- Toast Notification Container -->
<div id="toast-container" style="position: fixed; bottom: 20px; right: 20px; z-index: 1000;"></div>

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

<script>
function showToast(message) {
    const toast = $('<div class="toast">').text(message);
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
    console.log('Fetching withdrawal data...'); // Debug log
    $.ajax({
        url: 'fetch_withdrawal.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            console.log('AJAX response:', data); // Debug log
            if (data.success && data.name && data.amount) {
                showToast(`${data.name} has successfully withdrawn $${data.amount} from CASHAPP PROJECT SUPPORT PROGRAM.`);
            } else {
                console.log('No valid data:', data.error || 'Unknown error');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('AJAX error:', textStatus, errorThrown); // Debug log
        }
    });
}

function startNotifications() {
    fetchRandomWithdrawal();
    // Fixed interval for testing
    setInterval(fetchRandomWithdrawal, 10000); // 10 seconds for testing
    // For production: Math.floor(Math.random() * (15000 - 5000 + 1)) + 5000
}

$(document).ready(function() {
    console.log('Starting notifications...'); // Debug log
    startNotifications();
});
</script>
