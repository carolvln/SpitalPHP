<?php
session_start();
include 'db.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

$doctor_id = $_SESSION['id'];
$message = "";

$zile = ['Luni', 'Marti', 'Miercuri', 'Joi', 'Vineri', 'Sambata', 'Duminica'];

// Salvare program
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach ($zile as $zi) {
        $is_working = isset($_POST["work_$zi"]) ? 1 : 0;
        $start = $_POST["start_$zi"];
        $end = $_POST["end_$zi"];

        $stmt = $conn->prepare("INSERT INTO doctor_schedule (doctor_id, day_of_week, start_time, end_time, is_working) 
                                VALUES (?, ?, ?, ?, ?) 
                                ON DUPLICATE KEY UPDATE start_time=?, end_time=?, is_working=?");
        $stmt->bind_param("isssissi", $doctor_id, $zi, $start, $end, $is_working, $start, $end, $is_working);
        $stmt->execute();
    }
    $message = "✅ Programul a fost actualizat cu succes!";
}

// Preluare program actual
$current_schedule = [];
$res = $conn->query("SELECT * FROM doctor_schedule WHERE doctor_id = $doctor_id");
while ($row = $res->fetch_assoc()) {
    $current_schedule[$row['day_of_week']] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Setare Program Lucru</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .day-row { display: flex; align-items: center; gap: 20px; padding: 10px; border-bottom: 1px solid #eee; }
        .day-name { width: 100px; font-weight: bold; }
        .time-input { padding: 5px; }
    </style>
</head>
<body>
<header>
    <h1>Setări Program: Dr. <?= htmlspecialchars($_SESSION['name']) ?></h1>
    <a href="doctor_home.php" style="color:white;">Înapoi la Panou</a>
</header>

<div class="container">
    <form method="POST" class="section">
        <h2>Program Săptămânal</h2>
        <?php if($message) echo "<p style='color:green;'>$message</p>"; ?>
        
        <?php foreach ($zile as $zi): 
            $working = isset($current_schedule[$zi]) ? $current_schedule[$zi]['is_working'] : 0;
            $start = isset($current_schedule[$zi]) ? $current_schedule[$zi]['start_time'] : "08:00";
            $end = isset($current_schedule[$zi]) ? $current_schedule[$zi]['end_time'] : "16:00";
        ?>
        <div class="day-row">
            <div class="day-name">
                <input type="checkbox" name="work_<?= $zi ?>" <?= $working ? 'checked' : '' ?>> <?= $zi ?>
            </div>
            <div>
                De la: <input type="time" name="start_<?= $zi ?>" value="<?= substr($start, 0, 5) ?>" class="time-input">
                Până la: <input type="time" name="end_<?= $zi ?>" value="<?= substr($end, 0, 5) ?>" class="time-input">
            </div>
        </div>
        <?php endforeach; ?>

        <button type="submit" style="margin-top:20px; padding:12px 25px; background:#004d66; color:white; border:none; border-radius:5px; cursor:pointer; font-weight:bold;">
            Salvează Programul
        </button>
    </form>
</div>
</body>
</html>