<?php
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../config/admin_functions.php';

if (!isAdmin()) {
    setAlert('danger', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
    redirect(BASE_URL );
}

$pageTitle = "แก้ไขสินค้า";

// ตรวจสอบว่ามี ID สินค้าหรือไม่
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setAlert('danger', 'ไม่พบสินค้าที่ต้องการแก้ไข');
    redirect(BASE_URL . 'admin/products/list.php');
}

$productId = intval($_GET['id']);

// ดึงข้อมูลสินค้าจากฐานข้อมูล
$stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
$stmt->execute([':id' => $productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    setAlert('danger', 'ไม่พบสินค้าที่ต้องการแก้ไข');
    redirect(BASE_URL . 'admin/products/list.php');
}

// ดึงข้อมูลหมวดหมู่
$categories = $conn->query("SELECT * FROM categories WHERE status = 1")->fetchAll(PDO::FETCH_ASSOC);

// ตรวจสอบการ submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $category_id = $_POST['category_id'] ?: null;
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $discount_price = floatval($_POST['discount_price']);
    $stock = intval($_POST['stock']);
    $status = isset($_POST['status']) ? 1 : 0;
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    // ตรวจสอบข้อมูลที่จำเป็น
    if (empty($name) || $price <= 0) {
        setAlert('danger', 'กรุณากรอกชื่อสินค้าและราคาให้ถูกต้อง');
    } else {
        try {
            $imagePath = $product['image'];
            
            // อัปโหลดรูปภาพถ้ามีการอัปโหลดใหม่
            if (!empty($_FILES['image']['name'])) {
                $uploadResult = uploadProductImage($_FILES['image']);
                if (!$uploadResult['success']) {
                    throw new Exception($uploadResult['message']);
                }
                $imagePath = $uploadResult['file_path'];
                
                // ลบรูปภาพเก่าถ้ามี
                if ($product['image'] && file_exists(BASE_URL . "uploads/products/" . $product['image'])) {
                    unlink(BASE_URL . "uploads/products/" . $product['image']);
                }
            }
            
            // อัปเดตข้อมูลสินค้า
            $stmt = $conn->prepare("
                UPDATE products SET
                    category_id = :category_id,
                    name = :name,
                    description = :description,
                    price = :price,
                    discount_price = :discount_price,
                    image = :image,
                    stock = :stock,
                    status = :status,
                    featured = :featured,
                    updated_at = NOW()
                WHERE id = :id
            ");
            
            $stmt->execute([
                ':category_id' => $category_id,
                ':name' => $name,
                ':description' => $description,
                ':price' => $price,
                ':discount_price' => $discount_price > 0 ? $discount_price : null,
                ':image' => $imagePath,
                ':stock' => $stock,
                ':status' => $status,
                ':featured' => $featured,
                ':id' => $productId
            ]);
            
            setAlert('success', 'อัปเดตสินค้าเรียบร้อยแล้ว');
            redirect(BASE_URL . 'admin/products/list.php');
            
        } catch (Exception $e) {
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
                <h1 class="h2">แก้ไขสินค้า</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="list.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i> กลับไปรายการสินค้า
                    </a>
                </div>
            </div>

            <?php displayAlert(); ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="name" class="form-label">ชื่อสินค้า <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">รายละเอียดสินค้า</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($product['description']) ?></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="price" class="form-label">ราคาปกติ <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" value="<?= $product['price'] ?>" required>
                                            <span class="input-group-text">บาท</span>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="discount_price" class="form-label">ราคาลดพิเศษ</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="discount_price" name="discount_price" min="0" step="0.01" value="<?= $product['discount_price'] ?>">
                                            <span class="input-group-text">บาท</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="category_id" class="form-label">หมวดหมู่</label>
                                        <select class="form-select" id="category_id" name="category_id">
                                            <option value="">-- เลือกหมวดหมู่ --</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?= $category['id'] ?>" <?= $product['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($category['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="stock" class="form-label">จำนวนสต็อก</label>
                                        <input type="number" class="form-control" id="stock" name="stock" min="0" value="<?= $product['stock'] ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="status" name="status" <?= $product['status'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="status">เปิดขายสินค้า</label>
                                    </div>
                                    
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="featured" name="featured" <?= $product['featured'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="featured">สินค้าแนะนำ</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="image" class="form-label">รูปภาพสินค้า</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                    
                                    <div class="mt-3 text-center">
                                        <img id="imagePreview" src="<?= $product['image'] ? BASE_URL . "uploads/products/" . $product['image'] : BASE_URL . 'assets/images/no-image.png' ?>" 
                                             class="img-thumbnail" style="max-height: 200px; display: block;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="reset" class="btn btn-secondary me-md-2">
                                <i class="fas fa-undo me-2"></i> ล้างข้อมูล
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> บันทึกการเปลี่ยนแปลง
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// แสดงตัวอย่างรูปภาพก่อนอัปโหลด
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
    } else {
        preview.src = '<?= $product['image'] ? BASE_URL . "uploads/products/" . $product['image'] : BASE_URL . 'assets/images/no-image.png' ?>';
    }
});
</script>

<?php
include '../../includes/footer.php';
?>
