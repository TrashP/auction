<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Chat-GPT has been used to debug the code and suggest minor improvements for the code
include_once("db_connection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST['accountType'];
    // remove trailing whitespace with trim()
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $dateOfBirth = trim($_POST['dateOfBirth']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $passwordConfirmation = $_POST['passwordConfirmation'];

    // check if passwords match
    if ($password !== $passwordConfirmation) {
        die("Passwords do not match. <a href='register.php'>Go back</a> and try again.");
    }

    if (empty($firstName) || empty($lastName) || empty($dateOfBirth) || empty($email) || empty($password) || empty($passwordConfirmation)) {
        die("Please fill in all fields. <a href='register.php'>Go back</a> and try again.");
    }

    // validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email address. <a href='register.php'>Go back</a> and try again.");
    }

    // query database to see if email already exists
    $emailCheckStmt = $conn->prepare("SELECT userID FROM Users WHERE email = ?");
    $emailCheckStmt->bind_param("s", $email);
    $emailCheckStmt->execute();
    $emailCheckStmt->store_result();
    if ($emailCheckStmt->num_rows > 0) {
        die("Email is already registered. <a href='register.php'>Go back</a> and try again.");
    }

    // ensure date not in the future
    $currentDate = date("Y/m/d");
    if ($dateOfBirth > $currentDate) {
        die("Date of birth cannot be in the future. <a href='register.php'>Go back</a> and try again.");
    }

    // hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    // prep query
    $stmt = $conn->prepare("INSERT INTO Users (role, firstName, lastName, dateOfBirth, email, password) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $role, $firstName, $lastName, $dateOfBirth, $email, $hashedPassword);

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