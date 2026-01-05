<?php
session_start();
include 'db.php';
require_once('class.phpmailer.php');
require_once('class.smtp.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO patients (full_name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $full_name, $email, $password);

    if ($stmt->execute()) {
        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = "tls";
        $mail->Host = "mail.ptanasa.daw.ssmr.ro";
        $mail->Port = 587;
        $mail->Username = "account@ptanasa.daw.ssmr.ro";
        $mail->Password = "123456";

        $mail->From = "account@ptanasa.daw.ssmr.ro";
        $mail->FromName = "Spital Gemini";
        $mail->AddAddress($email);
        
        $mail->Subject = "Bun venit la Spital Gemini!";
        $mail->Body    = "Buna ziua " . $full_name . ",\n\nContul tau a fost creat cu succes! Te poti autentifica acum folosind adresa de email.";
        $mail->CharSet = 'UTF-8';

        $mail->Send();

        header("Location: login.php?success=1");
        exit();
    } else {
        $error = "Eroare la inregistrare.";
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