<?php
session_start();
include 'db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $pass = $_POST['password'];
    $captcha_input = $_POST['captcha_input'];

    if ($captcha_input !== $_SESSION['captcha_code']) {
        $message = "❌ Codul de securitate este incorect!";
    } 
    else {
        $checkEmail = $conn->prepare("SELECT email FROM patients WHERE email = ? UNION SELECT email FROM doctors WHERE email = ?");
        $checkEmail->bind_param("ss", $email, $email);
        $checkEmail->execute();
        $result = $checkEmail->get_result();

        if ($result->num_rows > 0) {
            $message = "❌ Acest email este deja utilizat de alt cont!";
        } else {
            $hashed_pass = password_hash($pass, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO patients (full_name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashed_pass);

            if ($stmt->execute()) {
                $message = "✅ Cont creat cu succes! Te poți loga.";
            } else {
                $message = "❌ Eroare la baza de date.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Înregistrare Pacient</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <form method="POST" class="section" style="max-width: 400px; margin: auto;">
        <h2>Creare Cont Pacient</h2>
        <?php if($message) echo "<p style='color:red;'>$message</p>"; ?>

        <input type="text" name="full_name" placeholder="Nume Complet" required style="width:100%; padding:10px; margin-bottom:10px;">
        <input type="email" name="email" placeholder="Email" required style="width:100%; padding:10px; margin-bottom:10px;">
        <input type="password" name="password" placeholder="Parolă" required style="width:100%; padding:10px; margin-bottom:15px;">

        <div style="margin-bottom: 15px; background: #eee; padding: 10px; border-radius: 5px;">
            <label>Cod de securitate:</label><br>
            <img src="captcha.php" style="margin: 5px 0; border: 1px solid #ccc;">
            <input type="text" name="captcha_input" placeholder="Introdu codul de sus" required style="width:100%; padding:8px;">
        </div>

        <button type="submit" style="width:100%; padding:10px; background:#004d66; color:white; border:none; cursor:pointer;">Înregistrare</button>
        <p><a href="login.php">Ai deja cont? Loghează-te</a></p>
    </form>
</div>
</body>
</html>