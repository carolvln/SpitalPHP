<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'patient') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Portal Pacient</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h1>Bun venit, <?= htmlspecialchars($_SESSION['name']) ?></h1>
    <nav><a href="logout.php" style="color:white;">Deconectare</a></nav>
</header>
<div class="container" style="display: flex; gap: 20px; justify-content: center; margin-top: 50px;">
    
    <div class="section" style="flex: 1; text-align: center;">
        <h3>Programare Nouă</h3>
        <p>Ai nevoie de o consultație? Alege medicul și data dorită.</p>
        <a href="make_appointment.php" class="btn-save" style="display:inline-block; padding:15px 25px; background:#0099cc; color:white; text-decoration:none; border-radius:5px; font-weight:bold;">SOLICITĂ PROGRAMARE</a>
    </div>

    <div class="section" style="flex: 1; text-align: center; border-left-color: #28a745;">
        <h3>Istoric & Recomandări</h3>
        <p>Vezi programările tale și notele trimise de medici.</p>
        <a href="my_appointments.php" style="display:inline-block; padding:15px 25px; background:#28a745; color:white; text-decoration:none; border-radius:5px; font-weight:bold;">VEZI ISTORICUL MEU</a>
    </div>

</div>
</body>
</html>