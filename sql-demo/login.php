<?php
// Start a session to manage user login state
session_start();

// Hardcoded database credentials (Another bad practice for simplicity)
$host = 'localhost';
$user = 'root';
$pass = ''; // Your MySQL password
$dbname = 'vulnerable_db';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user input WITHOUT any sanitization (VULNERABLE)
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Connect to the database
    $conn = new mysqli($host, $user, $pass, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // 🚨 CRITICAL VULNERABILITY: SQL Injection 🚨
    // We are directly inserting user input into the query string.
    $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    // Check if a matching user was found
    if ($result->num_rows > 0) {
        // Login successful
        session_regenerate_id(true);
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        // Redirect to protected page
        header("Location: profile.php");
        exit;
        $success_msg = "Login successful! Welcome, " . htmlspecialchars($username) . ".";
    } else {
        // Login failed
        $error_msg = "Invalid username or password!";
    }
    // Close the connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vulnerable Login</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 400px; margin: 50px auto; }
        .container { padding: 20px; border: 1px solid #ccc; border-radius: 5px; }
        input[type=text], input[type=password] { width: 100%; padding: 10px; margin: 8px 0; display: inline-block; border: 1px solid #ccc; box-sizing: border-box; }
        button { background-color: #4CAF50; color: white; padding: 14px 20px; margin: 8px 0; border: none; cursor: pointer; width: 100%; }
        .msg { padding: 10px; margin-bottom: 15px; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container">
        
        <h2>Insecure Login Form</h2>
        <p>This form is designed for educational purposes and contains severe security vulnerabilities.</p>

        <?php
        // Display success or error messages
        if (isset($success_msg)) {
            echo '<div class="msg success">' . $success_msg . '</div>';
        }
        if (isset($error_msg)) {
            echo '<div class="msg error">' . $error_msg . '</div>';
        }
        ?>

        <form method="post" action="">
            <label for="username"><b>Username</b></label>
            <input type="text" placeholder="Enter Username" name="username" required>

            <label for="password"><b>Password</b></label>
            <input type="password" placeholder="Enter Password" name="password" required>

            <button type="submit">Login</button>
        </form>
    </div>

    <!-- Section to demonstrate the vulnerability -->
    <div style="margin-top: 30px; padding: 20px; background-color: #f0f0f0; border-radius: 5px;">
        <h3>💀 Hacking Demo (SQL Injection Payloads)</h3>
        <p>Try these in the username field (leave password blank):</p>
        <ul>
            <li><code>' OR '1'='1' -- </code> (Classic bypass. Note the space after --)</li>
            <li><code>' UNION SELECT 1, 'hackeduser', 'hackedpass' -- </code></li>
        </ul>
        <p><strong>Why it works:</strong> The query becomes <code>SELECT * FROM users WHERE username = '' OR '1'='1' -- ' AND password = ''</code>. The <code>--</code> comments out the rest of the query, making the password check irrelevant, and <code>OR '1'='1'</code> is always true.</p>
    </div>
</body>
</html>