<?php
require_once '../config/db.php';
require_once '../config/functions.php';

$pageTitle = "ร้านขนมปั้นสิบยายนิดพัทลุง";

// ดึงสินค้าแนะนำ
$stmt = $conn->prepare("SELECT * FROM products WHERE featured = 1 AND status = 1 LIMIT 8");
$stmt->execute();
$featuredProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ดึงหมวดหมู่
$stmt = $conn->prepare("SELECT * FROM categories WHERE status = 1 LIMIT 6");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/head.php';
include '../includes/navbar.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

?>

<div class="hero-section bg-light py-5">
    <div class="container text-center">
        <h1 class="display-4">ขนมปั้นสิบยายนิดพัทลุง</h1>
        <p class="lead">ขนมพื้นบ้านรสชาติดี ทำด้วยใจ ส่งถึงมือคุณ</p>
        <a href="products.php" class="btn btn-success btn-lg mt-3">ดูสินค้าทั้งหมด</a>
    </div>
</div>

<div class="container my-5">
    <h2 class="text-center mb-4">สินค้าแนะนำ</h2>
    <div class="row">
        <?php foreach($featuredProducts as $product): ?>
        <div class="col-md-3 mb-4">
            <div class="card h-100">
                <img src="<?php echo BASE_URL . $product['image']; ?>" class="card-img-top" alt="<?php echo $product['name']; ?>">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $product['name']; ?></h5>
                    <p class="card-text text-success fw-bold">
                        <?php echo number_format($product['price'], 2); ?>
                        <?php if($product['discount_price'] > 0): ?>
                            <span class="text-danger text-decoration-line-through ms-2"><?php echo number_format($product['discount_price'], 2); ?></span>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="card-footer bg-white">
                    <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-success btn-sm">ดูรายละเอียด</a>
                    <button class="btn btn-success btn-sm add-to-cart" data-id="<?php echo $product['id']; ?>">เพิ่มในตะกร้า</button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="bg-light py-5">
    <div class="container">
        <h2 class="text-center mb-4">หมวดหมู่สินค้า</h2>
        <div class="row">
            <?php foreach($categories as $category): ?>
            <div class="col-md-2 mb-3">
                <a href="products.php?category=<?php echo $category['id']; ?>" class="text-decoration-none">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $category['name']; ?></h5>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
include '../includes/footer.php';
?>
