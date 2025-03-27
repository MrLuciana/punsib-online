<?php
require_once '../config/db.php';
require_once '../config/functions.php';

// ตรวจสอบการล็อกอิน
if (!isLoggedIn()) {
    setAlert('danger', 'กรุณาเข้าู่ระบบก่อนทำการสั่งซื้อ');
    redirect('login.php?redirect=checkout');
}

// ดึงข้อมูลตะกร้าินค้า
$stmt = $conn->prepare("
    SELECT p.id, p.name, p.price, p.discount_price, p.image, c.quantity 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ? AND p.status = 1
");
$stmt->execute([$_SESSION['user_id']]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ตรวจสอบว่าีสินค้าในตะกร้าหรือไม่
if (empty($cartItems)) {
    setAlert('warning', 'ไม่มีสินค้าในตะกร้า');
    redirect('cart.php');
}

// คำนวณราารวม
$subtotal = 0;
foreach ($cartItems as $item) {
    $price = $item['discount_price'] > 0 ? $item['discount_price'] : $item['price'];
    $subtotal += $price * $item['quantity'];
}

// ค่าัดส่งเริ่มต้น
$shippingFee = 50;
$total = $subtotal + $shippingFee;

// ดึงข้อมูลผู้ใช้
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$pageTitle = "ชำระเงิน - ร้านขนมปั้นสิบยายนิด";

include '../includes/head.php';
include '../includes/navbar.php';
?>

<div class="checkout-container py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <!-- ข้อมูลการจัดส่ง -->
                <div class="card checkout-card mb-4">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"><i class="fas fa-truck me-2"></i>ข้อมูลการจัดส่ง</h4>
                    </div>
                    <div class="card-body">
                        <form id="checkoutForm" method="POST" action="process_checkout.php">
                            <!-- ข้อมูลผู้รับ -->
                            <div class="mb-4">
                                <h5 class="mb-3 border-bottom pb-2">ข้อมูลผู้รับ</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="fullname" class="form-label">ชื่อ-สกุล</label>
                                        <input type="text" class="form-control" id="fullname" name="fullname" 
                                               value="<?= htmlspecialchars($user['fullname']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">เบอร์โทรศัพท์</label>
                                        <input type="tel" class="form-control" id="phone" name="phone"
                                               value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                                    </div>
                                </div>
                            </div>

                            <!-- ที่อยู่จัดส่ง -->
                            <div class="mb-4">
                                <h5 class="mb-3 border-bottom pb-2">ที่อยู่จัดส่ง</h5>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="address" class="form-label">ที่อยู่</label>
                                        <textarea class="form-control" id="address" name="address" rows="3" required><?= htmlspecialchars($user['address']  ?? '') ?></textarea>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="province" class="form-label">จังหวัด</label>
                                        <select class="form-select" id="province" name="province" required>
                                            <option value="">เลือกจังหวัด</option>
                                            <option value="กรุงเทพมหานคร">กรุงเทพมหานคร</option>
                                            <option value="นนทบุรี">นนทบุรี</option>
                                            <option value="ปทุมธานี">ปทุมธานี</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="district" class="form-label">อำเภอ/เขต</label>
                                        <select class="form-select" id="district" name="district" required>
                                            <option value="">เลือกอำเภอ/เขต</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="postal_code" class="form-label">รหัสไปรษณีย์</label>
                                        <input type="text" class="form-control" id="postal_code" name="postal_code" required>
                                    </div>
                                </div>
                            </div>

                            <!-- วิธีการจัดส่ง -->
                            <div class="mb-4">
                                <h5 class="mb-3 border-bottom pb-2">วิธีการจัดส่ง</h5>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="shipping_method" id="standardShipping" value="standard" checked>
                                    <label class="form-check-label" for="standardShipping">
                                        มาตรฐาน (จัดส่งภายใน 3-5 วัน) <span class="text-muted">+50</span>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="shipping_method" id="expressShipping" value="express">
                                    <label class="form-check-label" for="expressShipping">
                                        ด่วน (จัดส่งภายใน 1-2 วัน) <span class="text-muted">+100</span>
                                    </label>
                                </div>
                            </div>
                    </div>
                </div>

                <!-- วิธีการชำระเงิน -->
                <div class="card checkout-card">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"><i class="fas fa-credit-card me-2"></i>วิธีการชำระเงิน</h4>
                    </div>
                    <div class="card-body">
                        <div class="payment-methods">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" id="bankTransfer" value="bank_transfer" checked>
                                <label class="form-check-label" for="bankTransfer">
                                    <i class="fas fa-university me-2 text-primary"></i> โอนเงินผ่านธนาคาร
                                </label>
                                <div class="bank-transfer-info mt-3" id="bankTransferInfo">
                                    <p>คุณสามารถโอนเงินไปยังบัญชีต่อไปนี้:</p>
                                    <ul class="list-unstyled">
                                        <li><strong>ธนาคาร:</strong> กสิกรไทย</li>
                                        <li><strong>ชื่อบัญชี:</strong> ร้านขนมปั้นสิบยายนิด</li>
                                        <li><strong>เลขที่บัญชี:</strong> 123-4-56789-0</li>
                                    </ul>
                                    <div class="mb-3">
                                        <label for="payment_slip" class="form-label">อัพโหลดสลิปการโอน (ถ้ามี)</label>
                                        <input class="form-control" type="file" id="payment_slip" name="payment_slip" accept="image/*">
                                    </div>
                                </div>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod">
                                <label class="form-check-label" for="cod">
                                    <i class="fas fa-money-bill-wave me-2 text-danger"></i> ชำระเงินปลายทาง (COD)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- สรุปคำสั่งซื้อ -->
                <div class="card checkout-card mb-4">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"><i class="fas fa-receipt me-2"></i>สรุปคำสั่งซื้อ</h4>
                    </div>
                    <div class="card-body">
                        <div class="order-summary">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="order-item d-flex mb-3 pb-3 border-bottom">
                                    <div class="flex-shrink-0">
                                        <img src="<?= BASE_URL . ($item['image'] ?: 'assets/images/no-image.jpg') ?>" 
                                             alt="<?= htmlspecialchars($item['name']) ?>" 
                                             class="img-thumbnail" width="80">
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted"><?= $item['quantity'] ?> ชิ้น</span>
                                            <span class="fw-bold">
                                                <?= number_format(($item['discount_price'] > 0 ? $item['discount_price'] : $item['price']) * $item['quantity'], 2) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <div class="order-totals mt-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>ยอดรวมสินค้า</span>
                                    <span><?= number_format($subtotal, 2) ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>ค่าัดส่ง</span>
                                    <span id="shippingCost"><?= number_format($shippingFee, 2) ?></span>
                                </div>
                                <div class="d-flex justify-content-between border-top pt-3">
                                    <span class="fw-bold">รวมทั้งสิ้น</span>
                                    <span class="fw-bold" id="totalAmount"><?= number_format($total, 2) ?></span>
                                </div>
                            </div>

                            <button type="submit" form="checkoutForm" class="btn btn-success w-100 mt-4 py-2">
                                <i class="fas fa-lock me-2"></i>ยืนยันการสั่งซื้อ
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ข้อมูลเพิ่มเติม -->
                <div class="card checkout-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-shield-alt fa-2x text-success me-3"></i>
                            <div>
                                <h6 class="mb-0">การรักษาความปลอดภัย</h6>
                                <small class="text-muted">ข้อมูลของคุณจะถูกเข้ารหัสและปลอดภัย</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-headset fa-2x text-success me-3"></i>
                            <div>
                                <h6 class="mb-0">ช่วยเหลือ</h6>
                                <small class="text-muted">ติดต่อเรา: 099-999-9999</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // เปลี่ยนวิธีการจัดส่ง
    $('input[name="shipping_method"]').change(function() {
        const shippingCost = $(this).val() === 'express' ? 100 : 50;
        $('#shippingCost').text('' + shippingCost.toFixed(2));
        
        const total = <?= $subtotal ?> + shippingCost;
        $('#totalAmount').text('' + total.toFixed(2));
    });

    // ตัวอย่างการโหลดอำเภอเมื่อเลือกจังหวัด
    $('#province').change(function() {
        const province = $(this).val();
        const $district = $('#district');
        
        $district.empty().append('<option value="">เลือกอำเภอ/เขต</option>');
        
        if (province === 'กรุงเทพมหานคร') {
            $district.append('<option value="บางพลัด">บางพลัด</option>');
            $district.append('<option value="บางบอน">บางบอน</option>');
        } else if (province === 'นนทบุรี') {
            $district.append('<option value="เมืองนนทบุรี">เมืองนนทบุรี</option>');
            $district.append('<option value="บางใหญ่">บางใหญ่</option>');
        } else if (province === 'ปทุมธานี') {
            $district.append('<option value="เมืองปทุมธานี">เมืองปทุมธานี</option>');
            $district.append('<option value="คลองหลวง">คลองหลวง</option>');
        }
    });

    // ตรวจสอบความถูกต้องของฟอร์มก่อนส่ง
    $('#checkoutForm').submit(function(e) {
        // สามารถเพิ่มการตรวจสอบเพิ่มเติมที่นี่
        return confirm('คุณแน่ใจต้องการยืนยันการสั่งซื้อหรือไม่?');
    });
});
</script>

<?php
include '../includes/footer.php';
?>
