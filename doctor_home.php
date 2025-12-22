<?php
session_start();
include 'db.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

$doctor_id = $_SESSION['id'];
$message = "";

// 1. Logica pentru FINALIZARE CONSULT
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['complete_consult'])) {
    $app_id = $_POST['appointment_id'];
    $rec = trim($_POST['recommendations']);
    
    $stmt = $conn->prepare("UPDATE appointments SET recommendations = ?, status = 'Completed' WHERE id = ? AND doctor_id = ?");
    $stmt->bind_param("sii", $rec, $app_id, $doctor_id);
    if ($stmt->execute()) $message = "✅ Consult finalizat cu succes!";
}

// 2. Logica pentru ANULARE PROGRAMARE
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_appointment'])) {
    $app_id = $_POST['appointment_id'];
    
    $stmt = $conn->prepare("UPDATE appointments SET status = 'Cancelled' WHERE id = ? AND doctor_id = ?");
    $stmt->bind_param("ii", $app_id, $doctor_id);
    if ($stmt->execute()) $message = "⚠️ Programarea a fost anulată.";
}

// Luăm programările active
$upcoming = $conn->query("
    SELECT a.*, p.full_name as p_name 
    FROM appointments a 
    JOIN patients p ON a.patient_id = p.id 
    WHERE a.doctor_id = $doctor_id AND a.status = 'Scheduled'
    ORDER BY a.appointment_date ASC
");

// Luăm istoricul (Finalizate sau Anulate)
$history = $conn->query("
    SELECT a.*, p.full_name as p_name 
    FROM appointments a 
    JOIN patients p ON a.patient_id = p.id 
    WHERE a.doctor_id = $doctor_id AND a.status IN ('Completed', 'Cancelled')
    ORDER BY a.appointment_date DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Panou Medic</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h1>Cabinet Medical: Dr. <?= htmlspecialchars($_SESSION['name']) ?></h1>
    <div style="display: flex; gap: 15px; align-items: center;">
        <a href="doctor_schedule.php" style="background: #ffc107; color: #000; padding: 8px 15px; border-radius: 5px; text-decoration: none; font-weight: bold; font-size: 0.9em;">
            Setează Programul Meu
        </a>
        <a href="logout.php" style="color:white;">Deconectare</a>
    </div>
</header>

<div class="container">
    <?php if($message) echo "<p style='text-align:center; color: #d9534f; font-weight:bold;'>$message</p>"; ?>

    <div class="section">
        <h2 style="color:#004d66;"> Programări de Gestionat</h2>
        <?php while($row = $upcoming->fetch_assoc()): ?>
            <div style="background:#fff; border:1px solid #ddd; padding:15px; border-radius:8px; margin-bottom:20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <strong>Pacient: <?= htmlspecialchars($row['p_name']) ?></strong>
                    <span style="background:#007bff; color:white; padding:4px 8px; border-radius:4px; font-size:0.9em;">
                        <?= date("d.m.Y", strtotime($row['appointment_date'])) ?> | <?= substr($row['appointment_time'],0,5) ?>
                    </span>
                </div>
                
                <p style="margin: 10px 0;"><strong>Motiv:</strong> <?= htmlspecialchars($row['reason']) ?></p>
                    <div style="background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; border: 1px solid #ffeeba;">
                        <strong> Analize Pacient:</strong><br>
                        <?php
                        $docs_stmt = $conn->prepare("SELECT * FROM medical_documents WHERE appointment_id = ?");
                        $docs_stmt->bind_param("i", $row['id']); // $row['id'] fiind ID-ul programării
                        $docs_stmt->execute();
                        $docs_res = $docs_stmt->get_result();
                        
                        if($docs_res->num_rows == 0) {
                            echo "Niciun document încărcat.";
                        } else {
                            while($doc = $docs_res->fetch_assoc()) {
                                echo "<a href='{$doc['file_path']}' target='_blank' style='display:block; margin-bottom:5px; color:#856404;'>
                                        🔍 Deschide: {$doc['file_name']}
                                    </a>";
                            }
                        }
                        ?>
                    </div>
                <form method="POST" style="margin-top:10px;">
                    <input type="hidden" name="appointment_id" value="<?= $row['id'] ?>">
                    <textarea name="recommendations" placeholder="Adaugă recomandări pentru pacient..." style="width:100%; padding:10px; height:60px;" required></textarea>
                    
                    <div style="margin-top:10px; display:flex; gap:10px;">
                        <button type="submit" name="complete_consult" style="background:#28a745; color:white; border:none; padding:8px 15px; cursor:pointer; border-radius:4px;">
                             Finalizează
                        </button>
                        
                        <button type="submit" name="cancel_appointment" onclick="return confirm('Sigur anulezi această programare?')" style="background:#dc3545; color:white; border:none; padding:8px 15px; cursor:pointer; border-radius:4px;">
                             Anulează Programarea
                        </button>
                    </div>
                </form>
            </div>
        <?php endwhile; ?>
    </div>
    
    <div class="section">
        <h3> Istoric Activitate</h3>
        <table width="100%" style="border-collapse:collapse;">
            <tr style="background:#eee; text-align:left;">
                <th style="padding:10px;">Data</th>
                <th style="padding:10px;">Pacient</th>
                <th style="padding:10px;">Status</th>
                <th style="padding:10px;">Note</th>
            </tr>
            <?php while($row = $history->fetch_assoc()): ?>
            <tr style="border-bottom:1px solid #eee;">
                <td style="padding:10px;"><?= date("d.m.Y", strtotime($row['appointment_date'])) ?></td>
                <td style="padding:10px;"><?= htmlspecialchars($row['p_name']) ?></td>
                <td style="padding:10px;">
                    <span style="color: <?= $row['status'] == 'Completed' ? 'green' : 'red' ?>;">
                        <?= $row['status'] ?>
                    </span>
                </td>
                <td style="padding:10px; font-size:0.85em;"><?= htmlspecialchars($row['recommendations']) ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>
</body>
</html>