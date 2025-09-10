<?php
// No need to include config.php since header.php already includes the database connection

$query = "SELECT name, amount FROM popup_withdrawals ORDER BY RAND() LIMIT 1";
$result = mysqli_query($con, $query);

if ($row = mysqli_fetch_assoc($result)) {
    header('Content-Type: application/json');
    echo json_encode([
        'name' => $row['name'],
        'amount' => number_format($row['amount'], 2)
    ]);
} else {
    header('Content-Type: application/json');
    echo json_encode([]);
}

mysqli_close($con);
?>
