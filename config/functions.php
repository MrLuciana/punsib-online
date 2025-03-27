<?php
/**
 * ไฟล์ฟังก์ชันช่วยเหลือสำหรับระบบร้านขนมปั้นสิบยายนิดพัทลุง
 */

/**
 * ตรวจสอบว่าู้ใช้ล็อกอินอยู่หรือไม่
 * @return bool
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * ตรวจสอบว่าู้ใช้เป็นผู้ดูแลระบบหรือไม่
 * @return bool
 */
function isAdmin(): bool {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * ตรวจสอบว่าู้ใช้เป็นลูกค้าหรือไม่
 * @return bool
 */
function isCustomer(): bool {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'customer';
}

/**
 * แสดงข้อความแจ้งเตือน
 */
function displayAlert(): void {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        echo '<div class="alert alert-' . htmlspecialchars($alert['type']) . ' alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($alert['message']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION['alert']);
    }
}

/**
 * ตั้งค่า้อความแจ้งเตือน
 * @param string $type ประเภท (success, danger, warning, info)
 * @param string $message ข้อความ
 */
function setAlert(string $type, string $message): void {
    $_SESSION['alert'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * ทำการรีไดเร็กไปยัง URL ที่กำหนด
 * @param string $url URL ปลายทาง
 */
function redirect(string $url): void {
    header("Location: $url");
    exit();
}

/**
 * ทำความสะอาข้อมูลเพื่อป้องกัน XSS และ SQL Injection
 * @param mixed $data ข้อมูลที่ต้องการทำความสะอา
 * @return mixed ข้อมูลที่ทำความสะอาแล้ว
 */

 if (!function_exists('sanitize')) {
    function sanitize($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * สร้าง CSRF Token
 * @return string
 */
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * ตรวจสอบ CSRF Token
 * @param string $token Token ที่ต้องการตรวจสอบ
 * @return bool
 */
function verifyCsrfToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * อัปโหลดไฟล์รูปภาพ
 * @param array $file ข้อมูลไฟล์จาก $_FILES
 * @param string $target_dir ไดเร็กทอรีปลายทาง
 * @param int $max_size ขนาดไฟล์สูงสุด (bytes)
 * @return array ผลลัพธ์การอัปโหลด
 */
function uploadImage(array $file, string $target_dir, int $max_size = 5000000): array {
    // ตรวจสอบข้อผิดพลาดในการอัปโหลด
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์'];
    }

    // ตรวจสอบว่าเป็นรูปภาพจริงหรือไม่
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return ['success' => false, 'message' => 'ไฟล์ที่อัปโหลดไม่ใช่รูปภาพ'];
    }

    // ตรวจสอบขนาดไฟล์
    if ($file["size"] > $max_size) {
        return ['success' => false, 'message' => "ขนาดไฟล์ใหญ่เกิน {$max_size} bytes"];
    }

    // ตรวจสอบประเภทไฟล์
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($imageFileType, $allowed_types)) {
        return ['success' => false, 'message' => 'อนุญาตเฉพาะไฟล์ ' . implode(', ', $allowed_types)];
    }

    // สร้างชื่อไฟล์ใหม่เพื่อป้องกันการชนกัน
    $new_filename = uniqid() . '_' . time() . '.' . $imageFileType;
    $target_file = rtrim($target_dir, '/') . '/' . $new_filename;

    // สร้างไดเร็กทอรีถ้ายังไม่มี
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    // พยายามอัปโหลดไฟล์
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return [
            'success' => true,
            'file_path' => $target_file,
            'file_name' => $new_filename
        ];
    } else {
        return ['success' => false, 'message' => 'เกิดข้อผิดพลาดในการบันทึกไฟล์'];
    }
}

/**
 * ลบไฟล์รูปภาพ
 * @param string $file_path ทางเดินไฟล์
 * @return bool
 */
function deleteImage(string $file_path): bool {
    if (file_exists($file_path) && is_file($file_path)) {
        return unlink($file_path);
    }
    return false;
}

/**
 * สร้าง URL สำหรับไฟล์ในระบบ
 * @param string $path ทางเดินไฟล์
 * @return string
 */
function asset(string $path): string {
    return BASE_URL . ltrim($path, '/');
}

/**
 * จัดรูปแบบราา
 * @param float $price ราา
 * @param string $currency สกุลเงิน
 * @return string
 */
function formatPrice(float $price, string $currency = ''): string {
    return $currency . number_format($price, 2);
}

/**
 * ตรวจสอบอีเมล
 * @param string $email อีเมล
 * @return bool
 */
function validateEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * ตรวจสอบเบอร์โทรศัพท์ไทย
 * @param string $phone เบอร์โทรศัพท์
 * @return bool
 */
function validateThaiPhone(string $phone): bool {
    return preg_match('/^0[0-9]{8,9}$/', $phone) === 1;
}

/**
 * สร้างคำอธิบาย SEO จากข้อความ
 * @param string $text ข้อความ
 * @param int $length ความยาวที่ต้องการ
 * @return string
 */
function generateSeoDescription(string $text, int $length = 160): string {
    $text = strip_tags($text);
    $text = str_replace(["\r", "\n"], ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);
    
    if (mb_strlen($text) > $length) {
        $text = mb_substr($text, 0, $length - 3) . '...';
    }
    
    return $text;
}

/**
 * สร้าง slug จากข้อความ
 * @param string $text ข้อความ
 * @return string
 */
function generateSlug(string $text): string {
    $text = preg_replace('/[^a-zA-Z0-9ก-\s-]/u', '', $text);
    $text = preg_replace('/\s+/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    $text = trim($text, '-');
    $text = mb_strtolower($text, 'UTF-8');
    
    return $text;
}

/**
 * ตรวจสอบความแข็งแรงของรหัสผ่าน
 * @param string $password รหัสผ่าน
 * @return array ผลลัพธ์การตรวจสอบ
 */
function checkPasswordStrength(string $password): array {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = 'รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร';
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'รหัสผ่านต้องมีตัวอักษรภาษาอังกฤษตัวใหญ่อย่างน้อย 1 ตัว';
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'รหัสผ่านต้องมีตัวอักษรภาษาอังกฤษตัวเล็กอย่างน้อย 1 ตัว';
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'รหัสผ่านต้องมีตัวเลขอย่างน้อย 1 ตัว';
    }
    
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'รหัสผ่านต้องมีอักขระพิเศษอย่างน้อย 1 ตัว';
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * สร้างรหัสยืนยันแบบสุ่ม
 * @param int $length ความยาวของรหัส
 * @return string
 */
function generateVerificationCode(int $length = 6): string {
    $characters = '0123456789';
    $code = '';
    
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    return $code;
}

/**
 * ตรวจสอบและสร้าง CSRF field สำหรับฟอร์ม
 * @return string
 */
function csrfField(): string {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * ตรวจสอบการร้องขอว่าเป็น AJAX หรือไม่
 * @return bool
 */
function isAjaxRequest(): bool {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

/**
 * ส่งข้อมูล JSON กลับไปยังไคลเอนต์
 * @param array $data ข้อมูลที่จะส่ง
 * @param int $status_code HTTP status code
 */
function jsonResponse(array $data, int $status_code = 200): void {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

/**
 * ตรวจสอบว่า้อมูลเป็น JSON หรือไม่
 * @param string $string ข้อมูลที่จะตรวจสอบ
 * @return bool
 */
function isJson(string $string): bool {
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}

/**
 * ตรวจสอบวันที่
 * @param string $date วันที่
 * @param string $format รูปแบบวันที่
 * @return bool
 */
function validateDate(string $date, string $format = 'Y-m-d'): bool {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * แปลงวันที่เป็นรูปแบบไทย
 * @param string $date วันที่
 * @param bool $show_time แสดงเวลาหรือไม่
 * @return string
 */
function thaiDate(string $date, bool $show_time = false): string {
    $thai_months = [
        1 => 'มกราคม',
        2 => 'กุมภาพันธ์',
        3 => 'มีนาคม',
        4 => 'เมษายน',
        5 => 'พฤษภาคม',
        6 => 'มิถุนายน',
        7 => 'กรกฎาคม',
        8 => 'สิงหาคม',
        9 => 'กันยายน',
        10 => 'ตุลาคม',
        11 => 'พฤศจิกายน',
        12 => 'ธันวาคม'
    ];
    
    $short_thai_months = [
        1 => 'ม.ค.',
        2 => 'ก.พ.',
        3 => 'มี.ค.',
        4 => 'เม.ย.',
        5 => 'พ.ค.',
        6 => 'มิ.ย.',
        7 => 'ก.ค.',
        8 => 'ส.ค.',
        9 => 'ก.ย.',
        10 => 'ต.ค.',
        11 => 'พ.ย.',
        12 => 'ธ.ค.'
    ];
    
    $timestamp = strtotime($date);
    $day = date('j', $timestamp);
    $month = date('n', $timestamp);
    $year = date('Y', $timestamp) + 543;
    $time = date('H:i', $timestamp);
    
    $date_string = $day . ' ' . $thai_months[$month] . ' ' . $year;
    
    if ($show_time) {
        $date_string .= ' ' . $time . ' น.';
    }
    
    return $date_string;
}
