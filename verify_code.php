<?php
session_start();
include 'db.php';

// Verificăm dacă utilizatorul a trecut prin login.php
if (!isset($_SESSION['temp_user_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_code = trim($_POST['code']);
    $user_id = $_SESSION['temp_user_id'];

    // Preluăm codul salvat în DB
    $stmt = $conn->prepare("SELECT id, full_name, verification_code FROM patients WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $pat = $stmt->get_result()->fetch_assoc();

    if ($pat && $pat['verification_code'] === $input_code) {
        // Cod corect: Finalizăm logarea
        $_SESSION['id'] = $pat['id'];
        $_SESSION['name'] = $pat['full_name'];
        $_SESSION['role'] = 'patient';
        
        // Ștergem codul din DB pentru securitate
        $conn->query("UPDATE patients SET verification_code = NULL WHERE id = $user_id");
        
        unset($_SESSION['temp_user_id']);
        header("Location: home.php");
        exit();
    } else {
        $error = "Codul introdus este incorect!";
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Verificare Cod</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container" style="max-width: 400px; margin: 100px auto; text-align: center;">
        <form method="POST" class="section">
            <h2>Verificare Email</h2>
            <p>Am trimis un cod de 6 cifre pe adresa ta.</p>
            <?php if($error): ?> <p style="color:red;"><?= $error ?></p> <?php endif; ?>
            
            <input type="text" name="code" placeholder="Cod 6 cifre" required 
                   style="width:80%; padding:10px; font-size: 1.5em; text-align: center; letter-spacing: 5px;">
            
            <button type="submit" style="margin-top: 20px; padding: 10px 20px;">Verifică Codul</button>
        </form>
    </div>
</body>
</html>