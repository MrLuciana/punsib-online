<?php
require_once '../../config/db.php';
require_once '../../config/functions.php';
require '../../vendor/autoload.php'; // โหลด PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$token_lifetime_minutes = 30;
$shop_name = "ร้านขนมปั้นสิบยายนิดพัทลุง";

// 1. ตรวจสอบ Method และ CSRF Token
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setAlert('danger', 'Invalid request method.');
    redirect(BASE_URL . 'forgot-password.php');
}

if (!verifyCsrfToken($_POST['csrf_token'])) {
    setAlert('danger', 'CSRF token validation failed.');
    redirect(BASE_URL . 'forgot-password.php');
}

// 2. ตรวจสอบและกรองข้อมูลอีเมล
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
if (!$email) {
    setAlert('danger', 'กรุณากรอกอีเมลให้ถูกต้อง');
    $_SESSION['old_input']['email'] = $_POST['email'];
    redirect(BASE_URL . 'forgot-password.php');
}

// 3. ค้นหาผู้ใช้
$stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$generic_success_message = 'หากมีบัญชีที่ใช้อีเมลนี้ในระบบ เราได้ส่งลิงก์สำหรับตั้งรหัสผ่านใหม่ไปให้แล้ว กรุณาตรวจสอบกล่องจดหมายของคุณ';

if ($user) {
    try {
        $conn->beginTransaction();

        $conn->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$user['id']]);

        $token = bin2hex(random_bytes(32));
        $token_hash = hash('sha256', $token);
        $expires_at = date('Y-m-d H:i:s', time() + ($token_lifetime_minutes * 60));

        $conn->prepare("INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, ?)")
              ->execute([$user['id'], $token_hash, $expires_at]);

        $reset_link = BASE_URL . "reset-password.php?token={$token}&email=" . urlencode($email);

        // ส่งอีเมลด้วย PHPMailer
        $mail = new PHPMailer(true);
        try {
            // ตั้งค่า SMTP Server
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // หรือ SMTP ของโฮสต์คุณ
            $mail->SMTPAuth = true;
            $mail->Username = 'your-email@gmail.com'; // แก้ไข
            $mail->Password = 'your-app-password';     // ใช้รหัสผ่านแบบ App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('your-email@gmail.com', $shop_name);
            $mail->addAddress($email, $user['username']);
            $mail->isHTML(true);
            $mail->Subject = "คำขอตั้งรหัสผ่านใหม่สำหรับ {$shop_name}";
            $mail->Body = "
                <p>สวัสดีคุณ " . htmlspecialchars($user['username']) . ",</p>
                <p>กรุณาคลิกลิงก์นี้เพื่อรีเซ็ตรหัสผ่าน:</p>
                <p><a href='{$reset_link}'>{$reset_link}</a></p>
                <p>ลิงก์จะหมดอายุใน {$token_lifetime_minutes} นาที</p>
                <p>หากไม่ได้ร้องขอ โปรดเพิกเฉย</p>
                <br><p>จากทีมงาน {$shop_name}</p>
            ";

            $mail->send();

            $conn->commit();
        } catch (Exception $e) {
            $conn->rollBack();
            // error_log("Email sending failed: " . $mail->ErrorInfo);
        }

    } catch (Exception $e) {
        $conn->rollBack();
        // error_log("DB transaction error: " . $e->getMessage());
    }
}

setAlert('success', $generic_success_message);
redirect(BASE_URL . 'forgot-password.php');
?>
