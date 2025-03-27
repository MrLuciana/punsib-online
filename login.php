<?php
require_once 'config/db.php';
require_once 'config/functions.php';

$pageTitle = "เข้าสู่ระบบ - ร้านขนมปั้นสิบยายนิดพัทลุง";

// ตรวจสอบว่าู้ใช้ล็อกอินอยู่แล้วหรือไม่
if(isLoggedIn()) {
    redirect(BASE_URL . (isAdmin() ? 'admin/dashboard.php' : ''));
}

// เก็บ URL ที่จะกลับไปหลังล็อกอินสำเร็จ
if(!isset($_SESSION['redirect_url']) && isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], BASE_URL) === 0) {
    $_SESSION['redirect_url'] = $_SERVER['HTTP_REFERER'];
}

include 'includes/head.php';
?>

<body class="bg-light">
    <div class="auth-container">
        <!-- Navbar -->
        <?php include 'includes/navbar.php'; ?>

        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-success text-white text-center py-3">
                            <h3 class="mb-0"><i class="fas fa-sign-in-alt me-2"></i>เข้าสู่ระบบ</h3>
                        </div>
                        <div class="card-body p-4">
                            <?php 
                            // แสดงข้อผิดพลาดจากการลงทะเบียน (ถ้ามี)
                            if(isset($_SESSION['register_errors'])): ?>
                                <div class="alert alert-danger">
                                    <h5 class="alert-heading">กรุณาตรวจสอบข้อมูลต่อไปนี้:</h5>
                                    <ul class="mb-0">
                                        <?php foreach($_SESSION['register_errors'] as $error): ?>
                                            <li><?php echo htmlspecialchars($error); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <?php unset($_SESSION['register_errors']); ?>
                            <?php endif; ?>

                            <?php 
                            // แสดงข้อความแจ้งเตือน
                            displayAlert(); 
                            
                            // แสดงข้อความสำเร็จจากการลงทะเบียน
                            if(isset($_SESSION['register_success'])): ?>
                                <div class="alert alert-success">
                                    <?php echo htmlspecialchars($_SESSION['register_success']); ?>
                                </div>
                                <?php unset($_SESSION['register_success']); ?>
                            <?php endif; ?>

                            <form id="loginForm" action="<?php echo BASE_URL; ?>includes/auth/login-process.php" method="POST" novalidate>
                                <?php echo csrfField(); ?>
                                
                                <div class="mb-3">
                                    <label for="username" class="form-label">อีเมลหรือชื่อผู้ใช้</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" id="username" name="username" 
                                               value="<?php echo htmlspecialchars($_SESSION['old_input']['username'] ?? ''); ?>" 
                                               required autofocus>
                                    </div>
                                    <div class="invalid-feedback">กรุณากรอกอีเมลหรือชื่อผู้ใช้</div>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">รหัสผ่าน</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                        <button class="btn btn-outline-secondary toggle-password" type="button">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback">กรุณากรอกรหัสผ่าน</div>
                                </div>

                                <div class="mb-3 d-flex justify-content-between align-items-center">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                        <label class="form-check-label" for="remember">จดจำฉัน</label>
                                    </div>
                                    <a href="<?php echo BASE_URL; ?>forgot-password.php" class="text-decoration-none">ลืมรหัสผ่าน?</a>
                                </div>

                                <button type="submit" class="btn btn-success btn-lg w-100 py-2 mb-3">
                                    <i class="fas fa-sign-in-alt me-2"></i>เข้าสู่ระบบ
                                </button>

                                <div class="text-center mb-3">
                                    <span class="d-inline-block bg-light px-2">หรือ</span>
                                </div>

                                <div class="row g-2">
                                    <div class="col-6">
                                        <a href="#" class="btn btn-outline-primary w-100">
                                            <i class="fab fa-facebook-f me-2"></i>Facebook
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="#" class="btn btn-outline-danger w-100">
                                            <i class="fab fa-google me-2"></i>Google
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="card-footer bg-light text-center py-3">
                            ยังไม่มีบัญชี? <a href="<?php echo BASE_URL; ?>register.php" class="text-success fw-bold">สมัครสมาชิก</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <?php include 'includes/footer.php'; ?>
    </div>

    <script>
    // ฟังก์ชันสำหรับแสดง/ซ่อนรหัสผ่าน
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
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
        const form = document.getElementById('loginForm');
        
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
<?php unset($_SESSION['old_input']); ?>
