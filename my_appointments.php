<?php
session_start();
include 'db.php';
if (!isset($_SESSION['id'])) { header("Location: login.php"); exit(); }

$stmt = $conn->prepare("
    SELECT a.*, d.full_name as d_name, d.specialization 
    FROM appointments a 
    JOIN doctors d ON a.doctor_id = d.id 
    WHERE a.patient_id = ? 
    ORDER BY a.appointment_date DESC
");
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head><title>Programările Mele</title><link rel="stylesheet" href="style.css"></head>
<body>
<header><h1>Istoric Medical & Programări</h1><a href="home.php" style="color:white">Înapoi</a></header>
<div class="container">
    <?php if($res->num_rows == 0): ?>
        <div class="section"><p>Nu ai nicio programare înregistrată.</p></div>
    <?php endif; ?>

    <?php while($row = $res->fetch_assoc()): ?>
    <div class="section" style="border-left-color: <?= $row['status'] == 'Cancelled' ? '#ff4d4d' : '#28a745' ?>;">
        <div style="float:right; background:#eee; padding:5px 10px; border-radius:4px;">
            <strong>Status: <?= $row['status'] ?></strong>
        </div>
        <h4>Data: <?= date("d.m.Y", strtotime($row['appointment_date'])) ?> | Ora: <?= substr($row['appointment_time'],0,5) ?></h4>
        <p><strong>Medic:</strong> Dr. <?= htmlspecialchars($row['d_name']) ?> (<?= htmlspecialchars($row['specialization']) ?>)</p>
        <p><strong>Motivul tău:</strong> <?= htmlspecialchars($row['reason']) ?></p>
        
        <hr>
        <div style="background:#f0f9ff; padding:15px; border-radius:5px; border:1px solid #bde0fe;">
            <h5 style="margin-top:0; color:#004d66;">Recomandările Medicului:</h5>
            <p style="font-style: italic; color:#333;">
                <?= $row['recommendations'] ? nl2br(htmlspecialchars($row['recommendations'])) : "Așteptare consult/analize..." ?>
            </p>
        </div>
    </div>
    <?php endwhile; ?>
</div>
</body>
</html>