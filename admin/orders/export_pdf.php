<?php
require_once '../../vendor/autoload.php';
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../config/admin_functions.php';

// ตรวจสอบสิทธิ์ Admin
if (!isAdmin()) {
    exit('Unauthorized');
}

// ตรวจสอบว่ามี ID คำสั่งซื้อส่งมาหรือไม่
if (!isset($_GET['id']) || empty($_GET['id'])) {
    exit('ไม่พบรหัสคำสั่งซื้อ'); // เปลี่ยนข้อความเป็นภาษาไทยให้สอดคล้องกัน
}

$orderId = $_GET['id'];

// --- ดึงข้อมูลคำสั่งซื้อ ---
try {
    $sqlOrder = "SELECT o.*, u.fullname, u.email, u.phone 
                 FROM orders o 
                 JOIN users u ON o.user_id = u.id 
                 WHERE o.id = ?";
    $stmtOrder = $conn->prepare($sqlOrder);
    $stmtOrder->execute([$orderId]);
    $order = $stmtOrder->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        exit('ไม่พบข้อมูลคำสั่งซื้อ');
    }

    // --- ดึงข้อมูลรายการสินค้าในคำสั่งซื้อ ---
    $sqlItems = "SELECT oi.*, p.name 
                 FROM order_items oi 
                 JOIN products p ON oi.product_id = p.id 
                 WHERE oi.order_id = ?";
    $stmtItems = $conn->prepare($sqlItems);
    $stmtItems->execute([$orderId]);
    $orderItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // จัดการข้อผิดพลาดในการเชื่อมต่อฐานข้อมูลหรือการ query
    error_log("Database Error: " . $e->getMessage()); // บันทึก error log
    exit('เกิดข้อผิดพลาดในการดึงข้อมูล');
}


// --- ตั้งค่า mPDF ---
try {
    $mpdf = new \Mpdf\Mpdf([
        'default_font' => 'sarabun',
        'fontDir' => array_merge($mpdfConfig['fontDir'] ?? [], [__DIR__ . '/../../fonts']), // ใช้ array_merge เพื่อความยืดหยุ่น
        'fontdata' => array_merge($mpdfConfig['fontdata'] ?? [], [ // ใช้ array_merge
            'sarabun' => [
                'R' => 'THSarabunNew.ttf',
                'B' => 'THSarabunNew Bold.ttf',
                'I' => 'THSarabunNew Italic.ttf', // เพิ่ม Italic ถ้ามี
                'BI' => 'THSarabunNew BoldItalic.ttf', // เพิ่ม BoldItalic ถ้ามี
            ]
        ]),
        'tempDir' => __DIR__ . '/tmp', // ระบุ tempDir ชัดเจน
        'format' => 'A4' // กำหนดขนาดกระดาษ
    ]);

    // เปิดใช้งานการเก็บ Output Buffer
    ob_start();

    // --- ส่วน HTML และ CSS สำหรับ PDF ---
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายละเอียดคำสั่งซื้อ #<?= htmlspecialchars($order['order_number']) ?></title>
    <style>
        body {
            font-family: 'sarabun', sans-serif;
            font-size: 12pt;
            color: #333;
        }
        .container {
            width: 100%;
            margin: 0 auto;
            padding: 15px;
        }
        .header, .footer {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
             margin: 0;
             font-size: 18pt;
             color: #0056b3; /* สีน้ำเงินเข้ม */
        }
         .header p, .company-info p {
             margin: 2px 0;
             font-size: 10pt;
             color: #555;
         }
        .order-details, .customer-details {
            margin-bottom: 20px;
            border: 1px solid #eee;
            padding: 15px;
            background-color: #f9f9f9;
             border-radius: 5px;
        }
         .order-details table, .customer-details table {
             width: 100%;
             border-collapse: collapse;
         }
         .order-details td, .customer-details td {
             padding: 5px;
             vertical-align: top;
         }
        .order-details td:first-child {
             font-weight: bold;
             width: 150px; /* กำหนดความกว้างของคอลัมน์แรก */
             color: #0056b3;
         }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); /* เพิ่มเงาเล็กน้อย */
        }
        .items-table th, .items-table td {
            border: 1px solid #ddd;
            padding: 10px; /* เพิ่ม padding */
            text-align: left;
            vertical-align: middle; /* จัดกึ่งกลางแนวตั้ง */
        }
        .items-table th {
            background-color: #007bff; /* สีพื้นหลังหัวตาราง */
            color: #ffffff; /* สีตัวอักษรหัวตาราง */
            font-weight: bold; /* ใช้ฟอนต์ตัวหนา */
            font-size: 11pt; /* ปรับขนาดฟอนต์หัวตาราง */
        }
        .items-table td.number, .items-table th.number {
            text-align: right; /* ตัวเลขชิดขวา */
        }
         .items-table td.center, .items-table th.center {
             text-align: center; /* จัดกึ่งกลาง */
         }
        .items-table tbody tr:nth-child(even) {
            background-color: #f8f9fa; /* สลับสีพื้นหลังแถว */
        }
         .items-table tbody tr:hover { /* เพิ่ม hover effect เล็กน้อย (อาจไม่เห็นผลใน PDF ทุกตัว) */
            background-color: #e9ecef;
        }
        .total-row td {
            font-weight: bold;
            font-size: 13pt; /* เน้นขนาดตัวอักษร */
            text-align: right;
            background-color: #f0f0f0; /* สีพื้นหลังแถวรวม */
             border-top: 2px solid #aaa; /* เส้นหนาบนแถวรวม */
        }
         .total-label {
             text-align: right;
             font-weight: bold;
              padding-right: 10px;
         }
        .shipping-address {
             margin-top: 5px;
             padding: 10px;
             background-color: #fff;
             border: 1px dashed #ccc;
             border-radius: 4px;
             white-space: pre-wrap; /* ทำให้ nl2br ทำงานได้ดีขึ้น */
         }
         .status-badge {
             display: inline-block; /* ทำให้เป็น block แต่ยังอยู่ในบรรทัด */
             padding: 3px 8px;
             font-size: 10pt;
             font-weight: bold;
             border-radius: 4px;
             color: #fff;
         }
         .status-pending, .payment-pending { background-color: #ffc107; color: #333;} /* เหลือง */
         .status-processing { background-color: #17a2b8; } /* ฟ้า */
         .status-shipped, .payment-paid { background-color: #28a745; } /* เขียว */
         .status-delivered { background-color: #007bff; } /* น้ำเงิน */
         .status-cancelled, .payment-failed { background-color: #dc3545; } /* แดง */
         .status-refunded { background-color: #6c757d; } /* เทา */

         .footer {
             margin-top: 30px;
             font-size: 9pt;
             color: #777;
             border-top: 1px solid #eee;
             padding-top: 10px;
         }
         /* ใช้คลาสแทนการอ้างอิงตำแหน่ง */
         .align-right { text-align: right; }
         .align-center { text-align: center; }
         .font-bold { font-weight: bold; }
         .product-name { font-weight: bold; } /* ทำให้ชื่อสินค้าเด่นขึ้น */

         /* --- ข้อมูลบริษัท (ตัวอย่าง) --- */
         .company-info {
             text-align: right; /* จัดชิดขวา */
             margin-bottom: 20px;
             padding-bottom: 10px;
             border-bottom: 1px solid #eee;
         }
         .company-info h3 {
             margin: 0;
             font-size: 14pt;
             color: #333;
         }

    </style>
</head>
<body>
    <div class="container">

        <table width="100%" style="border-bottom: 1px solid #eee; padding-bottom: 10px;">
            <tr>
                <td style="width: 60%; vertical-align: top;">
                    <div class="header">
                        <h1>ใบสั่งซื้อ / Order Invoice</h1>
                         <p>เลขที่: <strong><?= htmlspecialchars($order['order_number']) ?></strong></p>
                         <p>วันที่สั่งซื้อ: <?= thaiDate($order['created_at'], true, true) ?></p> <?php // เพิ่ม true ตัวสุดท้ายให้แสดงเวลาด้วย ?>
                    </div>
                </td>
                <td style="width: 40%; vertical-align: top;">
                    <div class="company-info">
                        <h3>ชื่อบริษัทของคุณ</h3>
                        <p>123 ถนนตัวอย่าง ตำบล/แขวง อำเภอ/เขต</p>
                        <p>จังหวัด 10000</p>
                        <p>เบอร์โทร: 02-XXX-XXXX</p>
                        <p>เลขประจำตัวผู้เสียภาษี: XXXXXXXXXXXXX</p>
                    </div>
                </td>
            </tr>
        </table>


        <table width="100%" style="margin-top: 20px;">
            <tr>
                <td style="width: 50%; vertical-align: top; padding-right: 10px;">
                     <div class="customer-details">
                        <h3 style="margin-top:0; margin-bottom:10px; color:#0056b3;">ข้อมูลลูกค้า</h3>
                        <table>
                             <tr>
                                 <td>ชื่อ-นามสกุล:</td>
                                 <td><?= htmlspecialchars($order['fullname']) ?></td>
                             </tr>
                             <tr>
                                 <td>อีเมล:</td>
                                 <td><?= htmlspecialchars($order['email']) ?></td>
                             </tr>
                             <tr>
                                 <td>เบอร์โทรศัพท์:</td>
                                 <td><?= htmlspecialchars($order['phone']) ?></td>
                             </tr>
                             <tr>
                                 <td style="vertical-align: top;">ที่อยู่จัดส่ง:</td>
                                 <td><div class="shipping-address"><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></div></td>
                             </tr>
                        </table>
                    </div>
                </td>
                <td style="width: 50%; vertical-align: top; padding-left: 10px;">
                    <div class="order-details">
                        <h3 style="margin-top:0; margin-bottom:10px; color:#0056b3;">รายละเอียดคำสั่งซื้อ</h3>
                        <table>
                            <tr>
                                <td>สถานะคำสั่งซื้อ:</td>
                                <td><span class="status-badge status-<?= strtolower(str_replace(' ', '-', $order['order_status'])) ?>"><?= getOrderStatusText($order['order_status']) ?></span></td>
                            </tr>
                            <tr>
                                <td>สถานะการชำระเงิน:</td>
                                <td><span class="status-badge payment-<?= strtolower(str_replace(' ', '-', $order['payment_status'])) ?>"><?= getPaymentStatusText($order['payment_status']) ?></span></td>
                            </tr>
                            <?php if (!empty($order['payment_method'])): // แสดงช่องทางการชำระเงิน ถ้ามี ?>
                            <tr>
                                <td>ช่องทางการชำระเงิน:</td>
                                <td><?= htmlspecialchars($order['payment_method']) ?></td>
                            </tr>
                             <?php endif; ?>
                            <?php if (!empty($order['tracking_number'])): // แสดงเลข Tracking ถ้ามี ?>
                            <tr>
                                <td>เลขพัสดุ:</td>
                                <td><?= htmlspecialchars($order['tracking_number']) ?></td>
                            </tr>
                             <?php endif; ?>
                            <tr>
                                <td class="font-bold">ยอดรวมสุทธิ:</td>
                                <td class="font-bold" style="font-size: 14pt; color: #dc3545;"><?= number_format($order['total_amount'], 2) ?> บาท</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>


        <h3 style="margin-top: 25px; margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px; color:#0056b3;">รายการสินค้า</h3>
        <table class="items-table">
            <thead>
                <tr>
                    <th class="center" style="width: 5%;">#</th>
                    <th>สินค้า</th>
                    <th class="center" style="width: 10%;">จำนวน</th>
                    <th class="number" style="width: 20%;">ราคาต่อหน่วย (บาท)</th>
                    <th class="number" style="width: 20%;">ราคารวม (บาท)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orderItems as $index => $item): ?>
                <tr>
                    <td class="center"><?= $index + 1 ?></td>
                    <td>
                        <span class="product-name"><?= htmlspecialchars($item['name']) ?></span>
                        <?php // หากมีรายละเอียดเพิ่มเติม เช่น SKU หรือ ตัวเลือกสินค้า สามารถเพิ่มตรงนี้ได้
                           // if (!empty($item['sku'])) echo "<br><small>SKU: ".htmlspecialchars($item['sku'])."</small>";
                           // if (!empty($item['options'])) echo "<br><small>ตัวเลือก: ".htmlspecialchars($item['options'])."</small>";
                        ?>
                    </td>
                    <td class="center"><?= $item['quantity'] ?></td>
                    <td class="number"><?= number_format($item['price'], 2) ?></td>
                    <td class="number"><?= number_format($item['total_price'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="total-row">
                     <td colspan="4" class="total-label">ยอดรวมทั้งหมด:</td>
                     <td class="number"><?= number_format($order['total_amount'], 2) ?> บาท</td>
                 </tr>
                <?php
                // ตัวอย่างการเพิ่มส่วนลด หรือค่าจัดส่ง หากมีข้อมูลใน $order
                // if (isset($order['discount_amount']) && $order['discount_amount'] > 0) {
                //     echo '<tr><td colspan="4" class="align-right font-bold">ส่วนลด:</td><td class="align-right font-bold">- '.number_format($order['discount_amount'], 2).' บาท</td></tr>';
                // }
                // if (isset($order['shipping_cost']) && $order['shipping_cost'] > 0) {
                //     echo '<tr><td colspan="4" class="align-right font-bold">ค่าจัดส่ง:</td><td class="align-right font-bold">'.number_format($order['shipping_cost'], 2).' บาท</td></tr>';
                // }
                // ถ้ามีส่วนลด/ค่าส่ง ต้องปรับ ยอดรวมสุทธิ ในแถวสุดท้ายสุดให้ตรง
                ?>
            </tfoot>
        </table>

        <div class="footer">
            <p>ขอบคุณที่ใช้บริการ</p>
            <p>หากมีข้อสงสัย กรุณาติดต่อ [เบอร์โทรศัพท์ หรือ อีเมลของคุณ]</p>
        </div>

    </div>
</body>
</html>
<?php
    // ดึง HTML จาก Output Buffer
    $html = ob_get_clean();

    // เขียน HTML ลงใน PDF
    $mpdf->WriteHTML($html);

    // กำหนดชื่อไฟล์และส่ง Output ไปยัง Browser
    $fileName = "Order_" . preg_replace('/[^A-Za-z0-9_\-]/', '_', $order['order_number']) . ".pdf"; // ทำความสะอาดชื่อไฟล์
    $mpdf->Output($fileName, 'I'); // 'I' = แสดงในเบราว์เซอร์, 'D' = ดาวน์โหลด

    exit; // จบการทำงานหลังจากสร้าง PDF

} catch (\Mpdf\MpdfException $e) {
    // จัดการข้อผิดพลาดจาก mPDF
    error_log("mPDF Error: " . $e->getMessage());
    exit('เกิดข้อผิดพลาดในการสร้างไฟล์ PDF: ' . $e->getMessage());
} catch (\Exception $e) {
    // จัดการข้อผิดพลาดทั่วไปอื่นๆ
     error_log("General Error: " . $e->getMessage());
    exit('เกิดข้อผิดพลาดทั่วไป: ' . $e->getMessage());
}
?>