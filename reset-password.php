<?php
require_once 'config/db.php';
require_once 'config/functions.php';

// --- เริ่มส่วนประมวลผล (Processing Logic) ---

// ตรวจสอบ Token และ Email จาก URL ก่อนแสดงฟอร์ม
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['token']) || !isset($_GET['email'])) {
        setAlert('danger', 'ลิงก์สำหรับตั้งรหัสผ่านใหม่ไม่ถูกต้อง');
        redirect(BASE_URL . 'login.php');
    }

    $token = $_GET['token'];
    $email = $_GET['email'];

    // ตรวจสอบ Token ในฐานข้อมูล
    $stmt = $conn->prepare(
        "SELECT pr.* FROM password_resets pr 
         JOIN users u ON pr.user_id = u.id 
         WHERE u.email = ? LIMIT 1"
    );
    $stmt->execute([$email]);
    $reset_data = $stmt->fetch(PDO::FETCH_ASSOC);

    $is_token_valid = $reset_data && hash_equals($reset_data['token_hash'], hash('sha256', $token));
    $is_token_expired = $reset_data && (strtotime($reset_data['expires_at']) < time());

    if (!$is_token_valid || $is_token_expired) {
        // ถ้า Token ไม่ถูกต้องหรือหมดอายุ ให้ลบออกจาก DB (ถ้ามี)
        if ($reset_data) {
            $conn->prepare("DELETE FROM password_resets WHERE id = ?")->execute([$reset_data['id']]);
        }
        setAlert('danger', 'ลิงก์สำหรับตั้งรหัสผ่านใหม่ไม่ถูกต้องหรือหมดอายุแล้ว');
        redirect(BASE_URL . 'login.php');
    }
}


// ประมวลผลเมื่อมีการส่งฟอร์มตั้งรหัสผ่านใหม่
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. ตรวจสอบ CSRF และข้อมูลที่ส่งมา
    if (!verifyCsrfToken($_POST['_csrf_token'])) {
        setAlert('danger', 'การดำเนินการไม่ถูกต้อง (Invalid CSRF Token)');
        redirect(BASE_URL . 'login.php');
    }

    $token = $_POST['token'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password_confirmation = $_POST['password_confirmation'];

    // 2. ตรวจสอบความถูกต้องของ Token อีกครั้ง (สำคัญมาก)
    $stmt = $conn->prepare(
        "SELECT pr.*, u.id as user_id FROM password_resets pr 
         JOIN users u ON pr.user_id = u.id 
         WHERE u.email = ? LIMIT 1"
    );
    $stmt->execute([$email]);
    $reset_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $is_token_valid = $reset_data && hash_equals($reset_data['token_hash'], hash('sha256', $token));
    $is_token_expired = $reset_data && (strtotime($reset_data['expires_at']) < time());

    if (!$is_token_valid || $is_token_expired) {
        setAlert('danger', 'การดำเนินการหมดอายุหรือไม่ถูกต้อง กรุณาขอลิงก์ใหม่อีกครั้ง');
        redirect(BASE_URL . 'forgot-password.php');
    }

    // 3. ตรวจสอบรหัสผ่าน
    $errors = [];
    if (empty($password)) {
        $errors[] = "กรุณากรอกรหัสผ่านใหม่";
    } elseif (strlen($password) < 8) {
        $errors[] = "รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร";
    }
    if ($password !== $password_confirmation) {
        $errors[] = "รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน";
    }

    if (!empty($errors)) {
        // หากมีข้อผิดพลาด ให้แสดงข้อความและคงค่า token/email ไว้ในฟอร์ม
        $_SESSION['reset_errors'] = $errors;
        // ไม่ redirect แต่ปล่อยให้โค้ด HTML ด้านล่างทำงาน
    } else {
        // 4. อัปเดตรหัสผ่านและลบ Token
        try {
            $conn->beginTransaction();

            $new_password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // อัปเดตรหัสผ่านในตาราง users
            $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $updateStmt->execute([$new_password_hash, $reset_data['user_id']]);

            // ลบ Token ที่ใช้แล้วออกจากตาราง password_resets
            $deleteStmt = $conn->prepare("DELETE FROM password_resets WHERE user_id = ?");
            $deleteStmt->execute([$reset_data['user_id']]);

            $conn->commit();

            setAlert('success', 'เปลี่ยนรหัสผ่านสำเร็จแล้ว! กรุณาเข้าสู่ระบบด้วยรหัสผ่านใหม่');
            redirect(BASE_URL . 'login.php');

        } catch (Exception $e) {
            $conn->rollBack();
            setAlert('danger', 'เกิดข้อผิดพลาดในการบันทึกข้อมูล กรุณาลองใหม่อีกครั้ง');
            // ไม่ redirect เพื่อให้ผู้ใช้เห็นข้อความ
        }
    }
}

// --- จบส่วนประมวลผล ---


// --- เริ่มส่วนแสดงผล (Display Logic) ---
$pageTitle = "ตั้งรหัสผ่านใหม่ - ร้านขนมปั้นสิบยายนิดพัทลุง";
include 'includes/head.php';
?>

<body class="bg-light">
    <div class="auth-container">
        <?php include 'includes/navbar.php'; ?>

        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-success text-white text-center py-3">
                            <h3 class="mb-0"><i class="fas fa-redo-alt me-2"></i>ตั้งรหัสผ่านใหม่</h3>
                        </div>
                        <div class="card-body p-4">
                            <?php 
                            // แสดงข้อความแจ้งเตือนทั่วไป
                            displayAlert(); 

                            // แสดงข้อผิดพลาดจากการตั้งรหัสผ่าน (ถ้ามี)
                            if(isset($_SESSION['reset_errors'])): ?>
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        <?php foreach($_SESSION['reset_errors'] as $error): ?>
                                            <li><?php echo htmlspecialchars($error); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <?php unset($_SESSION['reset_errors']); ?>
                            <?php endif; ?>

                            <form id="resetPasswordForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" novalidate>
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">รหัสผ่านใหม่</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                        <button class="btn btn-outline-secondary toggle-password" type="button">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">ต้องมีความยาวอย่างน้อย 8 ตัวอักษร</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">ยืนยันรหัสผ่านใหม่</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-success btn-lg w-100 py-2 mt-3">
                                    <i class="fas fa-save me-2"></i>บันทึกรหัสผ่านใหม่
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include 'includes/footer.php'; ?>
    </div>

    <script>
    // ฟังก์ชันสำหรับแสดง/ซ่อนรหัสผ่าน
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.closest('.input-group').querySelector('input');
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });

    // ฟังก์ชันสำหรับตรวจสอบฟอร์มก่อนส่ง
    (function() {
        'use strict';
        const form = document.getElementById('resetPasswordForm');
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    })();
    </script>
</body>
</html>