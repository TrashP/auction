<?php
// Chat-GPT has been used to debug the code and suggest minor improvements for the code
include_once("db_connection.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        die("Email and password are required. <a href='browse.php'>Go back</a>");
    }

    // p a query to find the user based on email
    $stmt = $conn->prepare("SELECT userID, role, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // berify the password against the hashed password in the database
        if (password_verify($password, $user['password'])) {
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $email;
            $_SESSION['account_type'] = $user['role'];
            echo "<div class='text-center'>You are now logged in! Redirecting...</div>";
            header("refresh:2;url=index.php");
            exit();
        } else {
            echo "Invalid password. <a href='browse.php'>Go back</a>";
        }
    } else {
        echo "No account found with that email. <a href='register.php'>Register</a>";
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: browse.php");
    exit();
}
?>
