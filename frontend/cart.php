<?php
require_once '../config/db.php';
require_once '../config/functions.php';

$pageTitle = "ตะกร้าสินค้า - ร้านขนมปั้นสิบยายนิดพัทลุง";

// ตรวจสอบการล็อกอิน
if (!isLoggedIn()) {
    $_SESSION['redirect_url'] = BASE_URL . 'cart.php';
    setAlert('warning', 'กรุณาเข้าสู่ระบบเพื่อดูตะกร้าสินค้า');
    redirect('login.php');
}

// ดึงข้อมูลสินค้าในตะกร้า
$stmt = $conn->prepare("
    SELECT c.id as cart_id, p.id, p.name, p.price, p.discount_price, p.image, c.quantity, p.stock, cat.name as category_name
    FROM cart c
    JOIN products p ON c.product_id = p.id
    JOIN categories cat ON p.category_id = cat.id
    WHERE c.user_id = ? AND p.status = 1
");
$stmt->execute([$_SESSION['user_id']]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// คำนวณราคารวม
$subtotal = 0;
$totalDiscount = 0;
$totalItems = 0;

foreach ($cartItems as $item) {
    $price = $item['discount_price'] > 0 ? $item['discount_price'] : $item['price'];
    $subtotal += $price * $item['quantity'];
    if ($item['discount_price'] > 0) {
        $totalDiscount += ($item['price'] - $item['discount_price']) * $item['quantity'];
    }
    $totalItems += $item['quantity'];
}

// ค่าจัดส่ง (ฟรีเมื่อสั่งซื้อครบ 500 บาท)
$shippingFee = $subtotal >= 500 ? 0 : 50;
$grandTotal = $subtotal + $shippingFee;

include '../includes/head.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-shopping-cart me-2"></i>ตะกร้าสินค้าของคุณ
                </h2>
                <span class="badge bg-success rounded-pill"><?= number_format($totalItems) ?> ชิ้น</span>
            </div>

            <?php if (empty($cartItems)): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-shopping-cart fa-4x text-muted mb-4"></i>
                        <h4 class="mb-3">ตะกร้าสินค้าว่างเปล่า</h4>
                        <p class="text-muted mb-4">คุณยังไม่มีสินค้าในตะกร้า เริ่มช้อปปิ้งเลย!</p>
                        <a href="products.php" class="btn btn-success px-4">
                            <i class="fas fa-store me-2"></i>เลือกซื้อสินค้า
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="100">สินค้า</th>
                                        <th>รายละเอียด</th>
                                        <th width="120">ราคา</th>
                                        <th width="150">จำนวน</th>
                                        <th width="120">รวม</th>
                                        <th width="50"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cartItems as $item): ?>
                                        <?php
                                        $price = $item['discount_price'] > 0 ? $item['discount_price'] : $item['price'];
                                        $total = $price * $item['quantity'];
                                        ?>
                                        <tr class="align-middle" data-id="<?= $item['id'] ?>">
                                            <td>
                                                <a href="product-detail.php?id=<?= $item['id'] ?>">
                                                    <img src="<?= asset($item['image'] ?? 'assets/images/product1.jpg') ?>" 
                                                         class="img-fluid rounded-2" 
                                                         alt="<?= htmlspecialchars($item['name']) ?>"
                                                         style="width: 80px; height: 80px; object-fit: cover;">
                                                </a>
                                            </td>
                                            <td>
                                                <a href="product-detail.php?id=<?= $item['id'] ?>" class="text-decoration-none text-dark">
                                                    <h6 class="mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                                                </a>
                                                <small class="text-muted d-block"><?= htmlspecialchars($item['category_name']) ?></small>
                                                <?php if ($item['discount_price'] > 0): ?>
                                                    <span class="badge bg-danger mt-1">ลด <?= number_format($item['price'] - $item['discount_price'], 2) ?> บาท</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($item['discount_price'] > 0): ?>
                                                    <span class="text-danger fw-bold">
                                                        <?= number_format($item['discount_price'], 2) ?>
                                                    </span>
                                                    <span class="text-decoration-line-through text-muted small d-block">
                                                        <?= number_format($item['price'], 2) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="fw-bold">
                                                        <?= number_format($item['price'], 2) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <button class="btn btn-outline-secondary minus-btn" 
                                                            type="button" 
                                                            data-id="<?= $item['id'] ?>"
                                                            <?= $item['quantity'] <= 1 ? 'disabled' : '' ?>>
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                    <input type="number" 
                                                           class="form-control text-center quantity-input" 
                                                           value="<?= $item['quantity'] ?>" 
                                                           min="1" 
                                                           max="<?= $item['stock'] ?>"
                                                           data-id="<?= $item['id'] ?>">
                                                    <button class="btn btn-outline-secondary plus-btn" 
                                                            type="button" 
                                                            data-id="<?= $item['id'] ?>"
                                                            <?= $item['quantity'] >= $item['stock'] ? 'disabled' : '' ?>>
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                                <?php if ($item['quantity'] > $item['stock']): ?>
                                                    <small class="text-danger">มีสินค้าในสต็อกเพียง <?= $item['stock'] ?> ชิ้น</small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="fw-bold">
                                                <?= number_format($total, 2) ?> บาท
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-danger remove-item" 
                                                        data-id="<?= $item['cart_id'] ?>">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mb-4">
                    <a href="products.php" class="btn btn-outline-success">
                        <i class="fas fa-arrow-left me-2"></i>ช้อปปิ้งต่อ
                    </a>
                    <button class="btn btn-outline-danger" id="clearCart">
                        <i class="fas fa-trash-alt me-2"></i>ล้างตะกร้า
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>สรุปรายการสั่งซื้อ</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>ราคาสินค้า (<?= number_format($totalItems) ?> ชิ้น)</span>
                            <span><?= number_format($subtotal, 2) ?> บาท</span>
                        </div>
                        <?php if ($totalDiscount > 0): ?>
                            <div class="d-flex justify-content-between mb-2 text-danger">
                                <span>ส่วนลด</span>
                                <span>-<?= number_format($totalDiscount, 2) ?> บาท</span>
                            </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span>ค่าจัดส่ง</span>
                            <span id="shippingCost">
                                <?= $shippingFee == 0 ? '<span class="text-success">ฟรี</span>' : number_format($shippingFee, 2).' บาท' ?>
                            </span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5">
                            <span>รวมทั้งสิ้น</span>
                            <span id="totalAmount"><?= number_format($grandTotal, 2) ?> บาท</span>
                        </div>
                    </div>

                    <?php if (!empty($cartItems)): ?>
                        <a href="checkout.php" class="btn btn-success w-100 py-2 mb-3">
                            <i class="fas fa-credit-card me-2"></i>ดำเนินการชำระเงิน
                        </a>
                        <div class="alert alert-info small mb-0">
                            <i class="fas fa-info-circle me-2"></i>ฟรีค่าจัดส่งเมื่อสั่งซื้อครบ 500 บาท
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // ระบบเพิ่ม/ลดจำนวนสินค้า
    $('.plus-btn').click(function() {
        const productId = $(this).data('id');
        const input = $(this).siblings('.quantity-input');
        const newQuantity = parseInt(input.val()) + 1;
        updateCartItem(productId, newQuantity);
    });

    $('.minus-btn').click(function() {
        const productId = $(this).data('id');
        const input = $(this).siblings('.quantity-input');
        const newQuantity = parseInt(input.val()) - 1;
        if (newQuantity >= 1) {
            updateCartItem(productId, newQuantity);
        }
    });

    // ระบบอัปเดตจำนวนสินค้าเมื่อเปลี่ยนค่าใน input
    $('.quantity-input').change(function() {
        const productId = $(this).data('id');
        const newQuantity = parseInt($(this).val());
        if (newQuantity >= 1) {
            updateCartItem(productId, newQuantity);
        } else {
            $(this).val(1);
        }
    });

    // ระบบลบสินค้า
    $('.remove-item').click(function() {
        const cartId = $(this).data('id');
        removeCartItem(cartId);
    });

    // ระบบล้างตะกร้า
    $('#clearCart').click(function() {
        Swal.fire({
            title: 'ล้างตะกร้าสินค้า',
            text: 'คุณแน่ใจว่าต้องการลบสินค้าทั้งหมดออกจากตะกร้า?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#dc3545',
            confirmButtonText: 'ล้างตะกร้า',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= BASE_URL ?>includes/cart/clear-cart.php',
                    method: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'ล้างตะกร้าเรียบร้อย',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'เกิดข้อผิดพลาด',
                                text: response.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้'
                        });
                    }
                });
            }
        });
    });

    // ฟังก์ชันอัปเดตสินค้าในตะกร้า
    function updateCartItem(productId, quantity) {
        $.ajax({
            url: '<?= BASE_URL ?>includes/cart/update-cart.php',
            method: 'POST',
            data: { 
                product_id: productId, 
                quantity: quantity 
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // อัปเดตจำนวนสินค้าในตะกร้า
                    $('.cart-count').text(response.cart_count);
                    // รีโหลดหน้าเพื่อแสดงผลลัพธ์ใหม่
                    location.reload();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: response.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้'
                });
            }
        });
    }

    // ฟังก์ชันลบสินค้าในตะกร้า
    function removeCartItem(cartId) {
        Swal.fire({
            title: 'ลบสินค้า',
            text: 'คุณแน่ใจว่าต้องการลบสินค้านี้ออกจากตะกร้า?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#dc3545',
            confirmButtonText: 'ลบสินค้า',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= BASE_URL ?>includes/cart/remove-from-cart.php',
                    method: 'POST',
                    data: { cart_id: cartId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // อัปเดตจำนวนสินค้าในตะกร้า
                            $('.cart-count').text(response.cart_count);
                            // ลบแถวสินค้าจากตาราง
                            $(`tr[data-id="${response.product_id}"]`).remove();
                            
                            Swal.fire({
                                icon: 'success',
                                title: 'ลบสินค้าเรียบร้อย',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                if (response.cart_count == 0) {
                                    location.reload();
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'เกิดข้อผิดพลาด',
                                text: response.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้'
                        });
                    }
                });
            }
        });
    }
});
</script>

<style>
.cart-container {
    min-height: calc(100vh - 150px);
}

.table th {
    white-space: nowrap;
}

.quantity-input {
    width: 50px;
    -moz-appearance: textfield;
}

.quantity-input::-webkit-outer-spin-button,
.quantity-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.sticky-top {
    z-index: 1;
}

@media (max-width: 992px) {
    .sticky-top {
        position: static !important;
    }
}
</style>

<?php
include '../includes/footer.php';
?>
