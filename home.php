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
<div class="section" style="background: #f0f7f9; border-left: 5px solid #004d66; padding: 20px; margin-top: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
    <h3 style="color: #004d66; margin-top: 0; display: flex; align-items: center;">
        <span style="font-size: 1.5em; margin-right: 10px;">🩺</span> Recomandări și Actualități Medicale
    </h3>
    
    <?php
    $xml_source = '<?xml version="1.0" encoding="UTF-8"?>
    <healthData>
        <article>
            <title>Ghid de prevenție: Hidratarea în timpul iernii</title>
            <type>Sfaturi Practice</type>
            <urgency>Normal</urgency>
            <source>Ministerul Sănătății</source>
        </article>
        <article>
            <title>Alertă: Simptomele noii tulpini de gripă sezonieră</title>
            <type>Alertă Medicală</type>
            <urgency>Ridicat</urgency>
            <source>OMS România</source>
        </article>
        <article>
            <title>Importanța controlului cardiologic anual</title>
            <type>Educație</type>
            <urgency>Recomandat</urgency>
            <source>Centrul de Prevenție</source>
        </article>
    </healthData>';

    $parsed_content = simplexml_load_string($xml_source);

    if ($parsed_content) {
        echo "<div style='display: grid; gap: 15px; margin-top: 15px;'>";
        
        foreach ($parsed_content->article as $item) {
            $color = ($item->urgency == 'Ridicat') ? '#d32f2f' : '#00796b';
            
            echo "<div style='background: white; padding: 12px; border-radius: 6px; border-left: 4px solid $color;'>";
            echo "<strong style='color: #333; font-size: 1em; display: block;'> " . $item->title . "</strong>";
            echo "<div style='margin-top: 5px;'>";
            echo "<span style='font-size: 0.75em; background: #eee; padding: 3px 7px; border-radius: 10px; color: #555;'>" . $item->type . "</span> ";
            echo "<small style='color: #888; margin-left: 10px;'>Sursa: " . $item->source . "</small>";
            echo "</div>";
            echo "</div>";
        }
        
    } else {
        echo "<p>Eroare la procesarea datelor externe.</p>";
    }
    ?>
</div>
</body>
</html>