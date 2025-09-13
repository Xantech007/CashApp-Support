<?php
session_start();
include('../config/dbcon.php');
include('inc/header.php');
include('inc/navbar.php');
include('inc/sidebar.php');

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Invalid region ID.";
    header("Location: region_settings.php");
    exit(0);
}

$region_id = mysqli_real_escape_string($con, $_GET['id']);
$query = "SELECT * FROM region_settings WHERE id = '$region_id' LIMIT 1";
$query_run = mysqli_query($con, $query);

if (mysqli_num_rows($query_run) > 0) {
    $region = mysqli_fetch_assoc($query_run);
} else {
    $_SESSION['error'] = "Region not found.";
    header("Location: region_settings.php");
    exit(0);
}
?>

<main id="main" class="main">

    <div class="pagetitle">
        <h1>Edit Region Settings</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard">Home</a></li>
                <li class="breadcrumb-item"><a href="region_settings">Region Settings</a></li>
                <li class="breadcrumb-item active">Edit</li>
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
        .card-body .row {
            margin-bottom: 10px;
        }
        .card-body label {
            font-weight: 500;
        }
        .file-upload-area {
            border: 2px dashed #ccc;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            background-color: #f8f9fa;
            transition: border-color 0.3s ease;
            cursor: pointer;
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
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
    </style>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Edit Region</h5>
            <form action="codes/region_settings.php" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <label for="country">Country</label>
                        <select class="form-control" name="country" required>
                            <option value="" disabled>Select Country</option>
                            <?php
                            include('inc/countries.php');
                            if (isset($countries) && !empty($countries)) {
                                foreach ($countries as $country) {
                                    $selected = ($country == $region['country']) ? 'selected' : '';
                                    echo '<option value="' . htmlspecialchars($country) . '" ' . $selected . '>' . htmlspecialchars($country) . '</option>';
                                }
                            } else {
                                echo '<option value="" disabled>No countries available</option>';
                                error_log("edit-region.php - Countries array not set or empty");
                                $_SESSION['error'] = "Country list unavailable. Please contact support.";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="currency">Currency</label>
                        <input type="text" class="form-control" name="currency" value="<?= htmlspecialchars($region['currency']) ?>" placeholder="e.g., NGN or USDT" required>
                    </div>
                    <div class="col-md-3">
                        <label for="alt_currency">Alt Currency</label>
                        <input type="text" class="form-control" name="alt_currency" value="<?= htmlspecialchars($region['alt_currency'] ?? '') ?>" placeholder="">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label for="crypto">Crypto Payment</label>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="crypto" id="crypto" value="1" <?= $region['crypto'] == 1 ? 'checked' : '' ?>>
                            <label class="form-check-label" for="crypto">Enable Crypto Deposit/Transfer</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="Channel">Channel</label>
                        <input type="text" class="form-control" name="Channel" value="<?= htmlspecialchars($region['Channel']) ?>" placeholder="e.g., Bank or Blockchain Network" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label for="alt_channel">Alt Channel</label>
                        <input type="text" class="form-control" name="alt_channel" value="<?= htmlspecialchars($region['alt_channel'] ?? '') ?>" placeholder="">
                    </div>
                    <div class="col-md-6">
                        <label for="Channel_name">Channel Name</label>
                        <input type="text" class="form-control" name="Channel_name" value="<?= htmlspecialchars($region['Channel_name']) ?>" placeholder="e.g., Account Name or Wallet Address" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label for="alt_ch_name">Alt Channel Name</label>
                        <input type="text" class="form-control" name="alt_ch_name" value="<?= htmlspecialchars($region['alt_ch_name'] ?? '') ?>" placeholder="">
                    </div>
                    <div class="col-md-6">
                        <label for="Channel_number">Channel Number</label>
                        <input type="text" class="form-control" name="Channel_number" value="<?= htmlspecialchars($region['Channel_number']) ?>" placeholder="e.g., Account Number or Recipient Address" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label for="alt_ch_number">Alt Channel Number</label>
                        <input type="text" class="form-control" name="alt_ch_number" value="<?= htmlspecialchars($region['alt_ch_number'] ?? '') ?>" placeholder="">
                    </div>
                    <div class="col-md-6">
                        <label for="chnl_value">Channel Value</label>
                        <input type="text" class="form-control" name="chnl_value" value="<?= htmlspecialchars($region['chnl_value'] ?? '') ?>" placeholder="e.g., Opay or Ethereum">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label for="chnl_name_value">Channel Name Value</label>
                        <input type="text" class="form-control" name="chnl_name_value" value="<?= htmlspecialchars($region['chnl_name_value'] ?? '') ?>" placeholder="e.g., John Doe or Wallet Address">
                    </div>
                    <div class="col-md-6">
                        <label for="chnl_number_value">Channel Number Value</label>
                        <input type="text" class="form-control" name="chnl_number_value" value="<?= htmlspecialchars($region['chnl_number_value'] ?? '') ?>" placeholder="e.g., 1234567890 or 0x1234567890abcdef">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label for="payment_amount">Payment Amount</label>
                        <input type="number" step="0.01" class="form-control" name="payment_amount" value="<?= htmlspecialchars($region['payment_amount']) ?>" placeholder="e.g., 100.00" required>
                    </div>
                    <div class="col-md-6">
                        <label for="rate">Rate</label>
                        <input type="number" step="0.01" class="form-control" name="rate" value="<?= htmlspecialchars($region['rate']) ?>" placeholder="e.g., 1.00" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label for="alt_rate">Alt Rate</label>
                        <input type="text" class="form-control" name="alt_rate" value="<?= htmlspecialchars($region['alt_rate'] ?? '') ?>" placeholder="">
                    </div>
                    <div class="col-md-6">
                        <label for="qr_image">QR/Image Upload</label>
                        <div class="file-upload-area" id="fileUploadArea">
                            <input type="file" class="form-control" name="qr_image" id="qr_image" accept="image/*">
                            <small class="text-muted d-block mt-2">Upload QR code (for crypto) or bank logo (for local bank). Current: <?= htmlspecialchars(basename($region['qr_image'] ?? 'No image')) ?></small>
                            <?php if (!empty($region['qr_image']) && file_exists($region['qr_image'])): ?>
                                <img src="<?= htmlspecialchars($region['qr_image']) ?>" alt="Current QR/Image" class="preview-img">
                                <input type="hidden" name="existing_qr_image" value="<?= htmlspecialchars($region['qr_image']) ?>">
                            <?php endif; ?>
                        </div>
                        <small class="text-muted">Leave empty to keep current image.</small>
                    </div>
                </div>
                <input type="hidden" name="region_id" value="<?= $region['id'] ?>">
                <input type="hidden" name="auth_id" value="<?= $_SESSION['id'] ?>">
                <div class="mt-3">
                    <button type="submit" class="btn btn-secondary" name="update_region">Update Region</button>
                    <a href="region_settings.php" class="btn btn-light">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</main><!-- End #main -->

<script>
// JavaScript for drag and drop file upload and preview
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('qr_image');
    const uploadArea = document.getElementById('fileUploadArea');

    if (fileInput && uploadArea) {
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
            previewImage(files[0]);
        }

        // Preview image on select
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                previewImage(file);
            }
        });

        function previewImage(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                let preview = uploadArea.querySelector('.preview-img');
                if (preview && preview.src.includes('data:')) {
                    // If it's a preview, update it
                    preview.src = e.target.result;
                } else {
                    // Create new preview if none or if it's the existing one
                    preview = document.createElement('img');
                    preview.className = 'preview-img';
                    preview.alt = 'Preview';
                    uploadArea.appendChild(preview);
                }
                preview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
});
</script>

<?php include('inc/footer.php'); ?>
