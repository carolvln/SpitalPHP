<?php
session_start();
include 'db.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'patient') {
    header("Location: login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book'])) {
    
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Eroare: Validare CSRF eșuată. Încearcă să dai refresh la pagină.");
    }

    $patient_id = $_SESSION['id'];
    $doctor_id = $_POST['doctor_id'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $reason = trim($_POST['reason']);
    $history = trim($_POST['medical_history']);

    if ($date < date("Y-m-d")) {
        $message = "Nu poți face programări în trecut.";
    } else {
        $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason, medical_history, status) VALUES (?, ?, ?, ?, ?, ?, 'Scheduled')");
        $stmt->bind_param("iissss", $patient_id, $doctor_id, $date, $time, $reason, $history);
        
        if ($stmt->execute()) {
            $message = "Programare creată cu succes!";
        } else {
            $message = "Eroare la salvare: " . $conn->error;
        }
    }
}

$doctors = $conn->query("SELECT id, full_name, specialization FROM doctors WHERE status='Approved'");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Programare Nouă</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="section">
        <h2>Solicită o Consultație</h2>
        <?php if($message) echo "<p>$message</p>"; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <label>Medic:</label>
            <select name="doctor_id" required style="width:100%; padding:10px; margin-bottom:15px;">
                <?php while($d = $doctors->fetch_assoc()): ?>
                    <option value="<?= $d['id'] ?>">Dr. <?= $d['full_name'] ?> (<?= $d['specialization'] ?>)</option>
                <?php endwhile; ?>
            </select>

            <label>Data și Ora:</label>
            <div style="display:flex; gap:10px; margin-bottom:15px;">
                <input type="date" name="date" required style="flex:1; padding:10px;">
                <input type="time" name="time" required style="flex:1; padding:10px;">
            </div>

            <label>Motivul vizitei:</label>
            <textarea name="reason" placeholder="Descrie simptomele..." style="width:100%; padding:10px; margin-bottom:15px;"></textarea>

            <label><b>Istoric Medical (Important pentru medic):</b></label>
            <textarea name="medical_history" placeholder="Alergii, boli cronice, medicamente actuale..." required style="width:100%; height:100px; padding:10px; border: 2px solid #004d66;"></textarea>

            <button type="submit" name="book" style="width:100%; padding:12px; background:#0099cc; color:white; border:none; border-radius:5px; cursor:pointer;">
                Confirmă Programarea
            </button>
        </form>
        <p><a href="home.php">Înapoi la Panoul Principal</a></p>
    </div>
</div>
</body>
</html>