<?php
require_once '../config/db.php';
require_once '../config/functions.php';

session_start();

// ตรวจสอบ CSRF Token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid CSRF token'
    ]);
    exit();
}

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'กรุณาเข้าู่ระบบก่อน',
        'login_required' => true
    ]);
    exit();
}

$userId = $_SESSION['user_id'];
$fullname = trim($_POST['fullname']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');

// ตรวจสอบข้อมูลที่จำเป็น
if (empty($fullname) || empty($email)) {
    echo json_encode([
        'success' => false,
        'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน'
    ]);
    exit();
}

// ตรวจสอบรูปแบบอีเมล
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'รูปแบบอีเมลไม่ถูกต้อง'
    ]);
    exit();
}

// ตรวจสอบว่า email นี้มีผู้ใช้อื่นใช้แล้วหรือไม่
try {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $userId]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'อีเมลนี้มีผู้ใช้งานแล้ว'
        ]);
        exit();
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดในการตรวจสอบอีเมล'
    ]);
    exit();
}

// อัพเดทข้อมูลผู้ใช้
try {
    $stmt = $conn->prepare("
        UPDATE users 
        SET fullname = ?, email = ?, phone = ?, address = ?, updated_at = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$fullname, $email, $phone, $address, $userId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'อัพเดทข้อมูลส่วนตัวเรียบร้อยแล้ว',
        'redirect' => 'profile.php'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดในการอัพเดทข้อมูล: ' . $e->getMessage()
    ]);
}
