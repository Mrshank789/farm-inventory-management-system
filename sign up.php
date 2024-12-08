<?php
// Initialize variables for form and error messages
$message = '';
$error = '';

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database connection
    include('db.php');

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Collect form data
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // Check if username is unique
    $checkUser = "SELECT * FROM users WHERE username='$user'";
    $result = $conn->query($checkUser);

    if ($result->num_rows > 0) {
        $error = "Username already exists!";
    } else {
        // Insert the new user into the database
        $sql = "INSERT INTO users (username, password) VALUES ('$user', '$pass')"; 
        if ($conn->query($sql) === TRUE) {
            $message = "New record created successfully. Please <a href='log in.php'>login here</a>.";
        } else {
            $error = "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup Page</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="login-container">
        <h2>Signup</h2>

        <?php
        if (!empty($error)) {
            echo "<p style='color: red;'>$error</p>";
        }

        if (!empty($message)) {
            echo "<p style='color: green;'>$message</p>";
        }
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <input type="text" id="username" name="username" placeholder="Username" required>
            <input type="password" id="password" name="password" placeholder="Password" required>
            <input type="submit" value="Sign Up">
        </form>
    </div>
</body>
</html>
