<?php
// กำหนดค่าารเชื่อมต่อฐานข้อมูล
define('DB_HOST', 'localhost');
define('DB_USER', 'mrlu_punsib');
define('DB_PASS', '47Gexr43@');
define('DB_NAME', 'punsib_online');

// กำหนด URL ของเว็บไซต์
define('BASE_URL', 'http://punsib.mrluciana.com/');
define('ADMIN_URL', BASE_URL . 'admin/');

// เริ่ม Session
session_start();

// ตรวจสอบ Remember Me
if(!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE remember_token = ? AND token_expire > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['fullname'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        
        // อัปเดต Token และวันหมดอายุ
        $newToken = bin2hex(random_bytes(32));
        $expire = time() + (30 * 24 * 60 * 60); // 30 วัน
        
        $stmt = $conn->prepare("UPDATE users SET remember_token = ?, token_expire = ? WHERE id = ?");
        $stmt->execute([$newToken, date('Y-m-d H:i:s', $expire), $user['id']]);
        
        setcookie('remember_token', $newToken, $expire, '/');
    }
}


// เชื่อมต่อฐานข้อมูล
try {
    $conn = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES utf8");
} catch(PDOException $e) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $e->getMessage());
}

?>
