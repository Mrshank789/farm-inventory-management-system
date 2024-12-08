<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// the database connection
include('db.php');

// Check for connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables for form and error messages
$message = '';
$error = '';

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $user = $_POST['username'];
        $pass = $_POST['password'];

        // Check if the user exists
        $sql = "SELECT * FROM users WHERE username='$user'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // Fetch the user data
            $row = $result->fetch_assoc();
            
            // Verify the password 
            if ($pass === $row['password']) {
                echo "Login successful!";
                // Redirect to the dashboard or another page
                header('Location: dashboard.php');
                exit(); // Stop further script execution
            } else {
                $error = "Invalid password!";
            }
        } else {
            $error = "No user found!";
        }
    } else {
        $error = "Please enter both username and password!";
    }
} 

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>

        <?php
        if (!empty($error)) {
            echo "<p style='color: red;'>$error</p>";
        }
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <input type="text" id="username" name="username" placeholder="Username" required>
            <input type="password" id="password" name="password" placeholder="Password" required>
            <input type="submit" value="Login">
        </form>
        <p>Don't have an account? <a href="sign up.php">Sign Up</a></p>
    </div>
</body>
</html>
