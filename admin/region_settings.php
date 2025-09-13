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
        .file-upload-area {
            border: 2px dashed #ccc;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            background-color: #f8f9fa;
            transition: border-color 0.3s ease;
        }
        .file-upload-area:hover {
            border-color: #f7951d;
        }
        .file-upload-area.dragover {
            border-color: #f7951d;
            background-color: #fff3cd;
        }
        .preview-img {
            max-width: 200px;
            max-height: 200px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-top: 10px;
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
                            <div class="col-md-6">
                                <label for="qr_image">QR/Image Upload</label>
                                <input type="file" class="form-control" name="qr_image" id="qr_image" accept="image/*">
                                <small class="text-muted">Upload QR code (for crypto) or bank logo (for local bank)</small>
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
                            <th scope="col">Payment Amount</th>
                            <th scope="col">Rate</th>
                            <th scope="col">Alt Rate</th>
                            <th scope="col">QR/Image</th>
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
                                    <td><?= htmlspecialchars(number_format($data['payment_amount'], 2)) ?></td>
                                    <td><?= htmlspecialchars(number_format($data['rate'], 2)) ?></td>
                                    <td><?= htmlspecialchars($data['alt_rate'] ?? '-') ?></td>
                                    <td>
                                        <?php if (!empty($data['qr_image']) && file_exists($data['qr_image'])): ?>
                                            <img src="<?= htmlspecialchars($data['qr_image']) ?>" alt="QR/Image Preview" class="preview-img">
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="edit-region.php?id=<?= $data['id'] ?>" class="btn btn-light">Edit</a>
                                    </td>
                                    <td>
                                        <form action="codes/region_settings.php" method="POST">
                                            <input type="hidden" value="<?= $user_id ?>" name="auth_id">
                                            <button class="btn btn-danger" value="<?= $data['id'] ?>" name="delete">Delete</button>
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
// JavaScript for drag and drop file upload (optional enhancement)
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('qr_image');
    const uploadArea = fileInput.closest('.col-md-6'); // Adjust selector if needed

    if (fileInput) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            uploadArea.classList.add('dragover');
        }

        function unhighlight(e) {
            uploadArea.classList.remove('dragover');
        }

        uploadArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            fileInput.files = files;
        }

        // Preview image on select
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    let preview = uploadArea.querySelector('.preview-img');
                    if (!preview) {
                        preview = document.createElement('img');
                        preview.className = 'preview-img';
                        uploadArea.appendChild(preview);
                    }
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>

<?php include('inc/footer.php'); ?>
