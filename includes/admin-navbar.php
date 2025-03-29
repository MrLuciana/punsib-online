<?php
// ตรวจสอบการล็อกอินและสิทธิ์
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-gradient-primary shadow-sm py-2">
    <div class="container-fluid">
        <!-- Brand & Toggler -->
        <div class="d-flex align-items-center">
            <!-- Sidebar Toggle Button -->
            <button id="sidebarToggle" class="btn btn-link text-white px-3">
                <i class="fas fa-bars fa-lg"></i>
            </button>


            <!-- ปุ่มกลับไปหน้าร้าน (เพิ่มส่วนนี้) -->
            <a href="<?= BASE_URL ?>" class="btn btn-outline-light btn-sm mx-2 d-none d-md-inline-flex align-items-center" target="_blank">
                <i class="fas fa-store me-1"></i>
                กลับไปหน้าร้าน
            </a>
        </div>
        <!-- Brand Logo -->
        <a class="navbar-brand d-flex align-items-center mx-2" href="<?= BASE_URL ?>admin/dashboard.php">
            <!-- <img src="<?= BASE_URL ?>assets/images/logo-white.png" height="32" alt="Admin Logo" class="mr-2"> -->
            <span class="font-weight-bold">ระบบหลังบ้าน</span>
        </a>
        <!-- Right Navigation Items -->
        <ul class="navbar-nav ml-auto">
            <!-- Notifications Dropdown -->
            <li class="nav-item dropdown mx-1">
                <a class="nav-link position-relative" href="#" id="notificationsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-bell fa-lg"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                        3
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in border-0" aria-labelledby="notificationsDropdown" style="width: 320px;">
                    <h6 class="dropdown-header bg-primary text-white rounded-top">
                        <i class="fas fa-bell me-2"></i>การแจ้งเตือน
                    </h6>
                    <a class="dropdown-item d-flex align-items-center py-2" href="#">
                        <div class="me-3">
                            <div class="icon-circle bg-warning">
                                <i class="fas fa-exclamation-triangle text-white"></i>
                            </div>
                        </div>
                        <div>
                            <div class="small text-muted">วันนี้, 10:45 น.</div>
                            <span class="fw-bold">มีออเดอร์ใหม่ #12345 รอการตรวจสอบ</span>
                        </div>
                    </a>
                    <div class="dropdown-divider my-0"></div>
                    <a class="dropdown-item text-center small py-2" href="#">
                        ดูการแจ้งเตือนทั้งหมด <i class="fas fa-chevron-right ms-1"></i>
                    </a>
                </div>
            </li>

            <!-- Messages Dropdown -->
            <li class="nav-item dropdown mx-1">
                <a class="nav-link position-relative" href="#" id="messagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-envelope fa-lg"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                        2
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in border-0" aria-labelledby="messagesDropdown" style="width: 320px;">
                    <h6 class="dropdown-header bg-primary text-white rounded-top">
                        <i class="fas fa-envelope me-2"></i>ข้อความ
                    </h6>
                    <a class="dropdown-item d-flex align-items-center py-2" href="#">
                        <div class="me-3">
                            <img class="rounded-circle" src="<?= BASE_URL ?>assets/images/user-avatar.jpg" alt="User" width="40" height="40">
                        </div>
                        <div>
                            <div class="text-truncate fw-bold">ปัญหาการชำระเงินจากลูกค้า</div>
                            <div class="small text-muted">ลูกค้า: สมชาย · 1 ชม.ที่แล้ว</div>
                        </div>
                    </a>
                    <div class="dropdown-divider my-0"></div>
                    <a class="dropdown-item text-center small py-2" href="#">
                        อ่านข้อความทั้งหมด <i class="fas fa-chevron-right ms-1"></i>
                    </a>
                </div>
            </li>

            <!-- User Dropdown -->
            <li class="nav-item dropdown ms-2">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <div class="d-none d-lg-inline me-2 text-end">
                        <div class="fw-bold"><?= htmlspecialchars($_SESSION['user_name']) ?></div>
                        <div class="small text-white-50">ผู้ดูแลระบบ</div>
                    </div>
                    <img class="img-profile rounded-circle border border-white" src="<?= BASE_URL ?>assets/images/admin-avatar.jpg" width="40" height="40">
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in border-0" aria-labelledby="userDropdown">
                    <a class="dropdown-item d-flex align-items-center py-2" href="profile.php">
                        <i class="fas fa-user fa-fw me-2 text-primary"></i>
                        โปรไฟล์
                    </a>
                    <!-- <a class="dropdown-item d-flex align-items-center py-2" href="settings.php">
                        <i class="fas fa-cogs fa-fw me-2 text-primary"></i>
                        ตั้งค่าระบบ
                    </a> -->
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item d-flex align-items-center py-2" href="<?= BASE_URL ?>" target="_blank">
                        <i class="fas fa-store fa-fw me-2 text-primary"></i>
                        ไปที่หน้าร้าน
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item d-flex align-items-center py-2" href="<?= BASE_URL ?>logout.php" data-toggle="modal" data-target="#logoutModal">
                        <i class="fas fa-sign-out-alt fa-fw me-2 text-primary"></i>
                        ออกจากระบบ
                    </a>
                </div>
            </li>
        </ul>
    </div>
</nav>

<!-- Logout Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="logoutModalLabel">ยืนยันการออกจากระบบ</h5>
                <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-sign-out-alt fa-3x text-primary me-3"></i>
                    <p class="mb-0">คุณแน่ใจต้องการออกจากระบบหรือไม่?</p>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-dismiss="modal">ยกเลิก</button>
                <a href="../logout.php" class="btn btn-primary rounded-pill px-4">ออกจากระบบ</a>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>assets/js/admin-navbar.js"></script>