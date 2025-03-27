<?php
require_once '../config/db.php';
require_once '../config/functions.php';

$pageTitle = "ร้านขนมปั้นสิบยายนิดพัทลุง";

// ดึงสินค้าแนะนำ (Featured Products)
$stmt = $conn->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.featured = 1 AND p.status = 1 
    ORDER BY RAND()
    LIMIT 8
");
$stmt->execute();
$featuredProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ดึงหมวดหมู่ยอดนิยม
$stmt = $conn->prepare("
    SELECT c.*, COUNT(p.id) as product_count
    FROM categories c
    LEFT JOIN products p ON c.id = p.category_id AND p.status = 1
    WHERE c.status = 1
    GROUP BY c.id
    ORDER BY product_count DESC
    LIMIT 6
");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ดึงสินค้ายอดนิยม (Popular Products)
$stmt = $conn->prepare("
    SELECT p.*, c.name as category_name,
           (SELECT SUM(quantity) FROM order_items WHERE product_id = p.id) as sold
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.status = 1
    ORDER BY sold DESC
    LIMIT 4
");
$stmt->execute();
$popularProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ดึงสินค้ามาใหม่ (New Arrivals)
$stmt = $conn->prepare("
    SELECT p.*, c.name as category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.status = 1
    ORDER BY p.created_at DESC
    LIMIT 4
");
$stmt->execute();
$newProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/head.php';
include '../includes/navbar.php';
?>

<!-- Hero Section -->
<section class="hero-section position-relative">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 hero-content">
                <h1 class="display-4 fw-bold mb-4">ขนมปั้นสิบยายนิด</h1>
                <p class="lead mb-5">รักษ์คุณค่าทางวัฒนธรรม รสชาติดั่งเดิม แบบฉบับพัทลุง</p>
                <div class="d-flex gap-3">
                    <a href="products.php" class="btn btn-success btn-lg px-4">
                        <i class="fas fa-shopping-bag me-2"></i>ช้อปเลย
                    </a>
                    <a href="about.php" class="btn btn-outline-success btn-lg px-4">
                        <i class="fas fa-info-circle me-2"></i>เกี่ยวกับเรา
                    </a>
                </div>
            </div>
            <div class="col-md-6">
                <div class="hero-image">
                    <img src="<?= BASE_URL ?>assets/images/banner.jpg" alt="ขนมปั้นสิบยายนิด" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="section-header d-flex justify-content-between align-items-center mb-5">
            <h2 class="section-title">สินค้าแนะนำ</h2>
            <a href="products.php" class="btn btn-outline-success">
                ดูทั้งหมด <i class="fas fa-chevron-right ms-2"></i>
            </a>
        </div>
        
        <div class="row">
            <?php foreach($featuredProducts as $product): 
                $discountPercent = ($product['discount_price'] > 0) 
                    ? round(($product['price'] - $product['discount_price']) / $product['price'] * 100) 
                    : 0;
            ?>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card product-card h-100 border-0 shadow-sm">
                    <?php if($discountPercent > 0): ?>
                        <div class="badge bg-danger position-absolute top-0 end-0 m-2">-<?= $discountPercent ?>%</div>
                    <?php endif; ?>
                    
                    <a href="product-detail.php?id=<?= $product['id'] ?>">
                        <img src="<?= BASE_URL . ($product['image'] ?? 'assets/images/product1.jpg') ?>" 
                             class="card-img-top" 
                             alt="<?= $product['name'] ?>"
                             loading="lazy">
                    </a>
                    
                    <div class="card-body">
                        <span class="badge bg-light text-dark mb-2"><?= $product['category_name'] ?></span>
                        <h5 class="card-title">
                            <a href="product-detail.php?id=<?= $product['id'] ?>" class="text-decoration-none text-dark">
                                <?= $product['name'] ?>
                            </a>
                        </h5>
                        
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <?php if($product['discount_price'] > 0): ?>
                                    <span class="text-danger fw-bold">฿<?= number_format($product['discount_price']) ?></span>
                                    <span class="text-decoration-line-through text-muted small ms-2">฿<?= number_format($product['price']) ?></span>
                                <?php else: ?>
                                    <span class="text-success fw-bold">฿<?= number_format($product['price']) ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <button class="btn btn-sm btn-success add-to-cart" data-id="<?= $product['id'] ?>">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Categories -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="section-title">หมวดหมู่สินค้า</h2>
            <p class="text-muted">เลือกซื้อตามประเภทที่คุณสนใจ</p>
        </div>
        
        <div class="row">
            <?php foreach($categories as $category): ?>
            <div class="col-md-4 col-6 mb-4">
                <div class="card category-card border-0 shadow-sm h-100">
                    <a href="products.php?category=<?= $category['id'] ?>" class="text-decoration-none">
                        <img src="<?= BASE_URL . ($category['image'] ?? 'assets/images/product1.jpg') ?>" 
                             class="card-img-top" 
                             alt="<?= $category['name'] ?>">
                        <div class="card-body text-center">
                            <h5 class="card-title mb-0"><?= $category['name'] ?></h5>
                            <small class="text-muted">สินค้า <?= $category['product_count'] ?> รายการ</small>
                        </div>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Popular Products -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="section-header d-flex justify-content-between align-items-center mb-5">
            <h2 class="section-title">สินค้าขายดี</h2>
            <a href="products.php?sort=popular" class="btn btn-outline-success">
                ดูทั้งหมด <i class="fas fa-chevron-right ms-2"></i>
            </a>
        </div>
        
        <div class="row">
            <?php foreach($popularProducts as $product): ?>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card product-card h-100 border-0 shadow-sm">
                    <div class="badge bg-warning position-absolute top-0 start-0 m-2 text-dark">
                        ขายดี
                    </div>
                    
                    <a href="product-detail.php?id=<?= $product['id'] ?>">
                        <img src="<?= BASE_URL . ($product['image'] ?? 'assets/images/product1.jpg') ?>" 
                             class="card-img-top" 
                             alt="<?= $product['name'] ?>"
                             loading="lazy">
                    </a>
                    
                    <div class="card-body">
                        <h5 class="card-title">
                            <a href="product-detail.php?id=<?= $product['id'] ?>" class="text-decoration-none text-dark">
                                <?= $product['name'] ?>
                            </a>
                        </h5>
                        
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="text-success fw-bold">฿<?= number_format($product['price']) ?></span>
                            <button class="btn btn-sm btn-success add-to-cart" data-id="<?= $product['id'] ?>">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- New Arrivals -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="section-header d-flex justify-content-between align-items-center mb-5">
            <h2 class="section-title">สินค้ามาใหม่</h2>
            <a href="products.php?sort=newest" class="btn btn-outline-success">
                ดูทั้งหมด <i class="fas fa-chevron-right ms-2"></i>
            </a>
        </div>
        
        <div class="row">
            <?php foreach($newProducts as $product): ?>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card product-card h-100 border-0 shadow-sm">
                    <div class="badge bg-info position-absolute top-0 start-0 m-2">
                        ใหม่!
                    </div>
                    
                    <a href="product-detail.php?id=<?= $product['id'] ?>">
                        <img src="<?= BASE_URL . ($product['image'] ?? 'assets/images/product1.jpg') ?>" 
                             class="card-img-top" 
                             alt="<?= $product['name'] ?>"
                             loading="lazy">
                    </a>
                    
                    <div class="card-body">
                        <h5 class="card-title">
                            <a href="product-detail.php?id=<?= $product['id'] ?>" class="text-decoration-none text-dark">
                                <?= $product['name'] ?>
                            </a>
                        </h5>
                        
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="text-success fw-bold">฿<?= number_format($product['price']) ?></span>
                            <button class="btn btn-sm btn-success add-to-cart" data-id="<?= $product['id'] ?>">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <img src="<?= BASE_URL ?>assets/images/product1.jpg" alt="เกี่ยวกับยายนิด" class="img-fluid rounded shadow">
            </div>
            <div class="col-md-6">
                <h2 class="mb-4">เกี่ยวกับยายนิด</h2>
                <p class="lead">ขนมปั้นสิบสูตรดั้งเดิมจากยายนิด สูตรลับที่สืบทอดมากว่า 50 ปี</p>
                <p>เราคือร้านขนมพื้นบ้านที่นำเสนอขนมไทยโบราณสูตรดั้งเดิม ด้วยวัตถุดิบคุณภาพและการทำมือทุกขั้นตอน เพื่อรักษาคุณค่าของวัฒนธรรมการกินขนมไทยแบบดั้งเดิม</p>
                <a href="about.php" class="btn btn-success mt-3">อ่านเพิ่มเติม</a>
            </div>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    // ระบบเพิ่มสินค้าเข้าตะกร้า
    $(document).on('click', '.add-to-cart', function() {
        const productId = $(this).data('id');
        const button = $(this);
        
        // ปิดการคลิกชั่วคราวเพื่อป้องกันการคลิกซ้ำ
        button.prop('disabled', true);
        const originalHtml = button.html();
        button.html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: '<?= BASE_URL ?>includes/cart/add-to-cart.php',
            method: 'POST',
            data: { 
                product_id: productId, 
                quantity: 1,
                csrf_token: '<?= isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '' ?>'
            },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    // อัปเดตจำนวนสินค้าในตะกร้า
                    $('.cart-count').text(response.cart_count);
                    
                    // แสดงข้อความสำเร็จแบบโต้ตอบ
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    });
                    
                    Toast.fire({
                        icon: 'success',
                        title: 'เพิ่มสินค้าลงตะกร้าเรียบร้อยแล้ว'
                    });
                } else {
                    if (response.login_required) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'กรุณาเข้าสู่ระบบ',
                            text: 'คุณต้องเข้าสู่ระบบก่อนจึงจะสามารถเพิ่มสินค้าลงตะกร้าได้',
                            showCancelButton: true,
                            confirmButtonText: 'เข้าสู่ระบบ',
                            cancelButtonText: 'ปิด',
                            allowOutsideClick: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = '<?= BASE_URL ?>login.php?redirect=' + 
                                    encodeURIComponent(window.location.href);
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: response.message
                        });
                    }
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้'
                });
            },
            complete: function() {
                // คืนสถานะปุ่มเป็นปกติ
                button.prop('disabled', false);
                button.html(originalHtml);
            }
        });
    });
});
</script>

<style>
/* กำหนดโทนสี bronze */
:root {
    --primary-color: #cd7f32; /* สี bronze หลัก */
    --primary-hover: #b87333;
    --primary-light: #e6c8a0;
    --primary-dark: #8a5c2e;
    --secondary-color: #d4a76a; /* สี bronze อ่อน */
    --light-bg: #f8f1e8; /* สีพื้นหลังอ่อน */
}

/* Hero Section */
.hero-section {
    padding: 5rem 0;
    background: linear-gradient(135deg, rgba(205, 127, 50, 0.1) 0%, rgba(255, 255, 255, 1) 100%);
}

/* ปุ่ม */
.btn-success {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}
.btn-success:hover {
    background-color: var(--primary-hover);
    border-color: var(--primary-hover);
}
.btn-outline-success {
    color: var(--primary-color);
    border-color: var(--primary-color);
}
.btn-outline-success:hover {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

/* Section Header */
.section-header:after {
    background-color: var(--primary-color);
}

/* Product Card */
.product-card {
    transition: all 0.3s ease;
    border-radius: 10px;
    overflow: hidden;
    background-color: white;
}
.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(205, 127, 50, 0.1);
}

/* Badges */
.badge.bg-success {
    background-color: var(--primary-color) !important;
}
.badge.bg-warning {
    background-color: #ffc107 !important;
}
.badge.bg-danger {
    background-color: #dc3545 !important;
}
.badge.bg-info {
    background-color: #0dcaf0 !important;
}

/* Backgrounds */
.bg-white {
    background-color: white !important;
}
.bg-light {
    background-color: var(--light-bg) !important;
}

/* Text Colors */
.text-success {
    color: var(--primary-color) !important;
}
.text-danger {
    color: #dc3545 !important;
}

/* Navbar */
.navbar {
    background-color: white;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}
.navbar .navbar-nav .nav-link {
    color: #333;
}
.navbar .navbar-nav .nav-link:hover {
    color: var(--primary-color);
}
.navbar .navbar-brand {
    color: var(--primary-dark);
    font-weight: bold;
}

/* Footer */
.footer {
    background-color: var(--primary-dark);
    color: white;
}
.footer a {
    color: var(--primary-light);
}
.footer a:hover {
    color: white;
    text-decoration: none;
}

/* Animation และส่วนอื่นๆ เหมือนเดิม */
@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
    100% { transform: translateY(0px); }
}

/* Hero Section */
.hero-section {
    padding: 5rem 0;
    background: linear-gradient(135deg, rgba(25, 135, 84, 0.1) 0%, rgba(255, 255, 255, 1) 100%);
}

.hero-content {
    z-index: 2;
}

.hero-image {
    position: relative;
    z-index: 1;
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
    100% { transform: translateY(0px); }
}

/* Product Card */
.product-card {
    transition: all 0.3s ease;
    border-radius: 10px;
    overflow: hidden;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.product-card .card-img-top {
    transition: transform 0.3s ease;
    height: 200px;
    object-fit: cover;
}

.product-card:hover .card-img-top {
    transform: scale(1.05);
}

/* Category Card */
.category-card {
    transition: all 0.3s ease;
    border-radius: 10px;
    overflow: hidden;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.category-card .card-img-top {
    height: 150px;
    object-fit: cover;
}

/* Section Styling */
.section-header {
    position: relative;
    padding-bottom: 15px;
}

.section-header:after {
    content: '';
    position: absolute;
    left: 0;
    bottom: 0;
    width: 50px;
    height: 3px;
    background-color: #198754;
}

.section-title {
    position: relative;
    display: inline-block;
}
</style>

<?php
include '../includes/footer.php';
?>
