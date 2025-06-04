<?php
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../config/admin_functions.php';

if (!isAdmin()) {
    setAlert('danger', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
    redirect(BASE_URL);
}

$pageTitle = "แก้ไขหมวดหมู่";

// ตรวจสอบว่ามี ID ที่ส่งมาหรือไม่
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setAlert('danger', 'ไม่พบหมวดหมู่ที่ต้องการแก้ไข');
    redirect(BASE_URL . '/admin/categories/list.php');
}

$categoryId = (int)$_GET['id'];

// ดึงข้อมูลหมวดหมู่จากฐานข้อมูล
try {
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$categoryId]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$category) {
        setAlert('danger', 'ไม่พบหมวดหมู่ที่ต้องการแก้ไข');
        redirect(BASE_URL . '/admin/categories/list.php');
    }
} catch (PDOException $e) {
    setAlert('danger', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
    redirect(BASE_URL . '/admin/categories/list.php');
}

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
            $imagePath = $category['image'];

            if (!empty($_FILES['image']['name'])) {
                $uploadDir = __DIR__ . '/../../uploads/categories/';
                $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
                $targetFile = $uploadDir . $fileName;
                $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

                $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

                if (!in_array($fileType, $allowedTypes)) {
                    setAlert('danger', 'ประเภทไฟล์ไม่ถูกต้อง (รองรับเฉพาะ jpg, jpeg, png, gif)');
                } elseif (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                    // ลบรูปภาพเก่า
                    if ($category['image'] && file_exists($uploadDir . $category['image'])) {
                        unlink($uploadDir . $category['image']);
                    }
                    $imagePath = $fileName;
                } else {
                    setAlert('danger', 'ไม่สามารถอัปโหลดรูปภาพได้');
                }
            }

            // ตรวจสอบว่ามีชื่อหมวดหมู่ซ้ำหรือไม่ (ไม่รวมตัวเอง)
            $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ? AND id != ?");
            $stmt->execute([$name, $categoryId]);

            if ($stmt->rowCount() > 0) {
                setAlert('danger', 'มีชื่อหมวดหมู่นี้อยู่แล้ว');
            } else {
                // อัปเดตข้อมูลในฐานข้อมูล
                $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ?, status = ?, image = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$name, $description, $status, $imagePath, $categoryId]);

                setAlert('success', 'อัปเดตหมวดหมู่เรียบร้อยแล้ว');
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
                <h1 class="h2">แก้ไขหมวดหมู่</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="list.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i> กลับหน้ารายการ
                    </a>
                </div>
            </div>

            <?php displayAlert(); ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="name" class="form-label">ชื่อหมวดหมู่ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required
                                value="<?= htmlspecialchars($category['name']) ?>">
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">คำอธิบาย</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?=
                                                                                                        htmlspecialchars($category['description'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="status" name="status"
                                <?= $category['status'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="status">เปิดใช้งาน</label>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">รูปภาพหมวดหมู่</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">

                            <div class="mt-3 text-center">
                                <img id="imagePreview" src="<?= BASE_URL ?>uploads/categories/<?= $category['image'] ?>"
                                    class="img-thumbnail" style="max-height: 200px; display: block;">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> บันทึกการเปลี่ยนแปลง
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
<script>
    document.getElementById('image').addEventListener('change', function(e) {
        const preview = document.getElementById('imagePreview');
        const file = e.target.files[0];

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    });
</script>

<?php
include '../../includes/footer.php';
?>