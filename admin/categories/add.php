<?php
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../config/admin_functions.php';

if (!isAdmin()) {
    setAlert('danger', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
    redirect(BASE_URL);
}

$pageTitle = "เพิ่มหมวดหมู่";

// ตรวจสอบการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = isset($_POST['status']) ? 1 : 0;

    // ตรวจสอบข้อมูล
    if (empty($name)) {
        setAlert('danger', 'กรุณากรอกชื่อหมวดหมู่');
    } else {
        try {
            // ตรวจสอบว่ามีชื่อหมวดหมู่ซ้ำหรือไม่
            $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ?");
            $stmt->execute([$name]);
            
            if ($stmt->rowCount() > 0) {
                setAlert('danger', 'มีชื่อหมวดหมู่นี้อยู่แล้ว');
            } else {
                // เพิ่มข้อมูลลงฐานข้อมูล
                $stmt = $conn->prepare("INSERT INTO categories (name, description, status) VALUES (?, ?, ?)");
                $stmt->execute([$name, $description, $status]);
                
                setAlert('success', 'เพิ่มหมวดหมู่เรียบร้อยแล้ว');
                redirect(BASE_URL . '/admin/categories/list.php');
            }
        } catch (PDOException $e) {
            setAlert('danger', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }
}

include '../../includes/admin-head.php';
include '../../includes/admin-navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/admin-sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">เพิ่มหมวดหมู่</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="list.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i> กลับหน้ารายการ
                    </a>
                </div>
            </div>

            <?php displayAlert(); ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">ชื่อหมวดหมู่ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required 
                                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">คำอธิบาย</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?= 
                                htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="status" name="status" 
                                   <?= isset($_POST['status']) ? 'checked' : 'checked' ?>>
                            <label class="form-check-label" for="status">เปิดใช้งาน</label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> บันทึกข้อมูล
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php
include '../../includes/footer.php';
?>
