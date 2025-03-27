<?php
require_once '../config/db.php';
require_once '../config/functions.php';

// ตรวจสอบว่ามีการส่ง ID สินค้ามาหรือไม่
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setAlert('danger', 'ไม่พบสินค้าที่คุณต้องการ');
    redirect('products.php');
}

$product_id = (int)$_GET['id'];

// ดึงข้อมูลสินค้า
$stmt = $conn->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.id = ? AND p.status = 1
");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// ตรวจสอบว่าพบสินค้าหรือไม่
if (!$product) {
    setAlert('danger', 'ไม่พบสินค้าที่คุณต้องการ');
    redirect('products.php');
}

// ดึงสินค้าแนะนำในหมวดหมู่เดียวกัน
$stmt = $conn->prepare("
    SELECT p.* 
    FROM products p 
    WHERE p.category_id = ? AND p.id != ? AND p.status = 1 
    ORDER BY RAND() 
    LIMIT 4
");
$stmt->execute([$product['category_id'], $product['id']]);
$relatedProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// เพิ่มยอดวิวสินค้า
$conn->prepare("UPDATE products SET views = views + 1 WHERE id = ?")->execute([$product_id]);

$pageTitle = $product['name'] . " - ร้านขนมปั้นสิบยายนิดพัทลุง";

include '../includes/head.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">หน้าแรก</a></li>
            <li class="breadcrumb-item"><a href="products.php">สินค้าทั้งหมด</a></li>
            <li class="breadcrumb-item"><a href="products.php?category=<?= $product['category_id'] ?>"><?= htmlspecialchars($product['category_name']) ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($product['name']) ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <!-- ภาพหลักสินค้า -->
                    <div class="product-main-image mb-3">
                        <img src="<?= asset($product['image'] ?? 'assets/images/no-image.jpg') ?>" 
                             class="img-fluid rounded-3" 
                             alt="<?= htmlspecialchars($product['name']) ?>"
                             id="mainProductImage">
                    </div>
                    
                    <!-- ภาพย่อย (ถ้ามี) -->
                    <div class="row g-2">
                        <div class="col-3">
                            <div class="ratio ratio-1x1">
                                <img src="<?= asset($product['image'] ?? 'assets/images/no-image.jpg') ?>" 
                                     class="img-fluid rounded-2 cursor-pointer"
                                     onclick="changeMainImage(this)"
                                     style="object-fit: cover;">
                            </div>
                        </div>
                        <!-- สามารถเพิ่มภาพย่อยเพิ่มเติมได้ที่นี่ -->
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h1 class="h2 mb-3"><?= htmlspecialchars($product['name']) ?></h1>
                    
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-success me-2"><?= htmlspecialchars($product['category_name']) ?></span>
                        <div class="text-muted small">
                            <i class="fas fa-eye me-1"></i> <?= number_format($product['views'] ?? 0) ?> วิว
                            <i class="fas fa-shopping-bag ms-3 me-1"></i> <?= number_format($product['sold'] ?? 0) ?> ขายแล้ว
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <?php if ($product['discount_price'] > 0): ?>
                            <div class="d-flex align-items-center">
                                <span class="text-danger fw-bold fs-3 me-3">
                                    <?= number_format($product['discount_price'], 2) ?> บาท
                                </span>
                                <span class="text-decoration-line-through text-muted">
                                    <?= number_format($product['price'], 2) ?> บาท
                                </span>
                                <span class="badge bg-danger ms-2">
                                    ลด <?= number_format($product['price'] - $product['discount_price'], 2) ?> บาท
                                </span>
                            </div>
                        <?php else: ?>
                            <span class="text-success fw-bold fs-3">
                                <?= number_format($product['price'], 2) ?> บาท
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <span class="me-2">สถานะ:</span>
                            <?php if ($product['stock'] > 0): ?>
                                <span class="text-success fw-bold">
                                    <i class="fas fa-check-circle"></i> มีสินค้า
                                </span>
                            <?php else: ?>
                                <span class="text-danger fw-bold">
                                    <i class="fas fa-times-circle"></i> สินค้าหมด
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-flex align-items-center">
                            <span class="me-2">จำนวน:</span>
                            <div class="input-group" style="width: 120px;">
                                <button class="btn btn-outline-secondary btn-sm minus-btn" type="button">-</button>
                                <input type="number" class="form-control form-control-sm text-center quantity-input" 
                                       value="1" min="1" max="<?= $product['stock'] ?>">
                                <button class="btn btn-outline-secondary btn-sm plus-btn" type="button">+</button>
                            </div>
                            <span class="ms-2 text-muted small">
                                <?= number_format($product['stock']) ?> ชิ้นในสต็อก
                            </span>
                        </div>
                    </div>
                    
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <button class="btn btn-success btn-lg flex-grow-1 add-to-cart-btn" 
                                data-id="<?= $product['id'] ?>"
                                <?= ($product['stock'] <= 0) ? 'disabled' : '' ?>>
                            <i class="fas fa-cart-plus me-2"></i>เพิ่มในตะกร้า
                        </button>
                        
                        <button class="btn btn-outline-success btn-lg flex-grow-1 buy-now-btn"
                                data-id="<?= $product['id'] ?>"
                                <?= ($product['stock'] <= 0) ? 'disabled' : '' ?>>
                            <i class="fas fa-bolt me-2"></i>ซื้อทันที
                        </button>
                    </div>
                    
                    <div class="accordion mb-4" id="productAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#descriptionCollapse">
                                    รายละเอียดสินค้า
                                </button>
                            </h2>
                            <div id="descriptionCollapse" class="accordion-collapse collapse show" data-bs-parent="#productAccordion">
                                <div class="accordion-body">
                                    <?= nl2br(htmlspecialchars($product['description'])) ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#deliveryCollapse">
                                    ข้อมูลการจัดส่ง
                                </button>
                            </h2>
                            <div id="deliveryCollapse" class="accordion-collapse collapse" data-bs-parent="#productAccordion">
                                <div class="accordion-body">
                                    <ul>
                                        <li>จัดส่งทั่วประเทศผ่าน Kerry Express และ Flash Express</li>
                                        <li>เวลาจัดส่ง 1-3 วันทำการ</li>
                                        <li>ค่าจัดส่งเริ่มต้น 30 บาท (ฟรีเมื่อซื้อครบ 500 บาท)</li>
                                        <li>รับสินค้าที่ร้าน: 123 ถนนเทศบาล อำเภอเมือง พัทลุง</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-secondary btn-sm">
                            <i class="far fa-heart me-1"></i> เพิ่มรายการโปรด
                        </button>
                        <button class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-share-alt me-1"></i> แชร์
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- สินค้าแนะนำ -->
    <?php if (!empty($relatedProducts)): ?>
        <div class="mt-5 pt-4">
            <h3 class="mb-4">สินค้าแนะนำในหมวดหมู่เดียวกัน</h3>
            
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                <?php foreach ($relatedProducts as $related): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm product-card">
                            <?php if ($related['discount_price'] > 0): ?>
                                <span class="badge bg-danger position-absolute top-0 end-0 m-2">
                                    ลด <?= number_format($related['price'] - $related['discount_price'], 2) ?> บาท
                                </span>
                            <?php endif; ?>
                            
                            <a href="product-detail.php?id=<?= $related['id'] ?>">
                                <img src="<?= asset($related['image'] ?? 'assets/images/no-image.jpg') ?>" 
                                     class="card-img-top" 
                                     alt="<?= htmlspecialchars($related['name']) ?>">
                            </a>
                            
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a href="product-detail.php?id=<?= $related['id'] ?>" class="text-decoration-none text-dark">
                                        <?= htmlspecialchars($related['name']) ?>
                                    </a>
                                </h5>
                                
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div>
                                        <?php if ($related['discount_price'] > 0): ?>
                                            <span class="text-danger fw-bold">
                                                <?= number_format($related['discount_price'], 2) ?> บาท
                                            </span>
                                            <span class="text-decoration-line-through text-muted small ms-2">
                                                <?= number_format($related['price'], 2) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-success fw-bold">
                                                <?= number_format($related['price'], 2) ?> บาท
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// ฟังก์ชันเปลี่ยนภาพหลัก
function changeMainImage(img) {
    document.getElementById('mainProductImage').src = img.src;
}

// ระบบเพิ่ม/ลดจำนวนสินค้า
document.querySelectorAll('.plus-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const input = this.parentElement.querySelector('.quantity-input');
        const max = parseInt(input.max);
        if (input.value < max) {
            input.value = parseInt(input.value) + 1;
        }
    });
});

document.querySelectorAll('.minus-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const input = this.parentElement.querySelector('.quantity-input');
        if (input.value > 1) {
            input.value = parseInt(input.value) - 1;
        }
    });
});

// ระบบเพิ่มสินค้าเข้าตะกร้า
document.querySelector('.add-to-cart-btn').addEventListener('click', function() {
    const productId = this.dataset.id;
    const quantity = document.querySelector('.quantity-input').value;
    
    // ปิดการคลิกชั่วคราวเพื่อป้องกันการคลิกซ้ำ
    this.disabled = true;
    const originalText = this.innerHTML;
    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> กำลังดำเนินการ...';
    
    addToCart(productId, quantity, false, this, originalText);
});

// ระบบซื้อทันที
document.querySelector('.buy-now-btn').addEventListener('click', function() {
    const productId = this.dataset.id;
    const quantity = document.querySelector('.quantity-input').value;
    
    // ปิดการคลิกชั่วคราวเพื่อป้องกันการคลิกซ้ำ
    this.disabled = true;
    const originalText = this.innerHTML;
    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> กำลังดำเนินการ...';
    
    addToCart(productId, quantity, true, this, originalText);
});

// ฟังก์ชันเพิ่มสินค้า
function addToCart(productId, quantity, redirectToCheckout, button = null, originalText = null) {
    if (<?= isLoggedIn() ? 'true' : 'false' ?>) {
        $.ajax({
            url: '<?= BASE_URL ?>includes/cart/add-to-cart.php',
            method: 'POST',
            data: { 
                product_id: productId, 
                quantity: quantity,
                csrf_token: '<?= generateCsrfToken() ?>'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // อัปเดตจำนวนสินค้าในตะกร้า
                    $('.cart-count').text(response.cart_count);
                    
                    // แสดงการแจ้งเตือนแบบ Toast
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
                        title: redirectToCheckout ? 'เตรียมสั่งซื้อ' : 'เพิ่มสินค้าลงตะกร้าเรียบร้อย'
                    }).then(() => {
                        if (redirectToCheckout) {
                            window.location.href = '<?= BASE_URL ?>checkout.php';
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: response.message
                    });
                    
                    if (response.login_required) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'กรุณาเข้าสู่ระบบ',
                            text: 'คุณต้องเข้าสู่ระบบก่อนจึงจะสามารถสั่งซื้อสินค้าได้',
                            showCancelButton: true,
                            confirmButtonText: 'เข้าสู่ระบบ',
                            cancelButtonText: 'ปิด'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = '<?= BASE_URL ?>login.php?redirect=' + encodeURIComponent(window.location.href);
                            }
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
                if (button && originalText) {
                    button.disabled = false;
                    button.innerHTML = originalText;
                }
            }
        });
    } else {
        Swal.fire({
            icon: 'warning',
            title: 'กรุณาเข้าสู่ระบบ',
            text: 'คุณต้องเข้าสู่ระบบก่อนจึงจะสามารถสั่งซื้อสินค้าได้',
            showCancelButton: true,
            confirmButtonText: 'เข้าสู่ระบบ',
            cancelButtonText: 'ปิด'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '<?= BASE_URL ?>login.php?redirect=' + encodeURIComponent(window.location.href);
            }
        });
    }
}

// ป้องกันการคลิกขวาที่ปุ่ม
document.querySelectorAll('.add-to-cart-btn, .buy-now-btn').forEach(btn => {
    btn.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });
});

// ป้องกันการดับเบิลคลิก
document.querySelectorAll('.add-to-cart-btn, .buy-now-btn').forEach(btn => {
    btn.addEventListener('dblclick', function(e) {
        e.preventDefault();
    });
});
</script>


<style>
/* สไตล์เฉพาะสำหรับหน้ารายละเอียดสินค้า */
/* รูปภาพสินค้าหลัก */
.product-main-image {
    border: 1px solid #e6d5c3; /* สี bronze อ่อน */
    border-radius: 10px;
    overflow: hidden;
    background-color: #f8f1e8; /* พื้นหลังสี bronze อ่อน */
    transition: all 0.3s ease;
}

.product-main-image:hover {
    box-shadow: 0 5px 15px rgba(205, 127, 50, 0.1); /* เงาสี bronze */
}

/* ตัวชี้เมาส์ */
.cursor-pointer {
    cursor: pointer;
}

/* Accordion */
.accordion-button:not(.collapsed) {
    background-color: rgba(205, 127, 50, 0.1); /* สี bronze หลักแบบโปร่งใส */
    color: #5c3a21; /* สี bronze เข้ม */
    font-weight: 500;
}

.accordion-button:focus {
    box-shadow: 0 0 0 0.25rem rgba(205, 127, 50, 0.25); /* เงาสี bronze */
}

/* ตัวเลือกจำนวน */
.quantity-input {
    -moz-appearance: textfield;
    border: 1px solid #e6d5c3; /* สี bronze อ่อน */
    text-align: center;
    padding: 0.375rem 0.75rem;
    border-radius: 5px;
}

.quantity-input:focus {
    border-color: #cd7f32; /* สี bronze หลัก */
    box-shadow: 0 0 0 0.25rem rgba(205, 127, 50, 0.25);
}

.quantity-input::-webkit-outer-spin-button,
.quantity-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* ปุ่มซื้อทันที */
.buy-now-btn {
    background-color: #5c3a21; /* สี bronze เข้ม */
    color: white;
    border: none;
    transition: all 0.3s ease;
}

.buy-now-btn:hover {
    background-color: #3d2615; /* สี bronze เข้มกว่า */
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(92, 58, 33, 0.2);
}

/* ปุ่มเพิ่ม/ลดจำนวน */
.quantity-btn {
    background-color: #f1e6d6; /* สี bronze อ่อน */
    color: #5c3a21; /* สี bronze เข้ม */
    border: 1px solid #e6d5c3;
    width: 2.5rem;
    transition: all 0.2s ease;
}

.quantity-btn:hover {
    background-color: #e6d5c3;
    color: #3d2615;
}

.quantity-btn:focus {
    box-shadow: 0 0 0 0.25rem rgba(205, 127, 50, 0.25);
}

</style>

<?php
include '../includes/footer.php';
?>
