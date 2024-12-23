CREATE DATABASE face_attendance;
USE face_attendance;

CREATE TABLE students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nim VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE attendance_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    check_in_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id)
);

CREATE TABLE admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100)
);

-- Insert default admin
INSERT INTO admin (username, password, email) 
VALUES ('admin', '$2y$10$U8qhRn2SFihnzAuQofyWOuJPGQno9ctvDvhSkXdZwHzg5riFUVT/O', 'admin@example.com'); 