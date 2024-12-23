<?php
require_once 'database/database_connection.php';

try {
    $stmt = $pdo->query("SELECT id, nim FROM students");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($students);
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?> 