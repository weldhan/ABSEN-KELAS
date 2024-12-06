<?php
session_start();
include_once("koneksi.php");

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Function to check if the user is absent
function checkIfAbsent($userId) {
    global $mysqli; // Use the global mysqli connection

    // Get the current date
    $currentDate = date('Y-m-d');

    // Prepare the SQL statement to check for absence
    $query = "SELECT COUNT(*) as count FROM absensi WHERE nisn = ? AND DATE(waktu_kehadiran) = ? AND status = 'Hadir'";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $_SESSION['nisn'], $currentDate);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // Return true if the user is absent, false otherwise
    return $row['count'] > 0; // If count is greater than 0, the user is absent
}

// Check if the user is absent
$isAbsent = checkIfAbsent($_SESSION['user_id']);

if ($isAbsent) {
    // Update the absence record to present
    $nama = $_SESSION['nama'];
    $nisn = $_SESSION['nisn'];
    $status = 'Hadir'; // Change status to 'Hadir'

    $query = "INSERT INTO absensi (nama, nisn, status, waktu_kehadiran) VALUES (?, ?, ?, NOW())";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sss", $nama, $nisn, $status);
    $stmt->execute();
}

// Fetching the latest absence history
try {
    // Fetch the latest 10 records (or adjust as needed)
    $hasil = mysqli_query($mysqli, "SELECT * FROM absensi ORDER BY waktu_kehadiran DESC LIMIT 10");
} catch (mysqli_sql_exception $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Riwayat absen</title>
</head>
<body>
<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1">Riwayat</span>
        <span class="navbar-text">
            Welcome, <?php echo htmlspecialchars($_SESSION['nama']); ?>
        </span>
        <form method="POST" action="logout.php" class="d-inline">
            <button type="submit" class="btn btn-outline-light">Logout</button>
        </form>
    </div>
</nav>

<div class="container mt-5">
    <h2 class="text-center">Riwayat Absen</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>NISN</th>
                <th>Waktu Kehadiran</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($hasil) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($hasil)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['nama']); ?></td>
                        <td><?php echo htmlspecialchars($row['nisn']); ?></td>
                        <td><?php echo htmlspecialchars($row['waktu_kehadiran']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">No absence records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>