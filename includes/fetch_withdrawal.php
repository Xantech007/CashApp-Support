<?php
require_once '../config/dbcon.php'; // Include database connection

header('Content-Type: application/json');

$response = ['success' => false, 'error' => ''];

if (!isset($con) || !$con) {
    $response['error'] = 'Database connection not available';
    echo json_encode($response);
    exit;
}

$query = "SELECT name, amount FROM popup_withdrawals ORDER BY RAND() LIMIT 1";
$result = mysqli_query($con, $query);

if (!$result) {
    $response['error'] = 'Query failed: ' . mysqli_error($con);
    echo json_encode($response);
    mysqli_close($con);
    exit;
}

if ($row = mysqli_fetch_assoc($result)) {
    $response = [
        'success' => true,
        'name' => $row['name'],
        'amount' => number_format($row['amount'], 2)
    ];
} else {
    $response['error'] = 'No records found in popup_withdrawals';
}

echo json_encode($response);
mysqli_close($con);
?>
