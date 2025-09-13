<?php
session_start();
include('../config/dbcon.php');
include('inc/header.php');
include('inc/navbar.php');
include('inc/sidebar.php');
?>

<main id="main" class="main">

    <div class="pagetitle">
        <h1>Region Settings</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard">Home</a></li>
                <li class="breadcrumb-item">Settings</li>
                <li class="breadcrumb-item active">Region Settings</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <!-- Error/Success Messages -->
    <?php if (isset($_SESSION['error'])) { ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php } unset($_SESSION['error']); ?>
    <?php if (isset($_SESSION['success'])) { ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php } unset($_SESSION['success']); ?>

    <style>
        .add-btn {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 15px 0;
        }
        .form-control {
            color: black;
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 10px;
            width: 100%;
        }
        .form-control:focus {
            border-color: #f7951d;
            outline: none;
            box-shadow: 0 0 5px rgba(247, 149, 29, 0.3);
        }
        select.form-control {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url('data:image/svg+xml;utf8,<svg fill="#000000" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>');
            background-repeat: no-repeat;
            background-position: right 10px center;
        }
        .modal-body .row {
            margin-bottom: 10px;
        }
        .modal-body label {
            font-weight: 500;
        }
        .qr-preview {
            max-width: 100px;
            max-height: 100px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin: 5px 0;
        }
        .table img {
            max-width: 50px;
            max-height: 50px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>

    <div class="add-btn">
        <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addRegionModal">Add New Region</button>
    </div>

    <!-- Add Region Modal -->
    <div class="modal fade" id="addRegionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Region</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="codes/region_settings.php" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="country">Country</label>
                                <select class="form-control" name="country" required>
                                    <option value="" disabled selected>Select Country</option>
                                    <?php
                                    include('inc/countries.php');
                                    if (isset($countries) && !empty($countries)) {
                                        foreach ($countries as $country) {
                                            echo '<option value="' . htmlspecialchars($country) . '">' . htmlspecialchars($country) . '</option>';
                                        }
                                    } else {
                                        echo '<option value="" disabled>No countries available</option>';
                                        error_log("region_settings.php - Countries array not set or empty");
                                        $_SESSION['error'] = "Country list unavailable. Please contact support.";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="currency">Currency</label>
                                <input type="text" class="form-control" name="currency" placeholder="" required>
                            </div>
                            <div class="col-md-3">
                                <label for="alt_currency">Alt Currency</label>
                                <input type="text" class="form-control" name="alt_currency" placeholder="">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="crypto">Crypto Payment</label>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="crypto" id="crypto" value="1">
                                    <label class="form-check-label" for="crypto">Enable Crypto Deposit/Transfer</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="Channel">Channel</label>
                                <input type="text" class="form-control" name="Channel" placeholder="" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="alt_channel">Alt Channel</label>
                                <input type="text" class="form-control" name="alt_channel" placeholder="">
                            </div>
                            <div class="col-md-6">
                                <label for="Channel_name">Channel Name</label>
                                <input type="text" class="form-control" name="Channel_name" placeholder="" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="alt_ch_name">Alt Channel Name</label>
                                <input type="text" class="form-control" name="alt_ch_name" placeholder="">
                            </div>
                            <div class="col-md-6">
                                <label for="Channel_number">Channel Number</label>
                                <input type="text" class="form-control" name="Channel_number" placeholder="" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="alt_ch_number">Alt Channel Number</label>
                                <input type="text" class="form-control" name="alt_ch_number" placeholder="">
                            </div>
                            <div class="col-md-6">
                                <label for="chnl_value">Channel Value</label>
                                <input type="text" class="form-control" name="chnl_value" placeholder="">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="chnl_name_value">Channel Name Value</label>
                                <input type="text" class="form-control" name="chnl_name_value" placeholder="">
                            </div>
                            <div class="col-md-6">
                                <label for="chnl_number_value">Channel Number Value</label>
                                <input type="text" class="form-control" name="chnl_number_value" placeholder="">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="payment_amount">Payment Amount</label>
                                <input type="number" step="0.01" class="form-control" name="payment_amount" placeholder="" required>
                            </div>
                            <div class="col-md-6">
                                <label for="rate">Rate</label>
                                <input type="number" step="0.01" class="form-control" name="rate" placeholder="" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="alt_rate">Alt Rate</label>
                                <input type="text" class="form-control" name="alt_rate" placeholder="">
                            </div>
                        </div>

                        <!-- New: QR Image Upload Section for Add Modal -->
                        <div class="row">
                            <div class="col-md-12">
                                <label for="qr_image">QR Code Image (Optional)</label>
                                <input type="file" class="form-control" name="qr_image" id="qr_image" accept="image/jpeg,image/jpg,image/png">
                                <small class="form-text text-muted">Upload a QR code image (JPG, JPEG, PNG). Max size: 5MB.</small>
                                <div id="qr_preview_add" class="mt-2"></div>
                            </div>
                        </div>

                        <input type="hidden" name="auth_id" value="<?= $_SESSION['id'] ?>">
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-secondary" name="add_region">Add Region</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Region Settings List</h5>
            <!-- Bordered Table -->
            <div class="table-responsive">
                <table class="table table-borderless">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Country</th>
                            <th scope="col">Currency</th>
                            <th scope="col">Alt Currency</th>
                            <th scope="col">Crypto</th>
                            <th scope="col">Channel</th>
                            <th scope="col">Alt Channel</th>
                            <th scope="col">Channel Name</th>
                            <th scope="col">Alt Channel Name</th>
                            <th scope="col">Channel Number</th>
                            <th scope="col">Alt Channel Number</th>
                            <th scope="col">Channel Value</th>
                            <th scope="col">Channel Name Value</th>
                            <th scope="col">Channel Number Value</th>
                            <th scope="col">QR Image</th>
                            <th scope="col">Payment Amount</th>
                            <th scope="col">Rate</th>
                            <th scope="col">Alt Rate</th>
                            <th scope="col">Edit</th>
                            <th scope="col">Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $user_id = $_SESSION['id'];
                        $query = "SELECT * FROM region_settings";
                        $query_run = mysqli_query($con, $query);
                        if (mysqli_num_rows($query_run) > 0) {
                            foreach ($query_run as $data) { ?>
                                <tr>
                                    <td><?= htmlspecialchars($data['id']) ?></td>
                                    <td><?= htmlspecialchars($data['country']) ?></td>
                                    <td><?= htmlspecialchars($data['currency']) ?></td>
                                    <td><?= htmlspecialchars($data['alt_currency'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($data['crypto'] == 1 ? 'Yes' : 'No') ?></td>
                                    <td><?= htmlspecialchars($data['Channel']) ?></td>
                                    <td><?= htmlspecialchars($data['alt_channel'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($data['Channel_name']) ?></td>
                                    <td><?= htmlspecialchars($data['alt_ch_name'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($data['Channel_number']) ?></td>
                                    <td><?= htmlspecialchars($data['alt_ch_number'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($data['chnl_value'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($data['chnl_name_value'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($data['chnl_number_value'] ?? '-') ?></td>
                                    <td>
                                        <?php if (!empty($data['qr_image']) && file_exists($data['qr_image'])): ?>
                                            <img src="<?= htmlspecialchars($data['qr_image']) ?>" alt="QR Code" title="QR Code for <?= htmlspecialchars($data['country']) ?>">
                                            <br><small><?= htmlspecialchars(basename($data['qr_image'])) ?></small>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars(number_format($data['payment_amount'], 2)) ?></td>
                                    <td><?= htmlspecialchars(number_format($data['rate'], 2)) ?></td>
                                    <td><?= htmlspecialchars($data['alt_rate'] ?? '-') ?></td>
                                    <td>
                                        <a href="edit-region.php?id=<?= $data['id'] ?>" class="btn btn-light">Edit</a>
                                    </td>
                                    <td>
                                        <form action="codes/region_settings.php" method="POST" style="display: inline;">
                                            <input type="hidden" value="<?= $user_id ?>" name="auth_id">
                                            <input type="hidden" value="<?= $data['id'] ?>" name="region_id">
                                            <button type="submit" class="btn btn-danger" name="delete" onclick="return confirm('Are you sure you want to delete this region? This will also delete the associated QR image if any.');">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr>
                                <td colspan="20">No region settings found.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <!-- End Bordered Table -->
        </div>
    </div>

</main><!-- End #main -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Shared function for QR preview
    function handleQrPreview(inputId, previewId) {
        const qrInput = document.getElementById(inputId);
        const qrPreview = document.getElementById(previewId);
        if (qrInput && qrPreview) {
            qrInput.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    // Basic client-side validation
                    if (file.size > 5 * 1024 * 1024) {
                        alert('File size exceeds 5MB limit.');
                        event.target.value = '';
                        qrPreview.innerHTML = '';
                        return;
                    }
                    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                    if (!allowedTypes.includes(file.type)) {
                        alert('Invalid file type. Only JPG, JPEG, and PNG are allowed.');
                        event.target.value = '';
                        qrPreview.innerHTML = '';
                        return;
                    }

                    // Preview the selected image
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        qrPreview.innerHTML = '<img src="' + e.target.result + '" alt="QR Preview" class="qr-preview img-fluid">';
                    };
                    reader.readAsDataURL(file);
                } else {
                    qrPreview.innerHTML = '';
                }
            });
        }
    }

    // For Add Modal
    handleQrPreview('qr_image', 'qr_preview_add');
});
</script>

<?php include('inc/footer.php'); ?>
