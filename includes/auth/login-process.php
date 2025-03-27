<?php
require_once '../../config/db.php';
require_once '../../config/functions.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับค่าจากฟอร์ม
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;

    // ค้นหาผู้ใช้
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // ตรวจสอบผู้ใช้และรหัสผ่าน
    if($user && password_verify($password, $user['password'])) {
        // ตั้งค่า Session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['fullname'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;

        // ถ้าเลือกจดจำฉัน
        if($remember) {
            $token = bin2hex(random_bytes(32));
            $expire = time() + (30 * 24 * 60 * 60); // 30 วัน

            // บันทึก Token ในฐานข้อมูล
            $stmt = $conn->prepare("UPDATE users SET remember_token = ?, token_expire = ? WHERE id = ?");
            $stmt->execute([$token, date('Y-m-d H:i:s', $expire), $user['id']]);

            // ตั้งค่า Cookie
            setcookie('remember_token', $token, $expire, '/');
        }

        // ตรวจสอบบทบาทและ redirect
        if($user['role'] === 'admin') {
            redirect(ADMIN_URL . 'dashboard.php');
        } else {
            redirect(BASE_URL . 'frontend/index.php');
        }
    } else {
        $_SESSION['alert'] = [
            'type' => 'danger',
            'message' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง'
        ];
        $_SESSION['old_input'] = ['username' => $username];
        redirect(BASE_URL . 'login.php');
    }
} else {
    redirect(BASE_URL . 'login.php');
}
