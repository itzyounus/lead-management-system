<?php
// Database configuration
$host = 'localhost';
   $dbname = 'lead_management';  // Your database name
   $username = 'root';            // Default XAMPP username
   $password = '';

// Set JSON response header
header('Content-Type: application/json');

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get and sanitize input
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Name is required';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($company)) {
        $errors[] = 'Company is required';
    }
    
    if (empty($phone)) {
        $errors[] = 'Phone is required';
    } elseif (!preg_match('/^[\d\s\-\+\(\)]{10,}$/', $phone)) {
        $errors[] = 'Invalid phone format';
    }
    
    // If validation passes, insert into database
    if (empty($errors)) {
        try {
            // Create PDO connection
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Prepare SQL statement
            $sql = "INSERT INTO leads (name, email, company, phone, created_at) 
                    VALUES (:name, :email, :company, :phone, NOW())";
            
            $stmt = $pdo->prepare($sql);
            
            // Bind parameters
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':company', $company);
            $stmt->bindParam(':phone', $phone);
            
            // Execute
            $stmt->execute();
            
            // Return success response
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Lead submitted successfully']);
            
        } catch (PDOException $e) {
            // Return error response
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        // Return validation errors
        http_response_code(400);
        echo json_encode(['success' => false, 'errors' => $errors]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>