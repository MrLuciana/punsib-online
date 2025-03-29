<?php
require_once '../../config/db.php';
require_once '../../config/functions.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับค่าจากฟอร์ม
    $fullname = sanitize($_POST['fullname']);
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $address = sanitize($_POST['address']);

    // ตรวจสอบความถูกต้องของข้อมูล
    $errors = [];

    // ตรวจสอบรหัสผ่าน
    if($password !== $confirm_password) {
        $errors[] = "รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน";
    }

    // ตรวจสอบความยาวรหัสผ่าน
    if(strlen($password) < 8) {
        $errors[] = "รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร";
    }

    // ตรวจสอบว่ามีชื่อผู้ใช้นี้แล้วหรือไม่
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if($stmt->rowCount() > 0) {
        $errors[] = "ชื่อผู้ใช้นี้มีอยู่แล้ว";
    }

    // ตรวจสอบว่ามีอีเมลนี้แล้วหรือไม่
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if($stmt->rowCount() > 0) {
        $errors[] = "อีเมลนี้มีอยู่แล้ว";
    }

    // ถ้ามีข้อผิดพลาด
    if(!empty($errors)) {
        $_SESSION['register_errors'] = $errors;
        $_SESSION['old_input'] = $_POST;
        redirect(BASE_URL . 'register.php');
    }

    // เข้ารหัสรหัสผ่าน
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // บันทึกข้อมูลผู้ใช้
    try {
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, fullname, phone, address, role) 
                               VALUES (?, ?, ?, ?, ?, ?, 'customer')");
        $stmt->execute([$username, $hashed_password, $email, $fullname, $phone, $address]);

        $_SESSION['alert'] = [
            'type' => 'success',
            'message' => 'สมัครสมาชิกสำเร็จ! กรุณาเข้าสู่ระบบ'
        ];
        redirect(BASE_URL . 'login.php');
    } catch(PDOException $e) {
        $_SESSION['alert'] = [
            'type' => 'danger',
            'message' => 'เกิดข้อผิดพลาดในการสมัครสมาชิก: ' . $e->getMessage()
        ];
        redirect(BASE_URL . 'register.php');
    }
} else {
    redirect(BASE_URL . 'register.php');
}
