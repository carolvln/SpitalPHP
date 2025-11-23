<?php
session_start();
include 'db.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM patients WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['id'] = $user['id'];
            $_SESSION['name'] = $user['full_name'];
            header("Location: home.php");
            exit();
        } else {
            $message = "❌ Invalid password!";
        }
    } else {
        $message = "❌ No account found with that email.";
    }

    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Login</title>
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

<form action="login.php" method="POST">
    <h2>Patient Login</h2>
    <?php if ($message): ?><div class="msg"><?= $message ?></div><?php endif; ?>
    <input type="email" name="email" placeholder="Email Address" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
    <a href="signup.php">Create a new account</a>
</form>

</body>
</html>
