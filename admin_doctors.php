<?php
session_start();
include 'db.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";

if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $stmt = $conn->prepare("UPDATE doctors SET status = 'Approved' WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "✅ Medic aprobat cu succes!";
    }
}

if (isset($_GET['deactivate'])) {
    $id = intval($_GET['deactivate']);
    $stmt = $conn->prepare("UPDATE doctors SET status = 'Inactive' WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "⚠️ Contul medicului a fost dezactivat.";
    }
}

$pending_doctors = $conn->query("SELECT * FROM doctors WHERE status = 'Pending' ORDER BY id DESC");

$active_doctors = $conn->query("SELECT * FROM doctors WHERE status = 'Approved' ORDER BY full_name ASC");

$inactive_doctors = $conn->query("SELECT * FROM doctors WHERE status = 'Inactive' ORDER BY full_name ASC");
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Gestionare Medici - Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .badge { padding: 5px 10px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
        .badge-pending { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .badge-approved { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .btn-action { text-decoration: none; padding: 5px 10px; border-radius: 4px; color: white; font-size: 0.85em; }
        .btn-approve { background: #28a745; }
        .btn-deactivate { background: #dc3545; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; background: white; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        .section-box { margin-bottom: 40px; }
    </style>
</head>
<body>
<header>
    <h1>Gestionare Personal Medical</h1>
    <nav>
        <a href="admin_board.php" style="color:white; margin-right:15px;">⬅️ Înapoi la Dashboard</a>
        <a href="logout.php" style="color:white;">Deconectare</a>
    </nav>
</header>

<div class="container">
    <?php if($message) echo "<p style='text-align:center; font-weight:bold; color:#004d66;'>$message</p>"; ?>

    <div class="section-box">
        <h2 style="color: #856404;">⏳ Cereri de Înregistrare Noi</h2>
        <?php if($pending_doctors->num_rows == 0): ?>
            <p>Nu există cereri noi în acest moment.</p>
        <?php else: ?>
            <table>
                <tr style="background: #fff3cd;">
                    <th>Nume</th>
                    <th>Email</th>
                    <th>Specializare</th>
                    <th>Acțiuni</th>
                </tr>
                <?php while($row = $pending_doctors->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['specialization']) ?></td>
                    <td>
                        <a href="admin_doctors.php?approve=<?= $row['id'] ?>" class="btn-action btn-approve" onclick="return confirm('Aprobi acest medic?')">Aprobă</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        <?php endif; ?>
    </div>

    <div class="section-box">
        <h2 style="color: #155724;">✅ Medici Activi</h2>
        <table>
            <thead>
                <tr style="background: #d4edda;">
                    <th>Nume</th>
                    <th>Specializare</th>
                    <th>Email</th>
                    <th>Acțiuni</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $active_doctors->fetch_assoc()): ?>
                <tr>
                    <td>Dr. <?= htmlspecialchars($row['full_name']) ?></td>
                    <td><?= htmlspecialchars($row['specialization']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td>
                        <a href="admin_doctors.php?deactivate=<?= $row['id'] ?>" class="btn-action btn-deactivate" onclick="return confirm('Sigur vrei să dezactivezi acest cont?')">Dezactivează</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php if($inactive_doctors->num_rows > 0): ?>
    <div class="section-box">
        <h3 style="color: #666;">📜 Arhivă Medici Inactivi</h3>
        <table style="opacity: 0.7;">
            <?php while($row = $inactive_doctors->fetch_assoc()): ?>
            <tr>
                <td>Dr. <?= htmlspecialchars($row['full_name']) ?></td>
                <td><?= htmlspecialchars($row['specialization']) ?></td>
                <td>
                    <a href="admin_doctors.php?approve=<?= $row['id'] ?>" style="color: #007bff; font-size: 0.85em;">Re-activează</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
    <?php endif; ?>
</div>
</body>
</html>