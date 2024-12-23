<?php
require_once 'database/database_connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Face Recognition Attendance</title>
    <link rel="stylesheet" href="resources/assets/css/style.css">
    <script defer src="models/face-api.min.js"></script>
    <script defer src="resources/assets/javascript/script.js"></script>
</head>
<body>
    <div class="container">
        <h1>Face Recognition Attendance System</h1>
        
        <div class="navigation-buttons">
            <a href="admin/login.php" class="btn">Admin Login</a>
            <a href="register_student.php" class="btn">Register Student</a>
        </div>
        
        <div class="video-container">
            <video id="video" width="720" height="560" autoplay muted></video>
        </div>

        <div class="student-info" id="studentInfo">
            <h2>Student Information</h2>
            <div id="studentDetails">
                <!-- Student details will be displayed here -->
            </div>
        </div>
    </div>

    <script>
        // Add error handling for face-api.js loading
        window.onerror = function(msg, url, lineNo, columnNo, error) {
            console.error('Error: ' + msg + '\nURL: ' + url + '\nLine: ' + lineNo + '\nColumn: ' + columnNo + '\nError object: ' + JSON.stringify(error));
            return false;
        };
    </script>
</body>
</html> 