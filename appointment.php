<?php
session_start();
$message = "";
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'db.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$patient_id = $_SESSION['id'];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book'])) {
    $date = $_POST['date'];
    $time = $_POST['time'];
    $reason = trim($_POST['reason']);
    $today = date("Y-m-d");

    if ($date < $today) {
        $_SESSION['message'] = "You cannot book appointments in the past.";
    } else {
        $query = "INSERT INTO appointments (patient_id, appointment_date, appointment_time, reason) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            die("SQL prepare() failed: " . $conn->error);
        }
        $stmt->bind_param("isss", $patient_id, $date, $time, $reason);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Appointment booked successfully!";
        } else {
            $_SESSION['message'] = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }

    header("Location: appointment.php");
    exit();
}


if (isset($_GET['cancel'])) {
    $app_id = intval($_GET['cancel']);
    $cancel_query = "UPDATE appointments SET status='Cancelled' WHERE id=? AND patient_id=?";
    $cancel_stmt = $conn->prepare($cancel_query);
    if ($cancel_stmt) {
        $cancel_stmt->bind_param("ii", $app_id, $patient_id);
        $cancel_stmt->execute();
        $cancel_stmt->close();
        $_SESSION['message'] = "Appointment cancelled.";
        header("Location: appointment.php");
        exit();
    }
}


$query = "SELECT * FROM appointments WHERE patient_id=? ORDER BY appointment_date DESC, appointment_time DESC";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("SQL prepare() failed while loading appointments: " . $conn->error);
}
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Appointments</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f9f9f9; color: #333; margin: 30px; }
        h1 { color: #0066cc; }
        form { margin-bottom: 30px; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); width: 400px; }
        input, textarea, button { width: 100%; padding: 10px; margin-top: 10px; border-radius: 5px; border: 1px solid #ccc; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: center; }
        th { background-color: #007bff; color: white; }
        .msg { padding: 10px; border-radius: 5px; margin-bottom: 10px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .cancel { background: #ffeeba; color: #856404; }
        a.cancel-link { color: red; text-decoration: none; }
        a.cancel-link:hover { text-decoration: underline; }
    </style>
</head>
<body>

<h1>Book an Appointment</h1>

<?php if ($message): ?>
    <div class="msg <?= strpos($message, 'successfully') !== false ? 'success' : (strpos($message, 'cancelled') !== false ? 'cancel' : 'error') ?>">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<form action="appointment.php" method="POST">
    <label>Date:</label>
    <input type="date" name="date" required min="<?= date('Y-m-d'); ?>">

    <label>Time (24-hour format):</label>
    <input type="time" name="time" required>

    <label>Reason:</label>
    <textarea name="reason" placeholder="Describe your reason..."></textarea>

    <button type="submit" name="book">Book Appointment</button>
</form>

<h2>Your Appointments</h2>

<table>
    <tr>
        <th>Date</th>
        <th>Time</th>
        <th>Reason</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['appointment_date']) ?></td>
            <td><?= htmlspecialchars(substr($row['appointment_time'], 0, 5)) ?></td>
            <td><?= htmlspecialchars($row['reason']) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td>
                <?php if ($row['status'] == 'Scheduled'): ?>
                    <a class="cancel-link" href="appointment.php?cancel=<?= $row['id'] ?>" onclick="return confirm('Cancel this appointment?');">Cancel</a>
                <?php else: ?>
                    <span style="color:gray;">—</span>
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
