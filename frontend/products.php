<?php
require_once '../config/db.php';
require_once '../config/functions.php';

$pageTitle = "สินค้าทั้งหมด - ร้านขนมปั้นสิบยายนิดพัทลุง";

// รับค่าการค้นหาและกรอง
$search = $_GET['search'] ?? '';
$category_id = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$page = $_GET['page'] ?? 1;
$perPage = 12;

// สร้างเงื่อนไข SQL
$where = "WHERE p.status = 1";
$params = [];

if (!empty($search)) {
    $where .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($category_id) && is_numeric($category_id)) {
    $where .= " AND p.category_id = ?";
    $params[] = $category_id;
}

// สร้างเงื่อนไขการเรียงลำดับ
$orderBy = "ORDER BY ";
switch ($sort) {
    case 'price_asc':
        $orderBy .= "p.price ASC";
        break;
    case 'price_desc':
        $orderBy .= "p.price DESC";
        break;
    case 'popular':
        $orderBy .= "p.sold DESC";
        break;
    case 'discount':
        $orderBy .= "(p.discount_price > 0) DESC, p.discount_price ASC";
        break;
    default:
        $orderBy .= "p.created_at DESC";
}

// นับจำนวนสินค้าทั้งหมด
$countStmt = $conn->prepare("SELECT COUNT(*) as total FROM products p $where");
$countStmt->execute($params);
$totalProducts = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalProducts / $perPage);

// ดึงข้อมูลสินค้า
$perPage = (int) 12;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

$perPage = (int) $perPage;
$offset = (int) $offset;

$sql = "
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    $where 
    $orderBy 
    LIMIT $perPage OFFSET $offset
";

$stmt = $conn->prepare($sql);
$stmt->execute($params);


$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ดึงหมวดหมู่ทั้งหมด
$categories = $conn->query("SELECT * FROM categories WHERE status = 1")->fetchAll(PDO::FETCH_ASSOC);

include '../includes/head.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-3 mb-4">
            <!-- Sidebar สำหรับกรองสินค้า -->
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>กรองสินค้า</h5>
                </div>
                <div class="card-body">
                    <form id="filterForm" method="get" action="products.php">
                        <div class="mb-3">
                            <label for="search" class="form-label">ค้นหาสินค้า</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?= htmlspecialchars($search) ?>" placeholder="ชื่อสินค้า...">
                        </div>
                        
                        <div class="mb-3">
                            <label for="category" class="form-label">หมวดหมู่</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">ทั้งหมด</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" 
                                        <?= ($category_id == $category['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="sort" class="form-label">เรียงตาม</label>
                            <select class="form-select" id="sort" name="sort">
                                <option value="newest" <?= ($sort == 'newest') ? 'selected' : '' ?>>ใหม่ล่าสุด</option>
                                <option value="price_asc" <?= ($sort == 'price_asc') ? 'selected' : '' ?>>ราคาต่ำ-สูง</option>
                                <option value="price_desc" <?= ($sort == 'price_desc') ? 'selected' : '' ?>>ราคาสูง-ต่ำ</option>
                                <option value="popular" <?= ($sort == 'popular') ? 'selected' : '' ?>>ขายดี</option>
                                <option value="discount" <?= ($sort == 'discount') ? 'selected' : '' ?>>สินค้าลดราคา</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-search me-2"></i>ค้นหา
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <!-- ส่วนแสดงผลสินค้า -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">สินค้าทั้งหมด</h2>
                <div class="text-muted">
                    <?= number_format($totalProducts) ?> สินค้า
                </div>
            </div>
            
            <?php if (empty($products)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>ไม่พบสินค้าที่คุณค้นหา
                </div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
                    <?php foreach ($products as $product): ?>
                        <div class="col">
                            <div class="card h-100 shadow-sm product-card">
                                <?php if ($product['discount_price'] > 0): ?>
                                    <span class="badge bg-danger position-absolute top-0 end-0 m-2">
                                        ลด <?= number_format($product['price'] - $product['discount_price'], 2) ?> บาท
                                    </span>
                                <?php endif; ?>
                                
                                <a href="product-detail.php?id=<?= $product['id'] ?>">
                                    <img src="<?= asset($product['image'] ?? 'assets/images/no-image.jpg') ?>" 
                                         class="card-img-top" 
                                         alt="<?= htmlspecialchars($product['name']) ?>">
                                </a>
                                
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <span class="badge bg-light text-dark">
                                            <?= htmlspecialchars($product['category_name']) ?>
                                        </span>
                                        <?php if ($product['stock'] > 0): ?>
                                            <span class="text-success">
                                                <i class="fas fa-check-circle"></i> มีสินค้า
                                            </span>
                                        <?php else: ?>
                                            <span class="text-danger">
                                                <i class="fas fa-times-circle"></i> สินค้าหมด
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <h5 class="card-title mt-2">
                                        <a href="product-detail.php?id=<?= $product['id'] ?>" class="text-decoration-none text-dark">
                                            <?= htmlspecialchars($product['name']) ?>
                                        </a>
                                    </h5>
                                    
                                    <p class="card-text text-muted small">
                                        <?= mb_substr(strip_tags($product['description']), 0, 60) ?>...
                                    </p>
                                    
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div>
                                            <?php if ($product['discount_price'] > 0): ?>
                                                <span class="text-danger fw-bold fs-5">
                                                    <?= number_format($product['discount_price'], 2) ?> บาท
                                                </span>
                                                <span class="text-decoration-line-through text-muted small ms-2">
                                                    <?= number_format($product['price'], 2) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-success fw-bold fs-5">
                                                    <?= number_format($product['price'], 2) ?> บาท
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <button class="btn btn-sm btn-outline-success add-to-cart" 
                                                data-id="<?= $product['id'] ?>"
                                                <?= ($product['stock'] <= 0) ? 'disabled' : '' ?>>
                                            <i class="fas fa-cart-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav class="mt-5">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" 
                                   href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                            
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                    <a class="page-link" 
                                       href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                <a class="page-link" 
                                   href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // ระบบเพิ่มสินค้าเข้าตะกร้า
    $('.add-to-cart').click(function() {
        const productId = $(this).data('id');
        const button = $(this);
        
        $.ajax({
            url: '<?= BASE_URL ?>includes/cart/add-to-cart.php',
            method: 'POST',
            data: { product_id: productId, quantity: 1 },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    // อัปเดตจำนวนสินค้าในตะกร้า
                    $('.cart-count').text(response.cart_count);
                    
                    // แสดงข้อความสำเร็จ
                    Swal.fire({
                        icon: 'success',
                        title: 'เพิ่มสินค้าเรียบร้อย',
                        text: 'สินค้าถูกเพิ่มลงในตะกร้าแล้ว',
                        showConfirmButton: false,
                        timer: 1500
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: response.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้'
                });
            }
        });
    });
    
    // ระบบกรองสินค้า
    $('#filterForm').on('change', 'select', function() {
        $('#filterForm').submit();
    });
});
</script>

<?php
include '../includes/footer.php';
?>
