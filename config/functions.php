<?php
/**
 * ไฟล์ฟังก์ชันช่วยเหลือสำหรับระบบร้านขนมปั้นสิบยายนิดพัทลุง
 * Optimized version with additional helper functions
 */

/**
 * Session and User Related Functions
 */

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function isCustomer(): bool {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'customer';
}

function getCurrentUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Alert and Message Functions
 */

function displayAlert(): void {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        echo '<div class="alert alert-' . htmlspecialchars($alert['type']) . ' alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($alert['message']);
        if (isset($alert['details'])) {
            echo '<div class="mt-2 small">' . htmlspecialchars($alert['details']) . '</div>';
        }
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION['alert']);
    }
}

function setAlert(string $type, string $message, string $details = null): void {
    $_SESSION['alert'] = [
        'type' => $type,
        'message' => $message,
        'details' => $details
    ];
}

/**
 * URL and Redirect Functions
 */

function redirect(string $url, int $statusCode = 302): void {
    header("Location: $url", true, $statusCode);
    exit();
}

function redirectBack(): void {
    $referer = $_SERVER['HTTP_REFERER'] ?? '/';
    redirect($referer);
}

function asset(string $path): string {
    $timestamp = file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($path, '/')) 
        ? '?v=' . filemtime($_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($path, '/'))
        : '';
    return BASE_URL . ltrim($path, '/') . $timestamp;
}

function url(string $path = ''): string {
    return BASE_URL . ltrim($path, '/');
}

/**
 * Security Functions
 */

function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrfField(): string {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * File Handling Functions
 */

function uploadImage(array $file, string $target_dir, int $max_size = 5000000, array $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp']): array {
    // Error checking
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $upload_errors = [
            UPLOAD_ERR_INI_SIZE => 'ไฟล์มีขนาดใหญ่เกินค่าที่กำหนดในระบบ',
            UPLOAD_ERR_FORM_SIZE => 'ไฟล์มีขนาดใหญ่เกินค่าที่กำหนดในฟอร์ม',
            UPLOAD_ERR_PARTIAL => 'ไฟล์ถูกอัปโหลดเพียงบางส่วน',
            UPLOAD_ERR_NO_FILE => 'ไม่พบไฟล์ที่อัปโหลด',
            UPLOAD_ERR_NO_TMP_DIR => 'ไม่พบโฟลเดอร์ชั่วคราว',
            UPLOAD_ERR_CANT_WRITE => 'ไม่สามารถเขียนไฟล์ลงดิสก์',
            UPLOAD_ERR_EXTENSION => 'การอัปโหลดถูกหยุดโดยส่วนขยาย PHP'
        ];
        return [
            'success' => false,
            'message' => $upload_errors[$file['error']] ?? 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์'
        ];
    }

    // Check if file is an actual image
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return ['success' => false, 'message' => 'ไฟล์ที่อัปโหลดไม่ใช่รูปภาพ'];
    }

    // Check file size
    if ($file["size"] > $max_size) {
        $max_size_mb = round($max_size / 1024 / 1024, 2);
        return ['success' => false, 'message' => "ขนาดไฟล์ใหญ่เกิน {$max_size_mb} MB"];
    }

    // Check file type
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    if (!in_array($imageFileType, $allowed_types)) {
        return ['success' => false, 'message' => 'อนุญาตเฉพาะไฟล์ ' . implode(', ', $allowed_types)];
    }

    // Generate unique filename
    $new_filename = uniqid() . '_' . time() . '.' . $imageFileType;
    $target_file = rtrim($target_dir, '/') . '/' . $new_filename;

    // Create directory if not exists
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    // Move uploaded file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return [
            'success' => true,
            'file_path' => $target_file,
            'file_name' => $new_filename,
            'mime_type' => $check['mime']
        ];
    }

    return ['success' => false, 'message' => 'เกิดข้อผิดพลาดในการบันทึกไฟล์'];
}

function deleteImage(string $file_path): bool {
    if (file_exists($file_path) && is_file($file_path)) {
        return unlink($file_path);
    }
    return false;
}

/**
 * Order and Product Related Functions
 */

function getOrderStatusText(string $status): string {
    $statuses = [
        'pending' => 'รอดำเนินการ',
        'processing' => 'กำลังดำเนินการ',
        'shipped' => 'จัดส่งแล้ว',
        'delivered' => 'ส่งถึงผู้รับแล้ว',
        'completed' => 'สำเร็จ',
        'cancelled' => 'ยกเลิก'
    ];
    return $statuses[$status] ?? $status;
}

function getOrderStatusBadgeClass(string $status): string {
    $classes = [
        'pending' => 'bg-warning text-dark',
        'processing' => 'bg-info text-white',
        'shipped' => 'bg-primary text-white',
        'delivered' => 'bg-success text-white',
        'completed' => 'bg-success text-white',
        'cancelled' => 'bg-danger text-white'
    ];
    return $classes[$status] ?? 'bg-secondary text-white';
}

function getPaymentMethodText(string $method): string {
    $methods = [
        'cash' => 'เงินสด',
        'credit_card' => 'บัตรเครดิต',
        'bank_transfer' => 'โอนเงินผ่านธนาคาร',
        'qr_code' => 'QR Code'
    ];
    return $methods[$method] ?? $method;
}

function getPaymentStatusText(string $status): string {
    $statuses = [
        'pending' => 'รอการชำระเงิน',
        'paid' => 'ชำระเงินแล้ว',
        'failed' => 'ชำระเงินไม่สำเร็จ'
    ];
    return $statuses[$status] ?? $status;
}

function formatPrice(float $price, string $currency = '฿'): string {
    return $currency . number_format($price, 2);
}

function calculateDiscountPercentage(float $originalPrice, float $discountPrice): float {
    if ($originalPrice <= 0) return 0;
    return round((($originalPrice - $discountPrice) / $originalPrice) * 100, 2);
}

/**
 * Date and Time Functions
 */

function thaiDate(string $date, bool $showTime = false, bool $shortMonth = false): string {
    $thai_months = [
        1 => 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน',
        'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม',
        'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
    ];
    
    $short_thai_months = [
        1 => 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.',
        'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.',
        'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
    ];
    
    $timestamp = strtotime($date);
    $day = date('j', $timestamp);
    $month = (int)date('n', $timestamp);
    $year = (int)date('Y', $timestamp) + 543;
    $time = date('H:i', $timestamp);
    
    $month_name = $shortMonth ? $short_thai_months[$month] : $thai_months[$month];
    $date_string = "{$day} {$month_name} {$year}";
    
    if ($showTime) {
        $date_string .= " {$time} น.";
    }
    
    return $date_string;
}

function formatDateTime(string $dateTime, string $format = 'd/m/Y H:i'): string {
    $date = new DateTime($dateTime);
    return $date->format($format);
}

/**
 * Validation Functions
 */

function validateEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validateThaiPhone(string $phone): bool {
    return preg_match('/^0[0-9]{8,9}$/', $phone) === 1;
}

function validateDate(string $date, string $format = 'Y-m-d'): bool {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

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
 * String and Text Functions
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

function generateSlug(string $text): string {
    $text = preg_replace('/[^a-zA-Z0-9ก-๙\s-]/u', '', $text);
    $text = preg_replace('/\s+/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    $text = trim($text, '-');
    $text = mb_strtolower($text, 'UTF-8');
    
    return $text;
}

function truncateText(string $text, int $length = 100, string $suffix = '...'): string {
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . $suffix;
}

/**
 * API and AJAX Functions
 */

function isAjaxRequest(): bool {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

function jsonResponse(array $data, int $status_code = 200): void {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

function isJson(string $string): bool {
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}

/**
 * Database and Calculation Functions
 */

function calculatePercentageChange(float $current, float $previous): float {
    if ($previous == 0) return 0;
    return round((($current - $previous) / $previous) * 100, 2);
}

function getCategoryColor(string $category): string {
    $colors = [
        'ขนมปั้นสิบ' => '#4e73df',
        'ขนมไทย' => '#1cc88a',
        'ขนมอบ' => '#36b9cc',
        'ขนมหวาน' => '#f6c23e',
        'อื่นๆ' => '#e74a3b'
    ];
    return $colors[$category] ?? '#858796';
}

/**
 * Generate random string
 */
function generateRandomString(int $length = 10): string {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

/**
 * Generate verification code
 */
function generateVerificationCode(int $length = 6): string {
    return str_pad(rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

/**
 * Get client IP address
 */
function getClientIp(): string {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
}


function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validate_password($password) {
    // ตรวจสอบความยาวอย่างน้อย 8 ตัวอักษร
    if (strlen($password) < 8) {
        return false;
    }
    
    // ตรวจสอบว่ามีตัวเลขอย่างน้อย 1 ตัว
    if (!preg_match('/[0-9]/', $password)) {
        return false;
    }
    
    // ตรวจสอบว่ามีตัวอักษรพิมพ์ใหญ่อย่างน้อย 1 ตัว
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }
    
    // ตรวจสอบว่ามีตัวอักษรพิมพ์เล็กอย่างน้อย 1 ตัว
    if (!preg_match('/[a-z]/', $password)) {
        return false;
    }
    
    return true;
}
