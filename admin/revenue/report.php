<?php
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../config/admin_functions.php';

if (!isAdmin()) {
    setAlert('danger', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
    redirect(BASE_URL);
}

$pageTitle = "รายงานยอดขาย";

// กำหนดค่าเริ่มต้นสำหรับการกรอง
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');
$groupBy = $_GET['group_by'] ?? 'day'; // day, month, year
$paymentMethod = $_GET['payment_method'] ?? 'all'; // all, cash, credit_card, bank_transfer, qr_code
$source = $_GET['source'] ?? 'all'; // all, online, walk_in, delivery

// สร้างเงื่อนไขสำหรับ Query
$where = "WHERE date BETWEEN ? AND ?";
$params = [$startDate, $endDate];

if ($paymentMethod !== 'all') {
    $where .= " AND payment_method = ?";
    $params[] = $paymentMethod;
}

if ($source !== 'all') {
    $where .= " AND source = ?";
    $params[] = $source;
}

// กำหนดรูปแบบวันที่สำหรับการ GROUP BY
$dateFormat = '';
$groupByText = '';
switch ($groupBy) {
    case 'month':
        $dateFormat = "%Y-%m";
        $groupByText = 'เดือน';
        break;
    case 'year':
        $dateFormat = "%Y";
        $groupByText = 'ปี';
        break;
    default:
        $dateFormat = "%Y-%m-%d";
        $groupByText = 'วัน';
        break;
}

// ดึงข้อมูลรายงาน
$stmt = $conn->prepare("
    SELECT 
        DATE_FORMAT(date, ?) as period,
        SUM(amount) as total_revenue,
        SUM(discount_amount) as total_discount,
        SUM(shipping_fee) as total_shipping,
        SUM(tax_amount) as total_tax,
        SUM(net_amount) as net_revenue,
        COUNT(id) as transaction_count
    FROM revenue
    $where
    GROUP BY period
    ORDER BY period
");
$stmt->execute(array_merge([$dateFormat], $params));
$revenueReport = $stmt->fetchAll(PDO::FETCH_ASSOC);

// คำนวณยอดรวมทั้งหมด
$totalSummary = [
    'revenue' => 0,
    'discount' => 0,
    'shipping' => 0,
    'tax' => 0,
    'net' => 0,
    'transactions' => 0
];

foreach ($revenueReport as $row) {
    $totalSummary['revenue'] += $row['total_revenue'];
    $totalSummary['discount'] += $row['total_discount'];
    $totalSummary['shipping'] += $row['total_shipping'];
    $totalSummary['tax'] += $row['total_tax'];
    $totalSummary['net'] += $row['net_revenue'];
    $totalSummary['transactions'] += $row['transaction_count'];
}

include '../../includes/admin-head.php';
include '../../includes/admin-navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/admin-sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">รายงานยอดขาย</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button class="btn btn-outline-secondary me-2" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>พิมพ์รายงาน
                    </button>
                    <button class="btn btn-outline-success" id="exportReport">
                        <i class="fas fa-file-excel me-2"></i>Export Excel
                    </button>
                </div>
            </div>

            <!-- ฟอร์มกรองข้อมูล -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="get" class="row g-3">
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">วันที่เริ่มต้น</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $startDate ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">วันที่สิ้นสุด</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $endDate ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="group_by" class="form-label">จัดกลุ่มตาม</label>
                            <select class="form-select" id="group_by" name="group_by">
                                <option value="day" <?= $groupBy === 'day' ? 'selected' : '' ?>>รายวัน</option>
                                <option value="month" <?= $groupBy === 'month' ? 'selected' : '' ?>>รายเดือน</option>
                                <option value="year" <?= $groupBy === 'year' ? 'selected' : '' ?>>รายปี</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="payment_method" class="form-label">ช่องทางการชำระเงิน</label>
                            <select class="form-select" id="payment_method" name="payment_method">
                                <option value="all" <?= $paymentMethod === 'all' ? 'selected' : '' ?>>ทั้งหมด</option>
                                <option value="cash" <?= $paymentMethod === 'cash' ? 'selected' : '' ?>>เงินสด</option>
                                <option value="credit_card" <?= $paymentMethod === 'credit_card' ? 'selected' : '' ?>>บัตรเครดิต</option>
                                <option value="bank_transfer" <?= $paymentMethod === 'bank_transfer' ? 'selected' : '' ?>>โอนเงิน</option>
                                <option value="qr_code" <?= $paymentMethod === 'qr_code' ? 'selected' : '' ?>>QR Code</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="source" class="form-label">ช่องทางการขาย</label>
                            <select class="form-select" id="source" name="source">
                                <option value="all" <?= $source === 'all' ? 'selected' : '' ?>>ทั้งหมด</option>
                                <option value="online" <?= $source === 'online' ? 'selected' : '' ?>>ออนไลน์</option>
                                <option value="walk_in" <?= $source === 'walk_in' ? 'selected' : '' ?>>หน้าร้าน</option>
                                <option value="delivery" <?= $source === 'delivery' ? 'selected' : '' ?>>จัดส่ง</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-2"></i>กรองข้อมูล
                            </button>
                            <a href="report.php" class="btn btn-outline-secondary ms-2">
                                <i class="fas fa-sync-alt me-2"></i>รีเซ็ต
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- สรุปยอดรวม -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h6 class="card-title">รายได้รวม</h6>
                            <h3 class="mb-0"><?= number_format($totalSummary['revenue'], 2) ?> บาท</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h6 class="card-title">ส่วนลด</h6>
                            <h3 class="mb-0"><?= number_format($totalSummary['discount'], 2) ?> บาท</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-white bg-secondary">
                        <div class="card-body">
                            <h6 class="card-title">ค่าจัดส่ง</h6>
                            <h3 class="mb-0"><?= number_format($totalSummary['shipping'], 2) ?> บาท</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h6 class="card-title">ภาษี</h6>
                            <h3 class="mb-0"><?= number_format($totalSummary['tax'], 2) ?> บาท</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h6 class="card-title">รวมสุทธิ</h6>
                            <h3 class="mb-0"><?= number_format($totalSummary['net'], 2) ?> บาท</h3>
                            <small class="float-end"><?= number_format($totalSummary['transactions']) ?> ธุรกรรม</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- กราฟและตารางรายงาน -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">กราฟแสดงรายได้ตาม<?= $groupByText ?></h5>
                        </div>
                        <div class="card-body">
                            <canvas id="revenueChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">สรุปตามช่องทางการขาย</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="salesSourceChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">ตารางรายงานยอดขาย</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="reportTable">
                            <thead>
                                <tr>
                                    <th><?= $groupByText ?></th>
                                    <th class="text-end">รายได้</th>
                                    <th class="text-end">ส่วนลด</th>
                                    <th class="text-end">ค่าจัดส่ง</th>
                                    <th class="text-end">ภาษี</th>
                                    <th class="text-end">รวมสุทธิ</th>
                                    <th class="text-end">จำนวนธุรกรรม</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($revenueReport as $row): ?>
                                <tr>
                                    <td><?= $row['period'] ?></td>
                                    <td class="text-end"><?= number_format($row['total_revenue'], 2) ?></td>
                                    <td class="text-end"><?= number_format($row['total_discount'], 2) ?></td>
                                    <td class="text-end"><?= number_format($row['total_shipping'], 2) ?></td>
                                    <td class="text-end"><?= number_format($row['total_tax'], 2) ?></td>
                                    <td class="text-end fw-bold"><?= number_format($row['net_revenue'], 2) ?></td>
                                    <td class="text-end"><?= $row['transaction_count'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <tr class="table-active">
                                    <td class="fw-bold">รวมทั้งหมด</td>
                                    <td class="text-end fw-bold"><?= number_format($totalSummary['revenue'], 2) ?></td>
                                    <td class="text-end fw-bold"><?= number_format($totalSummary['discount'], 2) ?></td>
                                    <td class="text-end fw-bold"><?= number_format($totalSummary['shipping'], 2) ?></td>
                                    <td class="text-end fw-bold"><?= number_format($totalSummary['tax'], 2) ?></td>
                                    <td class="text-end fw-bold"><?= number_format($totalSummary['net'], 2) ?></td>
                                    <td class="text-end fw-bold"><?= number_format($totalSummary['transactions']) ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // ข้อมูลสำหรับกราฟ
    const reportData = <?= json_encode($revenueReport) ?>;
    const labels = reportData.map(item => item.period);
    const revenueData = reportData.map(item => parseFloat(item.net_revenue));
    
    // กราฟรายได้
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'รายได้สุทธิ (บาท)',
                data: revenueData,
                backgroundColor: 'rgba(25, 135, 84, 0.7)',
                borderColor: 'rgba(25, 135, 84, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString() + ' บาท';
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.raw.toLocaleString() + ' บาท';
                        }
                    }
                }
            }
        }
    });

    // ดึงข้อมูลสรุปช่องทางการขาย
    $.ajax({
        url: 'get-sales-source-data.php',
        method: 'GET',
        data: {
            start_date: '<?= $startDate ?>',
            end_date: '<?= $endDate ?>'
        },
        dataType: 'json',
        success: function(data) {
            const sourceCtx = document.getElementById('salesSourceChart').getContext('2d');
            const sourceChart = new Chart(sourceCtx, {
                type: 'doughnut',
                data: {
                    labels: data.labels,
                    datasets: [{
                        data: data.values,
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(255, 206, 86, 0.7)'
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 99, 132, 1)',
                            'rgba(255, 206, 86, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value.toLocaleString()} บาท (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }
    });

    // ระบบ Export Excel
    $('#exportReport').click(function() {
        // สร้างข้อมูล CSV
        let csv = '<?= $groupByText ?>,รายได้,ส่วนลด,ค่าจัดส่ง,ภาษี,รวมสุทธิ,จำนวนธุรกรรม\n';
        
        reportData.forEach(row => {
            csv += `"${row.period}",${row.total_revenue},${row.total_discount},${row.total_shipping},${row.total_tax},${row.net_revenue},${row.transaction_count}\n`;
        });
        
        csv += `"รวมทั้งหมด",${totalSummary['revenue']},${totalSummary['discount']},${totalSummary['shipping']},${totalSummary['tax']},${totalSummary['net']},${totalSummary['transactions']}\n`;
        
        // สร้างไฟล์และดาวน์โหลด
        const blob = new Blob(["\uFEFF" + csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `รายงานยอดขาย_${startDate}_ถึง_${endDate}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
});
</script>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    .container-fluid, .container-fluid * {
        visibility: visible;
    }
    .container-fluid {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .sidebar, .btn-toolbar {
        display: none !important;
    }
}
</style>

<?php
include '../../includes/footer.php';
?>
