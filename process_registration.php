<?php
// Chat-GPT has been used to debug the code and suggest minor improvements for the code
include_once("db_connection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST['accountType'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $passwordConfirmation = $_POST['passwordConfirmation'];

    // check if passwords match
    if ($password !== $passwordConfirmation) {
        die("Passwords do not match. <a href='register.php'>Go back</a> and try again.");
    }

    // hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // prep query
    $stmt = $conn->prepare("INSERT INTO users (role , email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $role, $email, $hashedPassword);

    // execute
    if ($stmt->execute()) {
        echo "Registration successful! <a href='browse.php'>Return to browse page to login</a>";
    } else {
        echo "Error: " . $stmt->error;
    }

    // close statement and connection
    $stmt->close();
    $conn->close();
} else {
    header("Location: register.php");
    exit();
}
?>
