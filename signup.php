<?php
session_start();
include 'db.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($name) || empty($email) || empty($password)) {
        $message = "❌ All fields are required!";
    } else {
        $check = $conn->prepare("SELECT id FROM patients WHERE email=?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "⚠️ Email already registered!";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO patients (full_name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashedPassword);
            if ($stmt->execute()) {
                $message = "✅ Account created successfully! You can now log in.";
            } else {
                $message = "❌ Database error: " . $stmt->error;
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Sign Up</title>
    <style>
        body { font-family: Arial; background: #f4f6f9; margin: 40px; color: #333; }
        form { background: white; padding: 25px; border-radius: 10px; width: 350px; margin: auto; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        input, button { width: 100%; padding: 10px; margin-top: 10px; border-radius: 5px; border: 1px solid #ccc; }
        button { background: #007bff; color: white; border: none; cursor: pointer; }
        button:hover { background: #0056b3; }
        .msg { margin-bottom: 10px; padding: 10px; border-radius: 5px; background: #e9ecef; }
        a { display: block; text-align: center; margin-top: 15px; text-decoration: none; color: #007bff; }
    </style>
</head>
<body>

<form action="signup.php" method="POST">
    <h2>Create Account</h2>
    <?php if ($message): ?><div class="msg"><?= $message ?></div><?php endif; ?>
    <input type="text" name="name" placeholder="Full Name" required>
    <input type="email" name="email" placeholder="Email Address" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Sign Up</button>
    <a href="login.php">Already have an account? Log in</a>
</form>

</body>
</html>
