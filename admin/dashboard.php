<?php
session_start();
require_once '../database/database_connection.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Handle student deletion
if (isset($_POST['delete_student'])) {
    try {
        $student_id = $_POST['student_id'];
        
        // First, delete the student's face image
        $stmt = $pdo->prepare("SELECT nim FROM students WHERE id = ?");
        $stmt->execute([$student_id]);
        $student = $stmt->fetch();
        
        if ($student) {
            // Delete face image
            $face_image = "../resources/faces/" . $student['nim'] . ".jpg";
            if (file_exists($face_image)) {
                unlink($face_image);
            }
            
            // Delete attendance logs
            $stmt = $pdo->prepare("DELETE FROM attendance_logs WHERE student_id = ?");
            $stmt->execute([$student_id]);
            
            // Delete student
            $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
            $stmt->execute([$student_id]);
            
            $success_message = "Student deleted successfully";
        }
    } catch (PDOException $e) {
        $error_message = "Error deleting student: " . $e->getMessage();
    }
}

// Fetch students
$stmt = $pdo->query("SELECT * FROM students ORDER BY name");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch attendance logs
$stmt = $pdo->query("
    SELECT a.*, s.nim, s.name, s.email 
    FROM attendance_logs a 
    JOIN students s ON a.student_id = s.id 
    ORDER BY a.check_in_time DESC
");
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../resources/assets/css/admin.css">
    <script>
        function confirmDelete(studentName) {
            return confirm(`Are you sure you want to delete ${studentName}? This action cannot be undone.`);
        }
    </script>
</head>
<body>
    <div class="admin-container">
        <h1>Admin Dashboard</h1>
        <div class="admin-header">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <!-- Student Management Section -->
        <div class="admin-section">
            <h2>Student Management</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>NIM</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= htmlspecialchars($student['nim']) ?></td>
                            <td><?= htmlspecialchars($student['name']) ?></td>
                            <td><?= htmlspecialchars($student['email']) ?></td>
                            <td><?= htmlspecialchars($student['phone']) ?></td>
                            <td>
                                <form method="POST" onsubmit="return confirmDelete('<?= htmlspecialchars($student['name']) ?>')">
                                    <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                                    <button type="submit" name="delete_student" class="delete-btn">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Attendance Logs Section -->
        <div class="admin-section">
            <h2>Attendance Logs</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>NIM</th>
                            <th>Name</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= htmlspecialchars($log['check_in_time']) ?></td>
                            <td><?= htmlspecialchars($log['nim']) ?></td>
                            <td><?= htmlspecialchars($log['name']) ?></td>
                            <td><?= htmlspecialchars($log['email']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html> 