<?php
require_once 'config/db.php';
require_once 'config/functions.php';

$pageTitle = "สมัครสมาชิก - ร้านขนมปั้นสิบยายนิดพัทลุง";

// ตรวจสอบว่าู้ใช้ล็อกอินอยู่แล้วหรือไม่
if(isLoggedIn()) {
    redirect(BASE_URL);
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
                            <h3 class="mb-0"><i class="fas fa-user-plus me-2"></i>สมัครสมาชิก</h3>
                        </div>
                        <div class="card-body p-4">
                            <?php 
                            // แสดงข้อผิดพลาด (ถ้ามี)
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

                            <form id="registerForm" action="<?php echo BASE_URL; ?>includes/auth/register-process.php" method="POST" novalidate>
                                <?php echo csrfField(); ?>
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="fullname" class="form-label">ชื่อ-สกุล</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                            <input type="text" class="form-control" id="fullname" name="fullname" 
                                                   value="<?php echo htmlspecialchars($_SESSION['old_input']['fullname'] ?? ''); ?>" required>
                                        </div>
                                        <div class="invalid-feedback">กรุณากรอกชื่อ-สกุล</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="username" class="form-label">ชื่อผู้ใช้</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-at"></i></span>
                                            <input type="text" class="form-control" id="username" name="username" 
                                                   value="<?php echo htmlspecialchars($_SESSION['old_input']['username'] ?? ''); ?>" required>
                                        </div>
                                        <div class="invalid-feedback">กรุณากรอกชื่อผู้ใช้</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="email" class="form-label">อีเมล</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="<?php echo htmlspecialchars($_SESSION['old_input']['email'] ?? ''); ?>" required>
                                        </div>
                                        <div class="invalid-feedback">กรุณากรอกอีเมลที่ถูกต้อง</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">เบอร์โทรศัพท์</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                            <input type="tel" class="form-control" id="phone" name="phone" 
                                                   value="<?php echo htmlspecialchars($_SESSION['old_input']['phone'] ?? ''); ?>" required>
                                        </div>
                                        <div class="invalid-feedback">กรุณากรอกเบอร์โทรศัพท์</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="password" class="form-label">รหัสผ่าน</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                            <input type="password" class="form-control" id="password" name="password" required>
                                            <button class="btn btn-outline-secondary toggle-password" type="button">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        <div class="invalid-feedback">กรุณากรอกรหัสผ่าน</div>
                                        <small class="form-text text-muted">รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร</small>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="confirm_password" class="form-label">ยืนยันรหัสผ่าน</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                            <button class="btn btn-outline-secondary toggle-password" type="button">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        <div class="invalid-feedback">กรุณายืนยันรหัสผ่าน</div>
                                    </div>

                                    <div class="col-12">
                                        <label for="address" class="form-label">ที่อยู่</label>
                                        <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($_SESSION['old_input']['address'] ?? ''); ?></textarea>
                                        <div class="invalid-feedback">กรุณากรอกที่อยู่</div>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="agree_terms" name="agree_terms" required>
                                            <label class="form-check-label" for="agree_terms">
                                                ฉันยอมรับ <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">ข้อกำหนดและเงื่อนไข</a>
                                            </label>
                                            <div class="invalid-feedback">กรุณายอมรับข้อกำหนดและเงื่อนไข</div>
                                        </div>
                                    </div>

                                    <div class="col-12 mt-3">
                                        <button type="submit" class="btn btn-success btn-lg w-100 py-2">
                                            <i class="fas fa-user-plus me-2"></i>สมัครสมาชิก
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="card-footer bg-light text-center py-3">
                            มีบัญชีอยู่แล้ว? <a href="<?php echo BASE_URL; ?>login.php" class="text-success fw-bold">เข้าสู่ระบบ</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal ข้อกำหนดและเงื่อนไข -->
        <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="termsModalLabel">ข้อกำหนดและเงื่อนไข</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h6>1. การยอมรับข้อกำหนด</h6>
                        <p>การใช้งานเว็บไซต์ร้านขนมปั้นสิบยายนิดพัทลุง หมายความว่า่านได้อ่าน ทำความเข้าใจ และยอมรับข้อกำหนดและเงื่อนไขเหล่านี้ทั้งหมด</p>
                        
                        <h6>2. บัญชีผู้ใช้</h6>
                        <p>ท่านต้องกรอกข้อมูลที่ถูกต้องครบถ้วนในการสมัครสมาิก และต้องรักษาข้อมูลบัญชีผู้ใช้เป็นความลับ</p>
                        
                        <h6>3. การสั่งซื้อสินค้า</h6>
                        <p>การสั่งซื้อสินค้าือว่า่านได้ตกลงซื้อสินค้าามราาที่ระบุในขณะสั่งซื้อ</p>
                        
                        <h6>4. การเปลี่ยนแปลงข้อกำหนด</h6>
                        <p>ทางร้านขอสงวนสิทธิ์ในการแก้ไขข้อกำหนดและเงื่อนไขนี้โดยไม่ต้องแจ้งให้ทราบล่วงหน้า</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" data-bs-dismiss="modal">เข้าใจแล้ว</button>
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
        const form = document.getElementById('registerForm');
        
        form.addEventListener('submit', function(event) {
            // ตรวจสอบรหัสผ่าน
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('รหัสผ่านไม่ตรงกัน');
            } else {
                confirmPassword.setCustomValidity('');
            }
            
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
