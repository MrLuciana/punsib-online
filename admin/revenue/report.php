<?php
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../config/admin_functions.php';

if (!isAdmin()) {
    setAlert('danger', 'คุณไม่มีสิทธิ์เข้าึงหน้านี้');
    redirect(BASE_URL);
}

$pageTitle = "รายงานรายได้";

// ตัวกรองข้อมูล
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-d');
$group_by = $_GET['group_by'] ?? 'day';
$payment_method = $_GET['payment_method'] ?? '';
$source = $_GET['source'] ?? '';

// สร้างคำสั่ง SQL
$sql = "SELECT 
            DATE(date) as report_date,
            SUM(amount) as total_amount,
            SUM(discount_amount) as total_discount,
            SUM(shipping_fee) as total_shipping,
            SUM(tax_amount) as total_tax,
            SUM(net_amount) as total_net,
            COUNT(id) as transaction_count
        FROM revenue 
        WHERE date BETWEEN ? AND ?";

$params = [$date_from, $date_to];

if (!empty($payment_method)) {
    $sql .= " AND payment_method = ?";
    $params[] = $payment_method;
}

if (!empty($source)) {
    $sql .= " AND source = ?";
    $params[] = $source;
}

// กลุ่มข้อมูลตามที่เลือก
switch ($group_by) {
    case 'month':
        $sql .= " GROUP BY YEAR(date), MONTH(date)";
        $sql .= " ORDER BY YEAR(date), MONTH(date)";
        break;
    case 'year':
        $sql .= " GROUP BY YEAR(date)";
        $sql .= " ORDER BY YEAR(date)";
        break;
    case 'product':
        $sql = "SELECT 
                    p.name as product_name,
                    SUM(r.amount) as total_amount,
                    SUM(r.discount_amount) as total_discount,
                    SUM(r.net_amount) as total_net,
                    COUNT(r.id) as transaction_count
                FROM revenue r
                JOIN products p ON r.product_id = p.id
                WHERE r.date BETWEEN ? AND ?";

        if (!empty($payment_method)) {
            $sql .= " AND r.payment_method = ?";
        }

        if (!empty($source)) {
            $sql .= " AND r.source = ?";
        }

        $sql .= " GROUP BY r.product_id";
        $sql .= " ORDER BY total_net DESC";
        break;
    case 'category':
        $sql = "SELECT 
                    c.name as category_name,
                    SUM(r.amount) as total_amount,
                    SUM(r.discount_amount) as total_discount,
                    SUM(r.net_amount) as total_net,
                    COUNT(r.id) as transaction_count
                FROM revenue r
                JOIN categories c ON r.category_id = c.id
                WHERE r.date BETWEEN ? AND ?";

        if (!empty($payment_method)) {
            $sql .= " AND r.payment_method = ?";
        }

        if (!empty($source)) {
            $sql .= " AND r.source = ?";
        }

        $sql .= " GROUP BY r.category_id";
        $sql .= " ORDER BY total_net DESC";
        break;
    default: // day
        $sql .= " GROUP BY DATE(date)";
        $sql .= " ORDER BY date";
}

// ดึงข้อมูลรายงาน
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// คำนวณยอดรวมทั้งหมด
$total_summary = [
    'amount' => 0,
    'discount' => 0,
    'shipping' => 0,
    'tax' => 0,
    'net' => 0,
    'count' => 0
];

foreach ($reports as $report) {
    $total_summary['amount'] += $report['total_amount'] ?? 0;
    $total_summary['discount'] += $report['total_discount'] ?? 0;
    $total_summary['shipping'] += $report['total_shipping'] ?? 0;
    $total_summary['tax'] += $report['total_tax'] ?? 0;
    $total_summary['net'] += $report['total_net'] ?? 0;
    $total_summary['count'] += $report['transaction_count'] ?? 0;
}

include '../../includes/admin-head.php';
include '../../includes/admin-navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/admin-sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">รายงานรายได้</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="exportBtn">
                            <i class="fas fa-file-export me-1"></i> Export
                        </button>
                    </div>
                </div>
            </div>

            <!-- ฟอร์มกรองข้อมูล -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>กรองข้อมูล</h5>
                </div>
                <div class="card-body">
                    <form method="get" class="row g-3">
                        <div class="col-md-3">
                            <label for="date_from" class="form-label">จากวันที่</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="<?= $date_from ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="date_to" class="form-label">ถึงวันที่</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="<?= $date_to ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="group_by" class="form-label">กลุ่มข้อมูลตาม</label>
                            <select class="form-select" id="group_by" name="group_by">
                                <option value="day" <?= $group_by == 'day' ? 'selected' : '' ?>>รายวัน</option>
                                <option value="month" <?= $group_by == 'month' ? 'selected' : '' ?>>รายเดือน</option>
                                <option value="year" <?= $group_by == 'year' ? 'selected' : '' ?>>รายปี</option>
                                <option value="product" <?= $group_by == 'product' ? 'selected' : '' ?>>สินค้า</option>
                                <option value="category" <?= $group_by == 'category' ? 'selected' : '' ?>>หมวดหมู่</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="payment_method" class="form-label">วิธีการชำระเงิน</label>
                            <select class="form-select" id="payment_method" name="payment_method">
                                <option value="">ทั้งหมด</option>
                                <option value="cash" <?= $payment_method == 'cash' ? 'selected' : '' ?>>เงินสด</option>
                                <option value="credit_card" <?= $payment_method == 'credit_card' ? 'selected' : '' ?>>บัตรเครดิต</option>
                                <option value="bank_transfer" <?= $payment_method == 'bank_transfer' ? 'selected' : '' ?>>โอนเงิน</option>
                                <option value="qr_code" <?= $payment_method == 'qr_code' ? 'selected' : '' ?>>QR Code</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="source" class="form-label">ช่องทางขาย</label>
                            <select class="form-select" id="source" name="source">
                                <option value="">ทั้งหมด</option>
                                <option value="online" <?= $source == 'online' ? 'selected' : '' ?>>ออนไลน์</option>
                                <option value="walk_in" <?= $source == 'walk_in' ? 'selected' : '' ?>>หน้าร้าน</option>
                                <option value="delivery" <?= $source == 'delivery' ? 'selected' : '' ?>>จัดส่ง</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i> ค้นหา
                            </button>
                            <a href="report.php" class="btn btn-outline-secondary">
                                <i class="fas fa-sync-alt me-1"></i> ล้างค่า
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- สรุปยอดรวม -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">สรุปยอดรวม</h5>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="card-subtitle mb-2 text-muted">จำนวนรายการ</h6>
                                            <h4 class="card-title"><?= number_format($total_summary['count']) ?></h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="card-subtitle mb-2 text-muted">ยอดขายรวม</h6>
                                            <h4 class="card-title"><?= number_format($total_summary['amount'], 2) ?> บาท</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="card-subtitle mb-2 text-muted">ส่วนลดรวม</h6>
                                            <h4 class="card-title"><?= number_format($total_summary['discount'], 2) ?> บาท</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="card-subtitle mb-2 text-muted">ยอดสุทธิ</h6>
                                            <h4 class="card-title"><?= number_format($total_summary['net'], 2) ?> บาท</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ตารางแสดงผล -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="revenueTable">
                            <thead class="table-light">
                                <tr>
                                    <?php if ($group_by == 'day'): ?>
                                        <th width="120">วันที่</th>
                                    <?php elseif ($group_by == 'month'): ?>
                                        <th width="120">เดือน</th>
                                    <?php elseif ($group_by == 'year'): ?>
                                        <th width="120">ปี</th>
                                    <?php elseif ($group_by == 'product'): ?>
                                        <th>สินค้า</th>
                                    <?php elseif ($group_by == 'category'): ?>
                                        <th>หมวดหมู่</th>
                                    <?php endif; ?>
                                    <th width="120" class="text-end">ยอดขาย</th>
                                    <th width="120" class="text-end">ส่วนลด</th>
                                    <th width="120" class="text-end">ค่าส่ง</th>
                                    <th width="120" class="text-end">ภาษี</th>
                                    <th width="120" class="text-end">ยอดสุทธิ</th>
                                    <th width="100" class="text-end">จำนวน</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reports as $report):?>
                                    <tr>
                                        <?php if ($group_by == 'day'): ?>
                                            <td><?= thaiDate($report['report_date']) ?></td>
                                        <?php elseif ($group_by == 'month'): ?>
                                            <td><?= date('F Y', strtotime($report['report_date'])) ?></td>
                                        <?php elseif ($group_by == 'year'): ?>
                                            <td><?= date('Y', strtotime($report['report_date'])) ?></td>
                                        <?php elseif ($group_by == 'product'): ?>
                                            <td><?= htmlspecialchars($report['product_name']) ?></td>
                                        <?php elseif ($group_by == 'category'): ?>
                                            <td><?= htmlspecialchars($report['category_name']) ?></td>
                                        <?php endif; ?>
                                        <td class="text-end"><?= number_format($report['total_amount'] ?? 0, 2) ?></td>
                                        <td class="text-end"><?= number_format($report['total_discount'] ?? 0, 2) ?></td>
                                        <td class="text-end"><?= number_format($report['total_shipping'] ?? 0, 2) ?></td>
                                        <td class="text-end"><?= number_format($report['total_tax'] ?? 0, 2) ?></td>
                                        <td class="text-end"><?= number_format($report['total_net'] ?? 0, 2) ?></td>
                                        <td class="text-end"><?= number_format($report['transaction_count'] ?? 0) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    $(document).ready(function() {
        // ระบบ DataTable
        $('#revenueTable').DataTable({
            responsive: true,
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Thai.json'
            },
            order: [],
            pageLength: 25,
            initComplete: function() {
                // ซ่อนปุ่ม Export เดิม
                $('#exportBtn').hide();
            }
        });
    });
</script>

    <!-- DataTables JS -->
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>

<?php
include '../../includes/footer.php';
?>