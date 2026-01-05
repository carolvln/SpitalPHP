<?php
session_start();
include 'db.php';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_captcha = strtoupper($_POST['captcha_input'] ?? '');
    $correct_captcha = $_SESSION['captcha_code'] ?? '';

    if (empty($user_captcha) || $user_captcha !== $correct_captcha) {
        $message = "Codul de securitate este incorect!";
    } else {
        $name = strip_tags(trim($_POST['name']));
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $hashed = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $spec = strip_tags($_POST['specialization']);

        $check = $conn->prepare("SELECT email FROM doctors WHERE email = ? UNION SELECT email FROM patients WHERE email = ?");
        $check->bind_param("ss", $email, $email);
        $check->execute();
        
        if ($check->get_result()->num_rows > 0) {
            $message = "Email deja folosit în sistem!";
        } else {
            $stmt = $conn->prepare("INSERT INTO doctors (full_name, email, password, specialization, status) VALUES (?, ?, ?, ?, 'Pending')");
            $stmt->bind_param("ssss", $name, $email, $hashed, $spec);
            if ($stmt->execute()) {
                $message = "Cont creat! Așteaptă aprobarea unui administrator.";
            } else {
                $message = "Eroare la salvarea datelor.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Înregistrare Medic</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container" style="max-width:400px; margin-top: 30px;">
    <form method="POST" class="section">
        <h2 style="text-align:center;">Înregistrare Medic</h2>
        <p style="color:<?= strpos($message, '✅') !== false ? 'green' : 'red' ?>; text-align:center; font-weight:bold;"><?= $message ?></p>
        
        <label>Nume Complet:</label>
        <input type="text" name="name" required style="width:100%; margin-bottom:10px; padding:10px; border:1px solid #ccc; border-radius:4px;">
        
        <label>Email:</label>
        <input type="email" name="email" required style="width:100%; margin-bottom:10px; padding:10px; border:1px solid #ccc; border-radius:4px;">
        
        <label>Parolă:</label>
        <input type="password" name="password" required style="width:100%; margin-bottom:10px; padding:10px; border:1px solid #ccc; border-radius:4px;">
        
        <label>Specializare:</label>
        <input type="text" name="specialization" required style="width:100%; margin-bottom:15px; padding:10px; border:1px solid #ccc; border-radius:4px;">

        <div style="background: #f9f9f9; padding: 15px; border-radius: 4px; margin-bottom: 15px; text-align: center; border: 1px solid #ddd;">
            <label style="display: block; margin-bottom: 8px; font-weight: bold;">Verificare Securitate:</label>
            <img src="captcha.php?r=<?= time(); ?>" id="captcha_img" style="border: 1px solid #004d66; cursor: pointer; border-radius: 4px;" title="Click pentru alt cod">
            <input type="text" name="captcha_input" placeholder="Introdu codul de mai sus" required 
                   style="width:100%; padding:10px; margin-top:10px; text-align: center; text-transform: uppercase; font-weight: bold;">
        </div>

        <button type="submit" style="width:100%; padding:12px; background:#004d66; color:white; border:none; border-radius:4px; cursor:pointer; font-weight:bold;">
            Trimite spre Aprobare
        </button>
        <p style="text-align:center; margin-top:15px;"><a href="login.php">Înapoi la Login</a></p>
    </form>
</div>

<script>
    document.getElementById('captcha_img').onclick = function() {
        this.src = 'captcha.php?r=' + Math.random();
    };
</script>
</body>
</html>