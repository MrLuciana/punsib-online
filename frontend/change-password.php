<?php
require_once '../config/db.php';
require_once '../config/functions.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// ดึงข้อมูลผู้ใช้ปัจจุบัน
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: logout.php');
    exit();
}

$errors = [];
$success = false;

// ตรวจสอบการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับค่าจากฟอร์ม
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // ตรวจสอบความถูกต้องของข้อมูล
    if (empty($current_password)) {
        $errors['current_password'] = 'กรุณากรอกรหัสผ่านปัจจุบัน';
    } elseif (!password_verify($current_password, $user['password'])) {
        $errors['current_password'] = 'รหัสผ่านปัจจุบันไม่ถูกต้อง';
    }

    if (empty($new_password)) {
        $errors['new_password'] = 'กรุณากรอกรหัสผ่านใหม่';
    } elseif (strlen($new_password) < 8) {
        $errors['new_password'] = 'รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร';
    }

    if ($new_password !== $confirm_password) {
        $errors['confirm_password'] = 'รหัสผ่านใหม่ไม่ตรงกัน';
    }

    // หากไม่มีข้อผิดพลาด ให้อัปเดตรหัสผ่าน
    if (empty($errors)) {
        try {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$hashed_password, $userId]);
            
            $success = true;
            $_SESSION['success_message'] = 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว';
            
            header('Location: profile.php');
            exit();
        } catch (PDOException $e) {
            $errors['database'] = 'เกิดข้อผิดพลาดในการเปลี่ยนรหัสผ่าน: ' . $e->getMessage();
        }
    }
}

$pageTitle = "เปลี่ยนรหัสผ่าน - " . $user['fullname'];
include '../includes/head.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-lock me-2"></i>เปลี่ยนรหัสผ่าน</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">รหัสผ่านปัจจุบัน <span class="text-danger">*</span></label>
                            <input type="password" class="form-control <?= isset($errors['current_password']) ? 'is-invalid' : '' ?>" 
                                   id="current_password" name="current_password" required>
                            <?php if (isset($errors['current_password'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['current_password']) ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">รหัสผ่านใหม่ <span class="text-danger">*</span></label>
                            <input type="password" class="form-control <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>" 
                                   id="new_password" name="new_password" required>
                            <?php if (isset($errors['new_password'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['new_password']) ?></div>
                            <?php endif; ?>
                            <small class="text-muted">รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">ยืนยันรหัสผ่านใหม่ <span class="text-danger">*</span></label>
                            <input type="password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" 
                                   id="confirm_password" name="confirm_password" required>
                            <?php if (isset($errors['confirm_password'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['confirm_password']) ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="profile.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>ย้อนกลับ
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>เปลี่ยนรหัสผ่าน
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include '../includes/footer.php';
?>
