<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$name = $_SESSION['name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home - Patient Portal</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 40px; color: #333; text-align: center; }
        .card { background: #fff; padding: 30px; border-radius: 10px; width: 400px; margin: auto; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h2 { color: #007bff; }
        a { display: inline-block; margin-top: 15px; padding: 10px 20px; border-radius: 5px; text-decoration: none; background: #007bff; color: white; }
        a:hover { background: #0056b3; }
        .logout { background: #dc3545; margin-left: 10px; }
        .logout:hover { background: #b02a37; }
    </style>
</head>
<body>

<div class="card">
    <h2>Welcome, <?= htmlspecialchars($name) ?> 👋</h2>
    <p>You are logged in to the hospital appointment system.</p>

    <a href="appointment.php">📅 View / Book Appointments</a>
    <a href="logout.php" class="logout">🚪 Log Out</a>
</div>

</body>
</html>
