<nav class="navbar navbar-expand-lg navbar-bronze">
    <div class="container">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
            <img src="<?php echo BASE_URL; ?>assets/images/logo.png" alt="Logo" height="40">
            <span class="brand-text">ปั้นสิบยายนิด</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/frontend/index.php">หน้าแรก</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/frontend/products.php">สินค้าทั้งหมด</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/frontend/about.php">เกี่ยวกับเรา</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link cart-link" href="<?php echo BASE_URL; ?>/frontend/cart.php">
                        <i class="fas fa-shopping-cart"></i> ตะกร้า
                        <span class="badge bg-gold cart-count">0</span>
                    </a>
                </li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            <?php echo $_SESSION['user_name']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>frontend/profile.php"><i class="fas fa-user me-2"></i>บัญชีของฉัน</a></li>
                            <?php if ($user['role'] == 'admin'): ?>
                                <li>
                                    <a href="<?= BASE_URL ?>admin/dashboard.php" class="dropdown-item text-danger">
                                        <i class="fas fa-cog me-2"></i>แผงควบคุม
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>frontend/orders.php"><i class="fas fa-clipboard-list me-2"></i>คำสั่งซื้อของฉัน</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>ออกจากระบบ</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>login.php"><i class="fas fa-sign-in-alt me-1"></i>เข้าสู่ระบบ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link register-link" href="<?php echo BASE_URL; ?>register.php"><i class="fas fa-user-plus me-1"></i>สมัครสมาชิก</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>