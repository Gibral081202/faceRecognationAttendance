<?php
require_once 'database/database_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Handle student registration
        $nim = $_POST['nim'];
        $name = $_POST['name'];
        $address = $_POST['address'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        
        // Check if NIM already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE nim = ?");
        $stmt->execute([$nim]);
        if ($stmt->fetchColumn() > 0) {
            $error = "Student with this NIM already exists";
        } else {
            // Insert student data
            $stmt = $pdo->prepare("INSERT INTO students (nim, name, address, phone, email) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nim, $name, $address, $phone, $email]);
            
            // Handle image upload
            if (isset($_FILES['face_image']) && $_FILES['face_image']['error'] === 0) {
                $upload_dir = 'resources/faces/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Save image with NIM as filename
                $file_extension = pathinfo($_FILES['face_image']['name'], PATHINFO_EXTENSION);
                $target_file = $upload_dir . $nim . '.' . $file_extension;
                
                if (move_uploaded_file($_FILES['face_image']['tmp_name'], $target_file)) {
                    $success = "Student registered successfully!";
                } else {
                    $error = "Error uploading image.";
                }
            }
        }
    } catch (PDOException $e) {
        $error = "Database Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Student</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        form {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #666;
        }
        
        input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        button {
            width: 100%;
            padding: 10px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 15px;
        }
        
        button:hover {
            background: #45a049;
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #666;
            text-decoration: none;
        }
        
        .back-link:hover {
            color: #333;
        }
        
        .success-message {
            color: #4CAF50;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .error-message {
            color: #dc3545;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <form method="POST" enctype="multipart/form-data">
            <h2>Register New Student</h2>
            
            <?php if (isset($success)): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="nim">NIM:</label>
                <input type="text" id="nim" name="nim" required>
            </div>
            
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="address">Address:</label>
                <input type="text" id="address" name="address" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="tel" id="phone" name="phone" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="face_image">Face Image:</label>
                <input type="file" id="face_image" name="face_image" accept="image/*" required>
            </div>
            
            <button type="submit">Register Student</button>
            <a href="index.php" class="back-link">Back to Home</a>
        </form>
    </div>
</body>
</html> 