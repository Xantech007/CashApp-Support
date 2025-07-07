<?php
session_start();
include('../config/dbcon.php');

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = mysqli_real_escape_string($con, $_POST['password']);

    // Validate inputs
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Email and password are required.";
        header("Location: ../signin");
        exit(0);
    }

    // Check if email exists
    $user_query = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
    $user_query_run = mysqli_query($con, $user_query);

    if (mysqli_num_rows($user_query_run) > 0) {
        $data = mysqli_fetch_assoc($user_query_run);
        
        // Verify password
        if (password_verify($password, $data['password'])) {
            // Password is correct, set session variables
            $user_id = $data['id'];
            $user_name = $data['name'];
            $balance = $data['balance'];
            $email = $data['email'];
            $image = $data['image'];
            $address = $data['address'];
            $btc_wallet = $data['btc_wallet'];
            $ref = $data['ref'];

            $_SESSION['auth'] = true;
            $_SESSION['email'] = $email;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['name'] = $user_name;
            $_SESSION['balance'] = $balance;
            $_SESSION['address'] = $address;
            $_SESSION['btc_wallet'] = $btc_wallet;
            $_SESSION['image'] = $image;
            $_SESSION['ref'] = $ref;

            header("Location: ../users/index");
            exit(0);
        } else {
            $_SESSION['error'] = "Invalid Credentials!";
            header("Location: ../signin");
            exit(0);
        }
    } else {
        $_SESSION['error'] = "Invalid Credentials!";
        header("Location: ../signin");
        exit(0);
    }
} else {
    $_SESSION['error'] = "Invalid form submission.";
    header("Location: ../signin");
    exit(0);
}
?>
