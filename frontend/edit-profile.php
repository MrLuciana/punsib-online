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
    $fullname = trim($_POST['fullname'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // ตรวจสอบความถูกต้องของข้อมูล
    if (empty($fullname)) {
        $errors['fullname'] = 'กรุณากรอกชื่อ-นามสกุล';
    }

    if (empty($phone)) {
        $errors['phone'] = 'กรุณากรอกเบอร์โทรศัพท์';
    } elseif (!preg_match('/^[0-9]{10,15}$/', $phone)) {
        $errors['phone'] = 'รูปแบบเบอร์โทรศัพท์ไม่ถูกต้อง';
    }

    if (empty($address)) {
        $errors['address'] = 'กรุณากรอกที่อยู่';
    }

    // หากไม่มีข้อผิดพลาด ให้อัปเดตข้อมูล
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("UPDATE users SET fullname = ?, phone = ?, address = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$fullname, $phone, $address, $userId]);
            
            $success = true;
            $_SESSION['success_message'] = 'อัปเดตโปรไฟล์เรียบร้อยแล้ว';
            
            // อัปเดตข้อมูลใน session
            $_SESSION['user_fullname'] = $fullname;
            
            header('Location: profile.php');
            exit();
        } catch (PDOException $e) {
            $errors['database'] = 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล: ' . $e->getMessage();
        }
    }
}

$pageTitle = "แก้ไขโปรไฟล์ - " . $user['fullname'];
include '../includes/head.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>แก้ไขโปรไฟล์</h5>
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
                            <label for="username" class="form-label">ชื่อผู้ใช้</label>
                            <input type="text" class="form-control" id="username" value="<?= htmlspecialchars($user['username']) ?>" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">อีเมล</label>
                            <input type="email" class="form-control" id="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label for="fullname" class="form-label">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= isset($errors['fullname']) ? 'is-invalid' : '' ?>" 
                                   id="fullname" name="fullname" 
                                   value="<?= htmlspecialchars($_POST['fullname'] ?? $user['fullname']) ?>" required>
                            <?php if (isset($errors['fullname'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['fullname']) ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" 
                                   id="phone" name="phone" 
                                   value="<?= htmlspecialchars($_POST['phone'] ?? $user['phone']) ?>" required>
                            <?php if (isset($errors['phone'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['phone']) ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">ที่อยู่ <span class="text-danger">*</span></label>
                            <textarea class="form-control <?= isset($errors['address']) ? 'is-invalid' : '' ?>" 
                                      id="address" name="address" rows="3" required><?= htmlspecialchars($_POST['address'] ?? $user['address']) ?></textarea>
                            <?php if (isset($errors['address'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['address']) ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="profile.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>ย้อนกลับ
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>บันทึกการเปลี่ยนแปลง
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
