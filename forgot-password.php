<?php
require_once 'config/db.php';
require_once 'config/functions.php';

$pageTitle = "ลืมรหัสผ่าน - ร้านขนมปั้นสิบยายนิดพัทลุง";

// ตรวจสอบว่าผู้ใช้ล็อกอินอยู่แล้วหรือไม่
if (isLoggedIn()) {
    redirect(BASE_URL . (isAdmin() ? 'admin/dashboard.php' : ''));
}

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
                            <h3 class="mb-0"><i class="fas fa-key me-2"></i>ลืมรหัสผ่าน</h3>
                        </div>
                        <div class="card-body p-4">
                            <?php
                            // แสดงข้อความแจ้งเตือนต่างๆ (เช่น ส่งอีเมลสำเร็จ, ไม่พบอีเมลในระบบ)
                            displayAlert();
                            ?>

                            <p class="text-muted text-center mb-4">
                                กรุณากรอกอีเมลที่ท่านใช้สมัครสมาชิก ระบบจะส่งลิงก์สำหรับตั้งรหัสผ่านใหม่ไปให้ทางอีเมลของท่าน
                            </p>

                            <form id="forgotPasswordForm" action="<?php echo BASE_URL; ?>includes/auth/forgot-password-process.php" method="POST" novalidate autocomplete="off">
                                <?php echo csrfField(); ?>

                                <div class="mb-3">
                                    <label for="email" class="form-label">อีเมล</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" class="form-control" id="email" name="email"
                                            autocomplete="email"
                                            value="<?php echo htmlspecialchars($_SESSION['old_input']['email'] ?? ''); ?>"
                                            required autofocus>

                                    </div>
                                    <div class="invalid-feedback">กรุณากรอกอีเมลให้ถูกต้อง</div>
                                </div>

                                <button type="submit" class="btn btn-success btn-lg w-100 py-2 mt-4" id="submitBtn">
                                    <i class="fas fa-paper-plane me-2"></i>ส่งลิงก์ตั้งรหัสผ่านใหม่
                                </button>

                            </form>
                        </div>
                        <div class="card-footer bg-light text-center py-3">
                            จำรหัสผ่านได้แล้ว? <a href="<?php echo BASE_URL; ?>login.php" class="text-success fw-bold">กลับไปเข้าสู่ระบบ</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include 'includes/footer.php'; ?>
    </div>

    <script>
        // ฟังก์ชันสำหรับตรวจสอบฟอร์มก่อนส่ง (Bootstrap 5 validation)
        (function() {
            'use strict';
            const form = document.getElementById('forgotPasswordForm');

            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }

                form.classList.add('was-validated');
            }, false);
        })();

        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            } else {
                // ป้องกันผู้ใช้กดซ้ำ
                const submitBtn = document.getElementById('submitBtn');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> กำลังส่ง...';
            }
            form.classList.add('was-validated');
        }, false);
    </script>
</body>

</html>
<?php unset($_SESSION['old_input']); ?>