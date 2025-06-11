<?php
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../../vendor/autoload.php'; // << สำคัญ! ใช้ PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$token_lifetime_minutes = 30;
$shop_name = "ร้านขนมปั้นสิบยายนิดพัทลุง";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setAlert('danger', 'Invalid request method.');
    redirect(BASE_URL . 'forgot-password.php');
}

if (!verifyCsrfToken($_POST['csrf_token'])) {
    setAlert('danger', 'CSRF token validation failed.');
    redirect(BASE_URL . 'forgot-password.php');
}

$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

if (!$email) {
    setAlert('danger', 'กรุณากรอกอีเมลให้ถูกต้อง');
    $_SESSION['old_input']['email'] = $_POST['email'];
    redirect(BASE_URL . 'forgot-password.php');
}

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

        $reset_link = BASE_URL . "reset-password.php?token=$token&email=" . urlencode($email);
        $subject = "คำขอตั้งรหัสผ่านใหม่สำหรับ $shop_name";
        $message = "
            <html>
            <head><title>{$subject}</title></head>
            <body>
                <p>สวัสดีคุณ " . htmlspecialchars($user['username']) . ",</p>
                <p>เราได้รับคำขอตั้งรหัสผ่านใหม่ กรุณาคลิกที่ลิงก์ด้านล่าง:</p>
                <p><a href='{$reset_link}'>{$reset_link}</a></p>
                <p>ลิงก์จะหมดอายุใน {$token_lifetime_minutes} นาที</p>
                <br><p>ทีมงาน $shop_name</p>
            </body>
            </html>
        ";

        // ส่งอีเมลด้วย PHPMailer
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // หรือ SMTP โฮสต์ของคุณ
        $mail->SMTPAuth = true;
        $mail->Username = 'thaniya.n@rmutsvmail.com'; // 🔁 ใส่อีเมลคุณ
        $mail->Password = 'swob pymf tgbc ovkg';    // 🔁 ใช้ App Password ไม่ใช่รหัสผ่านบัญชี
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('thaniya.n@rmutsvmail.com', $shop_name);
        $mail->addAddress($email, $user['username']);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->CharSet = 'UTF-8'; // สำคัญมากเพื่อให้แสดงภาษาไทยได้ถูกต้อง
        $mail->Encoding = 'base64'; // ป้องกันอักขระเพี้ยน โดยเฉพาะใน Subject


        $mail->send();

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("PHPMailer Error: " . $e->getMessage());
    }
}

setAlert('success', $generic_success_message);
redirect(BASE_URL . 'forgot-password.php');
