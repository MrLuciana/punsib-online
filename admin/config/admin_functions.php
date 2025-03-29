<?php
/**
 * ฟังก์ชันสำหรับผู้ดูแลระบบ
 */

// ฟังก์ชันตรวจสอบสถานะคำสั่งซื้อ
// function getOrderStatusText($status) {
//     $statuses = [
//         'pending' => 'รอดำเนินการ',
//         'processing' => 'กำลังเตรียมสินค้า',
//         'shipped' => 'จัดส่งแล้ว',
//         'delivered' => 'จัดส่งสำเร็จ',
//         'completed' => 'เสร็จสิ้น',
//         'cancelled' => 'ยกเลิก'
//     ];
//     return $statuses[$status] ?? $status;
// }

function getOrderStatusColor($status) {
    $colors = [
        'pending' => 'secondary',
        'processing' => 'info',
        'shipped' => 'primary',
        'delivered' => 'success',
        'completed' => 'success',
        'cancelled' => 'danger'
    ];
    return $colors[$status] ?? 'secondary';
}

// ฟังก์ชันตรวจสอบสถานะการชำระเงิน
// function getPaymentStatusText($status) {
//     $statuses = [
//         'pending' => 'รอชำระเงิน',
//         'paid' => 'ชำระเงินแล้ว',
//         'failed' => 'ชำระเงินล้มเหลว'
//     ];
//     return $statuses[$status] ?? $status;
// }

function getPaymentStatusColor($status) {
    $colors = [
        'pending' => 'warning',
        'paid' => 'success',
        'failed' => 'danger'
    ];
    return $colors[$status] ?? 'secondary';
}

// ฟังก์ชันอัปโหลดรูปภาพ
function uploadProductImage($file) {
    $targetDir = "../../uploads/products/";
    $fileName = uniqid() . '_' . basename($file["name"]);
    $targetFile = $targetDir . $fileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // // ตรวจสอบว่าเป็นรูปภาพจริงหรือไม่
    // $check = getimagesize($file["tmp_name"]);
    // if ($check === false) {
    //     return ['success' => false, 'message' => 'ไฟล์ที่อัปโหลดไม่ใช่รูปภาพ'];
    // }

    // ตรวจสอบขนาดไฟล์ (ไม่เกิน 5MB)
    if ($file["size"] > 5000000) {
        return ['success' => false, 'message' => 'ขนาดไฟล์ใหญ่เกิน 5MB'];
    }

    // อนุญาตเฉพาะไฟล์รูปภาพบางรูปแบบ
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($imageFileType, $allowedTypes)) {
        return ['success' => false, 'message' => 'อนุญาตเฉพาะไฟล์ JPG, JPEG, PNG & GIF เท่านั้น'];
    }

    // สร้างไดเร็กทอรีถ้ายังไม่มี
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    // พยายามอัปโหลดไฟล์
    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        return ['success' => true, 'file_path' => $fileName];
    } else {
        return ['success' => false, 'message' => 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์'];
    }
}
