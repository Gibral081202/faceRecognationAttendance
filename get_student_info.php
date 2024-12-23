<?php
require_once 'database/database_connection.php';

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($student) {
        // Log attendance
        $stmt = $pdo->prepare("INSERT INTO attendance_logs (student_id) VALUES (?)");
        $stmt->execute([$_GET['id']]);
        
        echo json_encode($student);
    } else {
        echo json_encode(['error' => 'Student not found']);
    }
}
?> 