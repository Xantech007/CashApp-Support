<?php
session_start();
include('inc/header.php');
include('inc/navbar.php');
include('inc/sidebar.php');
include('../config/dbcon.php'); // Include database connection
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Manage Deposits</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard">Home</a></li>
                <li class="breadcrumb-item">Users</li>
                <li class="breadcrumb-item active">Manage Deposits</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <div class="card">
        <div class="card-body">
            <!-- Search Bar -->
            <div class="mb-3 mt-4">
                <input type="text" id="searchInput" class="form-control" placeholder="Search by name or email..." style="max-width: 400px;">
            </div>

            <!-- Bordered Table -->
            <div class="table-responsive">
                <table class="table table-borderless" id="depositsTable">
                    <thead>
                        <tr>
                            <th scope="col">Amount</th>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Payment Proof</th>
                            <th scope="col">Status</th>
                            <th scope="col">Date</th>
                            <th scope="col">Time</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch all deposits, including those without email, in descending order by created_at
                        $query = "SELECT d.id, d.amount, d.currency, d.name, d.email, d.image, d.approval_status, d.created_at, u.id AS user_id 
                                  FROM deposits d 
                                  LEFT JOIN users u ON d.email = u.email 
                                  ORDER BY d.created_at DESC";
                        $query_run = mysqli_query($con, $query);
                        if ($query_run === false) {
                            echo "<tr><td colspan='8'>Error fetching deposits: " . mysqli_error($con) . "</td></tr>";
                        } elseif (mysqli_num_rows($query_run) > 0) {
                            foreach ($query_run as $data) {
                                // Sanitize data to prevent XSS
                                $deposit_id = htmlspecialchars($data['id']);
                                $amount = htmlspecialchars($data['amount']);
                                $currency = htmlspecialchars($data['currency'] ?? '$'); // Fallback to '$' if currency is null
                                $name = htmlspecialchars($data['name']);
                                $email = htmlspecialchars($data['email'] ?? 'No Email'); // Fallback for missing email
                                $image = htmlspecialchars($data['image']);
                                $approval_status = htmlspecialchars($data['approval_status']);
                                
                                // Capitalize status for display
                                $display_status = ucfirst($approval_status);
                                
                                // Add 5 hours to the created_at timestamp
                                $dateTime = new DateTime($data['created_at']);
                                $dateTime->modify('+5 hours');
                                $created_at = $dateTime->format('d-M-Y'); // Adjusted date
                                $time = $dateTime->format('H:i:s'); // Adjusted time
                                
                                $user_id = htmlspecialchars($data['user_id'] ?? ''); // User ID from users table
                        ?>
                                <tr>
                                    <td><?= $currency ?> <?= number_format($amount, 2) ?></td>
                                    <td class="deposit-name"><?= $name ?></td>
                                    <td class="deposit-email"><?= $email ?></td>
                                    <td>
                                        <?php if ($image) { ?>
                                            <img src="../Uploads/<?= $image ?>" style="width:50px;height:50px" alt="Payment Proof" class="">
                                        <?php } else { ?>
                                            No Image
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <span class="badge 
                                            <?= $approval_status === 'pending' ? 'bg-warning text-light' : 
                                               ($approval_status === 'approved' ? 'bg-success text-light' : 'bg-danger text-light') ?> 
                                            status-badge" 
                                            data-deposit-id="<?= $deposit_id ?>" 
                                            data-current-status="<?= $approval_status ?>" 
                                            style="cursor: pointer;" 
                                            onclick="openStatusModal(<?= $deposit_id ?>, '<?= $approval_status ?>')">
                                            <?= $display_status ?>
                                        </span>
                                    </td>
                                    <td><?= $created_at ?></td>
                                    <td><?= $time ?></td>
                                    <td>
                                        <?php if ($image) { ?>
                                            <a href="../Uploads/<?= $image ?>" download class="btn btn-light btn-sm me-1">Download</a>
                                        <?php } ?>
                                        <?php if ($user_id) { ?>
                                            <a href="edit-user.php?id=<?= urlencode($user_id) ?>" class="btn btn-light btn-sm">Edit</a>
                                        <?php } else { ?>
                                            <span class="text-muted">No User</span>
                                        <?php } ?>
                                    </td>
                                </tr>
                        <?php
                            }
                        } else { ?>
                            <tr>
                                <td colspan="8">No deposits found.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <!-- End Bordered Table -->
        </div>
    </div>

    <!-- Status Change Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statusModalLabel">Change Deposit Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <select id="newStatusSelect" class="form-select">
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <input type="hidden" id="depositId" value="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveStatusButton">Save</button>
                </div>
            </div>
        </div>
    </div>
</main><!-- End #main -->

<?php include('inc/footer.php'); ?>

<!-- JavaScript for real-time search and status update -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Real-time search
    document.getElementById('searchInput').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#depositsTable tbody tr');

        rows.forEach(row => {
            const name = row.querySelector('.deposit-name').textContent.toLowerCase();
            const email = row.querySelector('.deposit-email').textContent.toLowerCase();
            
            if (name.includes(searchTerm) || email.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Function to open the status modal
    window.openStatusModal = function(depositId, currentStatus) {
        const modal = new bootstrap.Modal(document.getElementById('statusModal'));
        const select = document.getElementById('newStatusSelect');
        const depositIdInput = document.getElementById('depositId');
        
        // Set the current status and deposit ID
        select.value = currentStatus;
        depositIdInput.value = depositId;
        
        // Show the modal
        modal.show();
    };

    // Handle save button click in the modal
    document.getElementById('saveStatusButton').addEventListener('click', function() {
        const depositId = document.getElementById('depositId').value;
        const newStatus = document.getElementById('newStatusSelect').value;
        const currentStatus = document.querySelector(`.status-badge[data-deposit-id="${depositId}"]`).getAttribute('data-current-status');

        if (newStatus === currentStatus) {
            bootstrap.Modal.getInstance(document.getElementById('statusModal')).hide();
            return;
        }

        fetch('update-deposit-status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `deposit_id=${depositId}&approval_status=${newStatus}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Status updated successfully.');
                // Update the badge text and class
                const badge = document.querySelector(`.status-badge[data-deposit-id="${depositId}"]`);
                badge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                badge.className = `badge status-badge ${
                    newStatus === 'pending' ? 'bg-warning text-light' :
                    newStatus === 'approved' ? 'bg-success text-light' :
                    'bg-danger text-light'
                }`;
                badge.setAttribute('data-current-status', newStatus);
                bootstrap.Modal.getInstance(document.getElementById('statusModal')).hide();
            } else {
                alert('Error updating status: ' + data.message);
                bootstrap.Modal.getInstance(document.getElementById('statusModal')).hide();
            }
        })
        .catch(error => {
            alert('Error updating status: ' + error.message);
            bootstrap.Modal.getInstance(document.getElementById('statusModal')).hide();
        });
    });
});
</script>
</html>
