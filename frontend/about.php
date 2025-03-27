<?php
require_once '../config/db.php';
require_once '../config/functions.php';

$pageTitle = "เกี่ยวกับเรา - ร้านขนมปั้นสิบยายนิดพัทลุง";

include '../includes/head.php';
include '../includes/navbar.php';
?>

<!-- Hero Section -->
<section class="about-hero bg-light py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="display-4 fw-bold mb-4">เกี่ยวกับยายนิด</h1>
                <p class="lead">รักษ์คุณค่าทางวัฒนธรรม รสชาติดั่งเดิม แบบฉบับพัทลุง</p>
            </div>
            <div class="col-md-6">
                <img src="<?= BASE_URL ?>assets/images/about-hero.webp" alt="เกี่ยวกับยายนิด" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</section>

<!-- Our Story -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h2 class="section-title mb-4">เรื่องราวของเรา</h2>
                <div class="section-divider mb-5"></div>
                
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-year">1970</div>
                        <div class="timeline-content">
                            <h3>จุดเริ่มต้น</h3>
                            <p>ยายนิดเริ่มทำขนมปั้นสิบขายในตลาดนัดพัทลุง ด้วยสูตรดั้งเดิมที่สืบทอดมาจากบรรพบุรุษ</p>
                        </div>
                    </div>
                    
                    <div class="timeline-item">
                        <div class="timeline-year">1995</div>
                        <div class="timeline-content">
                            <h3>เปิดร้านถาวร</h3>
                            <p>ตั้งร้านขนมปั้นสิบยายนิดอย่างเป็นทางการในตัวเมืองพัทลุง</p>
                        </div>
                    </div>
                    
                    <div class="timeline-item">
                        <div class="timeline-year">2010</div>
                        <div class="timeline-content">
                            <h3>ขยายสายผลิต</h3>
                            <p>เพิ่มเมนูขนมไทยโบราอื่นๆ นอกเหนือจากขนมปั้นสิบ</p>
                        </div>
                    </div>
                    
                    <div class="timeline-item">
                        <div class="timeline-year">2020</div>
                        <div class="timeline-content">
                            <h3>เปิดตัวเว็บไซต์</h3>
                            <p>ขยายช่องทางขายออนไลน์เพื่อให้ลูกค้าั่งซื้อได้สะดวกยิ่งขึ้น</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Our Values -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="section-title text-center mb-5">คุณค่าของเรา</h2>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card value-card h-100 border-0 text-center p-4">
                    <div class="value-icon mx-auto mb-4">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3>ทำด้วยใจ</h3>
                    <p>ทุกชิ้นทำด้วยความตั้งใจและความพิถีพิถัน เพื่อให้ได้ขนมที่มีรสชาติดีที่สุด</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card value-card h-100 border-0 text-center p-4">
                    <div class="value-icon mx-auto mb-4">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h3>วัตถุดิบคุณภาพ</h3>
                    <p>เลือกใช้เฉพาะวัตถุดิบคุณภาพดี ปลอดสารเคมี เพื่อสุขภาพที่ดีของผู้บริโภค</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card value-card h-100 border-0 text-center p-4">
                    <div class="value-icon mx-auto mb-4">
                        <i class="fas fa-history"></i>
                    </div>
                    <h3>สูตรดั้งเดิม</h3>
                    <p>ยึดมั่นในสูตรดั้งเดิมที่สืบทอดกันมา ไม่ตัดทอนขั้นตอนเพื่อความรวดเร็ว</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Our Team -->
<section class="py-5 bg-white">
    <div class="container">
        <h2 class="section-title text-center mb-5">ทีมงานของเรา</h2>
        
        <div class="row g-4 justify-content-center">
            <div class="col-lg-3 col-md-6">
                <div class="card team-card border-0 text-center h-100">
                    <img src="<?= BASE_URL ?>assets/images/team-yainid.webp" class="card-img-top" alt="ยายนิด">
                    <div class="card-body">
                        <h3 class="card-title">ยายนิด</h3>
                        <p class="text-muted">ผู้ก่อตั้ง</p>
                        <p class="card-text">ผู้คิดค้นสูตรขนมปั้นสิบอันเลื่องชื่อ</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card team-card border-0 text-center h-100">
                    <img src="<?= BASE_URL ?>assets/images/team-somchai.webp" class="card-img-top" alt="สมชาย">
                    <div class="card-body">
                        <h3 class="card-title">สมชาย</h3>
                        <p class="text-muted">หัวหน้ารัว</p>
                        <p class="card-text">ผู้ควบคุมการผลิตและพัฒนาสูตร</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card team-card border-0 text-center h-100">
                    <img src="<?= BASE_URL ?>assets/images/team-somporn.webp" class="card-img-top" alt="สมพร">
                    <div class="card-body">
                        <h3 class="card-title">สมพร</h3>
                        <p class="text-muted">ฝ่ายการตลาด</p>
                        <p class="card-text">ดูแลการขายและการสื่อสารกับลูกค้า</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card team-card border-0 text-center h-100">
                    <img src="<?= BASE_URL ?>assets/images/team-nidnoi.webp" class="card-img-top" alt="นิดน้อย">
                    <div class="card-body">
                        <h3 class="card-title">นิดน้อย</h3>
                        <p class="text-muted">ผู้สืบทอด</p>
                        <p class="card-text">รุ่นลูกที่กำลังเรียนรู้สูตรดั้งเดิม</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Our Promise -->
<section class="py-5 bg-success text-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h2 class="mb-4">คำสัญญาของเรา</h2>
                <p class="lead mb-5">เรามุ่งมั่นที่จะรักษามาตรฐานและรสชาติแบบดั้งเดิมของขนมปั้นสิบยายนิด พร้อมทั้งพัฒนาบริการให้ดียิ่งขึ้นเพื่อความพึงพอใจของลูกค้าุกท่าน</p>
                <a href="products.php" class="btn btn-light btn-lg px-4">ชมสินค้าองเรา</a>
            </div>
        </div>
    </div>
</section>

<!-- Contact Info -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card contact-card border-0 h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-map-marker-alt fa-2x mb-3 text-success"></i>
                        <h3>ที่อยู่</h3>
                        <p>123 ถนนเทศบาล<br>อำเภอเมือง พัทลุง 93000</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card contact-card border-0 h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-phone-alt fa-2x mb-3 text-success"></i>
                        <h3>ติดต่อเรา</h3>
                        <p>074-123456<br>089-1234567</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card contact-card border-0 h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-clock fa-2x mb-3 text-success"></i>
                        <h3>เวลาทำการ</h3>
                        <p>ทุกวัน 08:00 - 18:00 น.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Hero Section */
.about-hero {
    background: linear-gradient(135deg, rgba(25, 135, 84, 0.1) 0%, rgba(255, 255, 255, 1) 100%);
}

/* Section Styling */
.section-title {
    position: relative;
    display: inline-block;
}

.section-title:after {
    content: '';
    position: absolute;
    left: 50%;
    bottom: -10px;
    transform: translateX(-50%);
    width: 50px;
    height: 3px;
    background-color: #198754;
}

.section-divider {
    width: 100px;
    height: 2px;
    background-color: #198754;
    margin: 0 auto;
}

/* Timeline */
.timeline {
    position: relative;
    padding-left: 50px;
}

.timeline:before {
    content: '';
    position: absolute;
    left: 25px;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: #198754;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-year {
    position: absolute;
    left: -50px;
    top: 0;
    width: 40px;
    height: 40px;
    line-height: 40px;
    text-align: center;
    background-color: #198754;
    color: white;
    border-radius: 50%;
    font-weight: bold;
}

.timeline-content {
    padding: 20px;
    background-color: white;
    border-radius: 5px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
}

.timeline-content h3 {
    color: #198754;
}

/* Value Cards */
.value-card {
    transition: transform 0.3s ease;
}

.value-card:hover {
    transform: translateY(-5px);
}

.value-icon {
    width: 70px;
    height: 70px;
    line-height: 70px;
    background-color: rgba(25, 135, 84, 0.1);
    color: #198754;
    border-radius: 50%;
    font-size: 30px;
}

/* Team Cards */
.team-card {
    transition: transform 0.3s ease;
    overflow: hidden;
}

.team-card:hover {
    transform: translateY(-5px);
}

.team-card .card-img-top {
    height: 250px;
    object-fit: cover;
}

/* Contact Cards */
.contact-card {
    transition: transform 0.3s ease;
    background-color: white;
}

.contact-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}
</style>

<?php
include '../includes/footer.php';
?>
