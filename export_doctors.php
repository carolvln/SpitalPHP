<?php
session_start();
include 'db.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    die("Acces neautorizat.");
}

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Lista_Medici_" . date('Y-m-d') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

$query = "SELECT full_name, email, specialization, status FROM doctors ORDER BY full_name ASC";
$result = $conn->query($query);

echo "<table border='1'>";
echo "<tr>
        <th style='background-color: #004d66; color: white;'>Nume Medic</th>
        <th style='background-color: #004d66; color: white;'>Email</th>
        <th style='background-color: #004d66; color: white;'>Specializare</th>
        <th style='background-color: #004d66; color: white;'>Status</th>
      </tr>";

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['specialization']) . "</td>";
        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='4'>Nu exista medici inregistrati.</td></tr>";
}
echo "</table>";
exit;
?>