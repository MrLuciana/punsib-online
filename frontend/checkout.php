<?php
require_once '../config/db.php';
require_once '../config/functions.php';

$pageTitle = "ชำระเงิน - ร้านขนมปั้นสิบยายนิดพัทลุง";

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['redirect_url'] = BASE_URL . 'checkout.php';
    setAlert('warning', 'กรุณาเข้าสู่ระบบเพื่อดำเนินการชำระเงิน');
    redirect('login.php');
}

// Get cart items
$stmt = $conn->prepare("
    SELECT c.id as cart_id, p.id, p.name, p.price, p.discount_price, p.image, c.quantity, p.stock
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ? AND p.status = 1
");
$stmt->execute([$_SESSION['user_id']]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($cartItems)) {
    setAlert('warning', 'ตะกร้าสินค้าของคุณว่างเปล่า');
    redirect('cart.php');
}

// Calculate totals
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

// Shipping fee (free when order reaches 500 baht)
$shippingFee = $subtotal >= 500 ? 0 : 50;
$grandTotal = $subtotal + $shippingFee;

// Get user details
$userStmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$_SESSION['user_id']]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();

        // Prepare order data
        $orderNumber = 'ORD-' . time() . rand(100, 999);
        $paymentMethod = $_POST['payment_method'];
        $shippingAddress = $_POST['shipping_address'];
        $orderNote = $_POST['order_note'] ?? null;

        // Create order
        $orderStmt = $conn->prepare("
            INSERT INTO orders 
            (user_id, order_number, total_amount, payment_method, payment_status, order_status, shipping_address, billing_address, note) 
            VALUES (?, ?, ?, ?, 'pending', 'pending', ?, ?, ?)
        ");
        $orderStmt->execute([
            $_SESSION['user_id'],
            $orderNumber,
            $grandTotal,
            $paymentMethod,
            $shippingAddress,
            $shippingAddress,
            $orderNote
        ]);
        
        $orderId = $conn->lastInsertId();

        // Create order items
        $orderItemStmt = $conn->prepare("
            INSERT INTO order_items 
            (order_id, product_id, quantity, price, total_price) 
            VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($cartItems as $item) {
            $price = $item['discount_price'] > 0 ? $item['discount_price'] : $item['price'];
            $totalPrice = $price * $item['quantity'];
            
            $orderItemStmt->execute([
                $orderId,
                $item['id'],
                $item['quantity'],
                $price,
                $totalPrice
            ]);

            // Update product stock
            $updateStockStmt = $conn->prepare("UPDATE products SET stock = stock - ?, sold = COALESCE(sold, 0) + ? WHERE id = ?");
            $updateStockStmt->execute([$item['quantity'], $item['quantity'], $item['id']]);
        }

        // Clear cart
        $clearCartStmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $clearCartStmt->execute([$_SESSION['user_id']]);

        $conn->commit();

        // Redirect to order confirmation page
        $_SESSION['order_number'] = $orderNumber;
        redirect('order-confirmation.php');

    } catch (PDOException $e) {
        $conn->rollBack();
        error_log($e->getMessage());
        setAlert('danger', 'เกิดข้อผิดพลาดในการสร้างคำสั่งซื้อ: ' . $e->getMessage());
    }
}

include '../includes/head.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8">
            <div class="mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-credit-card me-2"></i>ข้อมูลการชำระเงิน
                </h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="cart.php">ตะกร้าสินค้า</a></li>
                        <li class="breadcrumb-item active" aria-current="page">ชำระเงิน</li>
                    </ol>
                </nav>
            </div>

            <form method="POST" id="checkoutForm">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-truck me-2"></i>ที่อยู่จัดส่ง</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="fullname" class="form-label">ชื่อ-นามสกุล</label>
                            <input type="text" class="form-control" id="fullname" value="<?= htmlspecialchars($user['fullname']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">เบอร์โทรศัพท์</label>
                            <input type="tel" class="form-control" id="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="shipping_address" class="form-label">ที่อยู่จัดส่ง</label>
                            <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" required><?= htmlspecialchars($user['address']) ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="order_note" class="form-label">หมายเหตุ (ถ้ามี)</label>
                            <textarea class="form-control" id="order_note" name="order_note" rows="2"></textarea>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>วิธีการชำระเงิน</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="bankTransfer" value="bank_transfer" checked>
                            <label class="form-check-label" for="bankTransfer">
                                <i class="fas fa-university me-2"></i>โอนเงินผ่านธนาคาร
                            </label>
                            <div class="mt-2 ps-4 text-muted small">
                                <p class="mb-1">บัญชีธนาคารไทยพาณิชย์</p>
                                <p class="mb-1">ชื่อบัญชี: ร้านขนมปั้นสิบยายนิด</p>
                                <p class="mb-1">เลขที่บัญชี: 123-4-56789-0</p>
                            </div>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="qrCode" value="qr_code">
                            <label class="form-check-label" for="qrCode">
                                <i class="fas fa-qrcode me-2"></i>ชำระผ่าน QR Code
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cash" disabled>
                            <label class="form-check-label" for="cod">
                                <i class="fas fa-money-bill-alt me-2"></i>ชำระเงินปลายทาง (ไม่รองรับ)
                            </label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mb-4">
                    <a href="cart.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>กลับไปตะกร้าสินค้า
                    </a>
                    <button type="submit" class="btn btn-success px-4" id="submitOrder">
                        <i class="fas fa-check-circle me-2"></i>ยืนยันคำสั่งซื้อ
                    </button>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>สรุปรายการสั่งซื้อ</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="mb-3">สินค้าที่สั่งซื้อ (<?= number_format($totalItems) ?> ชิ้น)</h6>
                        
                        <div class="border-bottom pb-3 mb-3">
                            <?php foreach ($cartItems as $item): ?>
                                <?php
                                $price = $item['discount_price'] > 0 ? $item['discount_price'] : $item['price'];
                                $total = $price * $item['quantity'];
                                ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <div>
                                        <span class="d-block"><?= htmlspecialchars($item['name']) ?></span>
                                        <small class="text-muted"><?= number_format($item['quantity']) ?> x <?= number_format($price, 2) ?> บาท</small>
                                    </div>
                                    <span><?= number_format($total, 2) ?> บาท</span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span>ราคาสินค้า</span>
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
                            <span><?= $shippingFee == 0 ? '<span class="text-success">ฟรี</span>' : number_format($shippingFee, 2).' บาท' ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5">
                            <span>รวมทั้งสิ้น</span>
                            <span><?= number_format($grandTotal, 2) ?> บาท</span>
                        </div>
                    </div>

                    <div class="alert alert-info small mb-0">
                        <i class="fas fa-info-circle me-2"></i>ฟรีค่าจัดส่งเมื่อสั่งซื้อครบ 500 บาท
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Form submission handling
    $('#checkoutForm').submit(function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'ยืนยันคำสั่งซื้อ',
            text: 'คุณแน่ใจว่าต้องการยืนยันคำสั่งซื้อนี้?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#dc3545',
            confirmButtonText: 'ยืนยัน',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                // Disable submit button to prevent double submission
                $('#submitOrder').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>กำลังดำเนินการ...');
                
                // Submit the form
                this.submit();
            }
        });
    });
});
</script>

<style>
.sticky-top {
    z-index: 1;
}

@media (max-width: 992px) {
    .sticky-top {
        position: static !important;
    }
}

.form-check-label {
    cursor: pointer;
}

#checkoutForm input[type="radio"]:disabled + label {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>

<?php
include '../includes/footer.php';
?>
