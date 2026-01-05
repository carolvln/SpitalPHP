<?php
session_start();
include 'db.php';
require_once('class.phpmailer.php');
require_once('class.smtp.php');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_captcha = strtoupper($_POST['captcha_input'] ?? '');
    $correct_captcha = $_SESSION['captcha_code'] ?? '';

    if (empty($user_captcha) || $user_captcha !== $correct_captcha) {
        $message = "Codul de securitate este incorect!";
    } 
    elseif (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "Eroare de sesiune.";
    }
    else {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        // 1. LOGIN ADMIN
        $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($admin = $res->fetch_assoc()) {
            if (password_verify($password, $admin['password'])) {
                $_SESSION['id'] = $admin['id'];
                $_SESSION['role'] = 'admin';
                header("Location: admin_board.php"); exit();
            }
        }

        // 2. LOGIN DOCTOR
        $stmt = $conn->prepare("SELECT id, full_name, password, status FROM doctors WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($doc = $res->fetch_assoc()) {
            if (password_verify($password, $doc['password'])) {
                if ($doc['status'] !== 'Approved') {
                    $message = "Contul tău încă nu a fost aprobat de admin.";
                } else {
                    $_SESSION['id'] = $doc['id'];
                    $_SESSION['role'] = 'doctor';
                    $_SESSION['name'] = $doc['full_name'];
                    header("Location: doctor_home.php"); exit();
                }
            }
        }

        // 3. LOGIN PACIENT
        $stmt = $conn->prepare("SELECT id, full_name, password, email FROM patients WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($pat = $res->fetch_assoc()) {
            if (password_verify($password, $pat['password'])) {
                
                $code = (string)rand(100000, 999999);
                
                $stmt_update = $conn->prepare("UPDATE patients SET verification_code = ? WHERE id = ?");
                $stmt_update->bind_param("si", $code, $pat['id']);
                
                if($stmt_update->execute()) {
                    $mail = new PHPMailer();
                    $mail->IsSMTP();
                    $mail->SMTPAuth = true;
                    $mail->SMTPSecure = "tls";
                    $mail->Host = "mail.ptanasa.daw.ssmr.ro";
                    $mail->Port = 587;
                    $mail->Username = "account@ptanasa.daw.ssmr.ro";
                    $mail->Password = "123456";

                    $mail->From = "account@ptanasa.daw.ssmr.ro";
                    $mail->FromName = "Spital PHP";
                    $mail->AddAddress($pat['email']);
                    $mail->Subject = "Cod de verificare - Spital PHP";
                    $mail->Body    = "Buna ziua " . $pat['full_name'] . ", codul tau de verificare este: " . $code;
                    $mail->CharSet = 'UTF-8';

                    if(!$mail->Send()) {
                        $_SESSION['debug_code'] = $code;
                    }

                    $_SESSION['temp_user_id'] = $pat['id'];
                    $_SESSION['temp_user_name'] = $pat['full_name'];
                    
                    header("Location: verify_code.php");
                    exit();
                } else {
                    $message = "Eroare interna la generarea codului.";
                }
            }
        }
        
        if (!$message) $message = "Date incorecte sau parola gresita.";
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Login - Spital</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container" style="max-width: 400px; margin: 50px auto;">
        <form action="login.php" method="POST" class="section">
            <h2 style="text-align:center;">Autentificare</h2>
            
            <?php if ($message): ?>
                <p style="color: red; text-align: center; font-weight: bold;"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>

            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            
            <label>Email:</label>
            <input type="text" name="email" required style="width:100%; padding:10px; margin-bottom:15px; border: 1px solid #ccc; border-radius: 4px;">
            
            <label>Parolă:</label>
            <input type="password" name="password" required style="width:100%; padding:10px; margin-bottom:15px; border: 1px solid #ccc; border-radius: 4px;">
            
            <div style="background: #f4f4f4; padding: 15px; border-radius: 4px; margin-bottom: 15px; text-align: center; border: 1px solid #ddd;">
                <label style="display: block; margin-bottom: 8px; font-weight: bold;">Cod de securitate:</label>
                <img src="captcha.php?r=<?php echo time(); ?>" id="captcha_img" style="border: 1px solid #004d66; border-radius: 4px; cursor: pointer;" title="Click pentru cod nou">
                <p style="font-size: 0.8em; color: #555; margin: 5px 0;">(Click pe imagine pentru refresh)</p>
                <input type="text" name="captcha_input" placeholder="Introdu codul de sus" required 
                       style="width:100%; padding:10px; text-align: center; font-weight: bold; text-transform: uppercase;">
            </div>

            <button type="submit" style="width:100%; padding:12px; background-color: #004d66; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 1em; font-weight: bold;">
                Log In
            </button>
            
            <div style="text-align:center; margin-top:20px; font-size: 0.9em;">
                <p><a href="signup.php">Creează cont pacient</a></p>
                <p><a href="doctor_singup.php">Ești medic? Înregistrează-te aici</a></p>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('captcha_img').onclick = function() {
            this.src = 'captcha.php?r=' + Math.random();
        };
    </script>
</body>
</html>