<?php
session_start();
include 'db.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// --- STATISTICI VIZITATORI ---
// Vizitatori totali azi
$visitors_today = $conn->query("SELECT COUNT(DISTINCT visitor_ip) as count FROM site_visits WHERE DATE(visit_time) = CURDATE()")->fetch_assoc()['count'];
// Pagini accesate azi (volum trafic)
$hits_today = $conn->query("SELECT COUNT(*) as count FROM site_visits WHERE DATE(visit_time) = CURDATE()")->fetch_assoc()['count'];

// --- STATISTICI BUSINESS ---
$total_patients = $conn->query("SELECT COUNT(*) as count FROM patients")->fetch_assoc()['count'];
$total_doctors = $conn->query("SELECT COUNT(*) as count FROM doctors WHERE status='Approved'")->fetch_assoc()['count'];
$total_appointments = $conn->query("SELECT COUNT(*) as count FROM appointments")->fetch_assoc()['count'];

// Programări săptămâna curentă vs trecută
$apps_this_week = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE YEARWEEK(appointment_date, 1) = YEARWEEK(CURDATE(), 1)")->fetch_assoc()['count'];
$apps_last_week = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE YEARWEEK(appointment_date, 1) = YEARWEEK(CURDATE(), 1) - 1")->fetch_assoc()['count'];

$diff = $apps_this_week - $apps_last_week;
$trend = ($apps_last_week > 0) ? round(($diff / $apps_last_week) * 100, 1) : 100;

// Top Medici
$stats_docs = $conn->query("
    SELECT d.full_name, d.specialization, COUNT(a.id) as total_apps,
    SUM(CASE WHEN a.status = 'Completed' THEN 1 ELSE 0 END) as completed
    FROM doctors d
    LEFT JOIN appointments a ON d.id = a.doctor_id
    WHERE d.status = 'Approved'
    GROUP BY d.id
    ORDER BY total_apps DESC
");
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard Pro</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.07); position: relative; overflow: hidden; }
        .stat-card::after { content: ""; position: absolute; bottom: 0; left: 0; width: 100%; height: 4px; background: #004d66; }
        .stat-card h3 { margin: 0; color: #7f8c8d; font-size: 0.85em; text-transform: uppercase; letter-spacing: 1px; }
        .stat-card .value { font-size: 2.2em; font-weight: 800; color: #2c3e50; margin: 10px 0; }
        .visitor-box { background: #004d66 !important; color: white !important; }
        .visitor-box h3, .visitor-box .value { color: white !important; }
        .trend { font-size: 0.9em; font-weight: bold; padding: 3px 8px; border-radius: 15px; }
        .up { background: #d4edda; color: #155724; }
        .down { background: #f8d7da; color: #721c24; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; }
        th { background: #f8f9fa; padding: 15px; text-align: left; color: #333; }
        td { padding: 15px; border-top: 1px solid #eee; }
        .progress-bar { background: #eee; border-radius: 5px; height: 8px; width: 100px; display: inline-block; }
        .progress-fill { background: #004d66; height: 100%; border-radius: 5px; }
    </style>
</head>
<body>

<header style="display: flex; justify-content: space-between; align-items: center; padding: 10px 5%; background: #004d66; color: white;">
    <h2>🏥 Panel Control Spital</h2>
    <nav>
        <a href="admin_doctors.php" style="color: white; text-decoration: none; font-weight: bold; margin-right: 20px;">👨‍⚕️ Gestionare Medici</a>
        <a href="logout.php" style="color: #ff4d4d; text-decoration: none; font-weight: bold;">Ieșire</a>
    </nav>
</header>

<div class="container" style="padding: 30px 5%;">
    
    <div class="stats-grid">
        <div class="stat-card visitor-box">
            <h3>Vizitatori Unici (Azi)</h3>
            <div class="value"><?= $visitors_today ?></div>
            <p>Utilizatori diferiți pe site</p>
        </div>
        <div class="stat-card">
            <h3>Afișări Pagini (Azi)</h3>
            <div class="value"><?= $hits_today ?></div>
            <p>Total click-uri efectuate</p>
        </div>
        <div class="stat-card">
            <h3>Pacienți Totali</h3>
            <div class="value"><?= $total_patients ?></div>
            <p>Conturi create în sistem</p>
        </div>
        <div class="stat-card">
            <h3>Programări Săptămânale</h3>
            <div class="value"><?= $apps_this_week ?></div>
            <span class="trend <?= $trend >= 0 ? 'up' : 'down' ?>">
                <?= $trend >= 0 ? '▲' : '▼' ?> <?= abs($trend) ?>%
            </span>
        </div>
    </div>

    

    <div style="background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.07);">
        <h3 style="margin-bottom: 20px; color: #2c3e50; border-left: 5px solid #004d66; padding-left: 10px;">📊 Performanță Echpă Medicală</h3>
        <table>
            <thead>
                <tr>
                    <th>Nume Medic</th>
                    <th>Specializare</th>
                    <th>Total Programări</th>
                    <th>Finalizate</th>
                    <th>Eficiență</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $stats_docs->fetch_assoc()): 
                    $eff = ($row['total_apps'] > 0) ? round(($row['completed'] / $row['total_apps']) * 100) : 0;
                ?>
                <tr>
                    <td><strong>Dr. <?= htmlspecialchars($row['full_name']) ?></strong></td>
                    <td><?= htmlspecialchars($row['specialization']) ?></td>
                    <td><?= $row['total_apps'] ?></td>
                    <td style="color: green; font-weight: bold;"><?= $row['completed'] ?></td>
                    <td>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= $eff ?>%;"></div>
                        </div>
                        <span style="font-size: 0.8em; margin-left: 5px;"><?= $eff ?>%</span>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 30px; display: flex; gap: 15px;">
        <?php 
        $status_res = $conn->query("SELECT status, COUNT(*) as c FROM appointments GROUP BY status");
        while($st = $status_res->fetch_assoc()): 
            $colors = ['Scheduled' => '#007bff', 'Completed' => '#28a745', 'Cancelled' => '#dc3545'];
            $c = $colors[$st['status']] ?? '#6c757d';
        ?>
            <div style="flex: 1; padding: 15px; border-radius: 8px; background: <?= $c ?>; color: white; text-align: center;">
                <div style="font-size: 0.8em; text-transform: uppercase;"><?= $st['status'] ?></div>
                <div style="font-size: 1.5em; font-weight: bold;"><?= $st['c'] ?></div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

</body>
</html>