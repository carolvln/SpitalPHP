<?php
session_start();
include 'db.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

$doctor_id = $_SESSION['id'];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['complete_consult'])) {
    $app_id = $_POST['appointment_id'];
    $rec = trim($_POST['recommendations']);
    
    $stmt = $conn->prepare("UPDATE appointments SET recommendations = ?, status = 'Completed' WHERE id = ? AND doctor_id = ?");
    $stmt->bind_param("sii", $rec, $app_id, $doctor_id);
    if ($stmt->execute()) $message = "Consult finalizat cu succes!";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_appointment'])) {
    $app_id = $_POST['appointment_id'];
    
    $stmt = $conn->prepare("UPDATE appointments SET status = 'Cancelled' WHERE id = ? AND doctor_id = ?");
    $stmt->bind_param("ii", $app_id, $doctor_id);
    if ($stmt->execute()) $message = "Programarea a fost anulată.";
}

$upcoming = $conn->query("
    SELECT a.*, p.full_name 
    FROM appointments a 
    INNER JOIN patients p ON a.patient_id = p.id 
    WHERE a.doctor_id = $doctor_id AND a.status = 'Scheduled'
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
");

$history = $conn->query("
    SELECT a.*, p.full_name 
    FROM appointments a 
    LEFT JOIN patients p ON a.patient_id = p.id 
    WHERE a.doctor_id = $doctor_id AND a.status IN ('Completed', 'Cancelled')
    ORDER BY a.appointment_date DESC
");
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Panou Medic - Spital Gemini</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h1>Dr. <?= htmlspecialchars($_SESSION['name'] ?? 'Medic') ?></h1>
    <div style="display: flex; gap: 15px; align-items: center;">
        <a href="doctor_schedule.php" style="background: #ffc107; color: #000; padding: 8px 15px; border-radius: 5px; text-decoration: none; font-weight: bold; font-size: 0.9em;">
            Setează Programul Meu
        </a>
        <a href="logout.php" style="color:white; text-decoration: none; font-weight: bold;">Deconectare</a>
    </div>
</header>

<div class="container" style="max-width: 900px; margin: 20px auto; padding: 20px;">
    <?php if($message) echo "<p style='text-align:center; background: #d4edda; color: #155724; padding: 10px; border-radius: 5px;'>$message</p>"; ?>

    <div class="section">
        <h2 style="color:#004d66; border-bottom: 2px solid #004d66; padding-bottom: 10px;">🩺 Programări Viitoare</h2>
        
        <?php if($upcoming->num_rows == 0): ?>
            <p style="text-align:center; color: #666;">Nu ai nicio programare activă în acest moment.</p>
        <?php endif; ?>

        <?php while($row = $upcoming->fetch_assoc()): ?>
            <div style="background:#fff; border:1px solid #ddd; padding:20px; border-radius:8px; margin-bottom:20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 15px;">
                    <span style="font-size: 1.2em; font-weight: bold; color: #333;">
                        Pacient: <?= htmlspecialchars($row['full_name']) ?>
                    </span>
                    <span style="background:#007bff; color:white; padding:5px 12px; border-radius:20px; font-size:0.9em; font-weight: bold;">
                        📅 <?= date("d.m.Y", strtotime($row['appointment_date'])) ?> | 🕒 <?= substr($row['appointment_time'],0,5) ?>
                    </span>
                </div>
                
                <p style="margin: 10px 0; color: #555;"><strong>Motivul vizitei:</strong> <?= htmlspecialchars($row['reason']) ?></p>
                
                <form method="POST" style="margin-top:15px; border-top: 1px solid #eee; pad: 15px;">
                    <input type="hidden" name="appointment_id" value="<?= $row['id'] ?>">
                    
                    <label style="display:block; margin-bottom: 5px; font-weight: bold;">Recomandări Medicale:</label>
                    <textarea name="recommendations" placeholder="Scrie aici diagnosticul sau tratamentul recomandat..." 
                              style="width:100%; padding:10px; height:80px; border: 1px solid #ccc; border-radius: 4px; font-family: inherit;" required></textarea>
                    
                    <div style="margin-top:15px; display:flex; gap:10px;">
                        <button type="submit" name="complete_consult" style="background:#28a745; color:white; border:none; padding:10px 20px; cursor:pointer; border-radius:4px; font-weight: bold;">
                              Finalizează Consultul
                        </button>
                        
                        <button type="submit" name="cancel_appointment" onclick="return confirm('Sigur dorești să anulezi programarea?')" 
                                style="background:#dc3545; color:white; border:none; padding:10px 20px; cursor:pointer; border-radius:4px; font-weight: bold;">
                               Anulează
                        </button>
                    </div>
                </form>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>