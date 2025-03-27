<?php
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../config/admin_functions.php';

header('Content-Type: application/json');

// ตรวจสอบสิทธิ์ผู้ดูแลระบบ
if (!isAdmin()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// รับค่าการกรอง
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// ดึงข้อมูลสรุปช่องทางการขาย
$stmt = $conn->prepare("
    SELECT 
        source,
        SUM(net_amount) as total_amount
    FROM revenue
    WHERE date BETWEEN ? AND ?
    GROUP BY source
");
$stmt->execute([$startDate, $endDate]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// จัดรูปแบบข้อมูลสำหรับ Chart.js
$labels = [];
$values = [];

foreach ($result as $row) {
    $labels[] = getSourceText($row['source']);
    $values[] = (float)$row['total_amount'];
}

// ฟังก์ชันแปลง source เป็นข้อความ
function getSourceText($source) {
    $sources = [
        'online' => 'ออนไลน์',
        'walk_in' => 'หน้าร้าน',
        'delivery' => 'จัดส่ง'
    ];
    return $sources[$source] ?? $source;
}

echo json_encode([
    'labels' => $labels,
    'values' => $values
]);
?>
