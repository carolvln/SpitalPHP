<?php
session_start();
include 'db.php';
if (!isset($_SESSION['id'])) { header("Location: login.php"); exit(); }

if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) die("CSRF Failed");

    $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason, medical_history, status) VALUES (?, ?, ?, ?, ?, ?, 'Scheduled')");
    $stmt->bind_param("iissss", $_SESSION['id'], $_POST['doctor_id'], $_POST['date'], $_POST['time'], $_POST['reason'], $_POST['history']);
    
    if($stmt->execute()) $msg = "✅ Programare trimisă cu succes!";
    else $msg = "❌ Eroare la trimitere.";
}

$docs = $conn->query("SELECT id, full_name, specialization FROM doctors WHERE status='Approved'");
?>
<!DOCTYPE html>
<html>
<head><title>Programare Nouă</title><link rel="stylesheet" href="style.css"></head>
<body>
<header><h1>Solicită o Consultație</h1><a href="home.php" style="color:white">Înapoi</a></header>
<div class="container" style="max-width:800px;">
    <form method="POST" class="section">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <?php if($msg) echo "<p>$msg</p>"; ?>
        
        <label>Medic:</label>
        <select name="doctor_id" required style="width:100%; padding:10px; margin-bottom:15px;">
            <?php while($d = $docs->fetch_assoc()) echo "<option value='{$d['id']}'>Dr. {$d['full_name']} ({$d['specialization']})</option>"; ?>
        </select>

        <label>Data și Ora:</label>
        <input type="date" name="date" required style="width:45%; padding:10px;">
        <input type="time" name="time" required style="width:35%; padding:10px; float:right;">
        
        <label style="display:block; margin-top:15px;">Motiv:</label>
        <textarea name="reason" style="width:100%; padding:10px;"></textarea>

        <label>Istoric Medical (Alergii/Boli):</label>
        <textarea name="history" required style="width:100%; padding:10px; height:80px;"></textarea>

        <button type="submit" style="width:100%; margin-top:15px; padding:12px; background:#0099cc; color:white; border:none; cursor:pointer;">Trimite Cererea</button>
    </form>
</div>
</body>
</html>