<?php
/**
 * Landing Page
 * Research-Oriented LMS
 */

require_once 'config/session.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $role = getCurrentUserRole();
    if ($role === 'admin') {
        header('Location: /student/admin/dashboard.php');
    } elseif ($role === 'teacher') {
        header('Location: /student/teacher/dashboard.php');
    } else {
        header('Location: /student/student/dashboard.php');
    }
    exit;
}

// Handle logout message
if (isset($_GET['message']) && $_GET['message'] === 'logged_out') {
    setFlashMessage('success', 'ออกจากระบบเรียบร้อยแล้ว');
    header('Location: /student/index.php');
    exit;
}

include 'includes/header.php';
?>

<main>
    <!-- Hero Section -->
    <section class="bg-primary text-white py-5">
        <div class="container py-5">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">
                        <i class="bi bi-mortarboard-fill"></i> Research-Oriented LMS
                    </h1>
                    <p class="lead mb-4">
                        ระบบจัดการเรียนการสอนออนไลน์ที่ออกแบบมาเพื่อการวิจัยทางการศึกษา
                    </p>
                    <p class="mb-4">
                        พัฒนาโดย <strong>อ.อดิเทพ ศรีมันตะ</strong> เพื่อรองรับการสอนและการทำวิจัยอย่างมีประสิทธิภาพ
                    </p>
                    <div class="d-grid gap-2 d-md-flex">
                        <a href="/student/auth/login.php" class="btn btn-light btn-lg px-4">
                            <i class="bi bi-box-arrow-in-right"></i> เข้าสู่ระบบ
                        </a>
                        <a href="/student/auth/register.php" class="btn btn-outline-light btn-lg px-4">
                            <i class="bi bi-person-plus"></i> สมัครสมาชิก
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center mt-5 mt-lg-0">
                    <i class="bi bi-laptop" style="font-size: 15rem; opacity: 0.8;"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container py-5">
            <h2 class="text-center mb-5">ฟีเจอร์หลักของระบบ</h2>
            
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 text-center">
                        <div class="card-body">
                            <div class="text-primary mb-3">
                                <i class="bi bi-book" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="card-title">จัดการเรียนการสอน</h5>
                            <p class="card-text">
                                สร้างรายวิชา อัปโหลดเอกสาร ฝังวิดีโอ และมอบหมายงานได้อย่างง่ายดาย
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 text-center">
                        <div class="card-body">
                            <div class="text-success mb-3">
                                <i class="bi bi-clipboard-check" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="card-title">ระบบทดสอบ</h5>
                            <p class="card-text">
                                คลังข้อสอบ การสุ่มข้อสอบ และระบบตรวจข้อสอบอัตโนมัติ
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 text-center">
                        <div class="card-body">
                            <div class="text-warning mb-3">
                                <i class="bi bi-graph-up" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="card-title">สถิติวิจัย</h5>
                            <p class="card-text">
                                T-Test, E1/E2 Efficiency, Item Analysis และดัชนีประสิทธิผล
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 text-center">
                        <div class="card-body">
                            <div class="text-danger mb-3">
                                <i class="bi bi-bar-chart" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="card-title">Dashboard วิเคราะห์</h5>
                            <p class="card-text">
                                แสดงผลสถิติและกราฟการเรียนรู้แบบเรียลไทม์
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 text-center">
                        <div class="card-body">
                            <div class="text-info mb-3">
                                <i class="bi bi-star" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="card-title">ประเมินความพึงพอใจ</h5>
                            <p class="card-text">
                                สร้างแบบประเมินและวิเคราะห์ผลด้วยกราฟเรดาร์
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 text-center">
                        <div class="card-body">
                            <div class="text-secondary mb-3">
                                <i class="bi bi-shield-check" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="card-title">ความปลอดภัย</h5>
                            <p class="card-text">
                                PDO Prepared Statements และ Password Hashing
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="bg-light py-5">
        <div class="container py-5">
            <h2 class="text-center mb-5">ฟังก์ชันสถิติวิจัย</h2>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card border-primary">
                        <div class="card-body">
                            <h5 class="card-title text-primary">
                                <i class="bi bi-calculator"></i> T-Test Dependent
                            </h5>
                            <p class="card-text">
                                เปรียบเทียบคะแนน Pre-test และ Post-test เพื่อหานัยสำคัญทางสถิติ
                                และวัดการพัฒนาของผู้เรียน
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card border-success">
                        <div class="card-body">
                            <h5 class="card-title text-success">
                                <i class="bi bi-speedometer2"></i> E1/E2 Efficiency
                            </h5>
                            <p class="card-text">
                                คำนวณประสิทธิภาพของสื่อการสอนตามเกณฑ์มาตรฐาน (เช่น 80/80)
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card border-warning">
                        <div class="card-body">
                            <h5 class="card-title text-warning">
                                <i class="bi bi-trophy"></i> Effectiveness Index
                            </h5>
                            <p class="card-text">
                                ดัชนีประสิทธิผลเพื่อวัดการพัฒนาของผู้เรียนอย่างเป็นระบบ
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card border-info">
                        <div class="card-body">
                            <h5 class="card-title text-info">
                                <i class="bi bi-list-check"></i> Item Analysis
                            </h5>
                            <p class="card-text">
                                วิเคราะห์ค่าความยากง่าย (p) และค่าอำนาจจำแนก (r) รายข้อ
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="bg-primary text-white py-5">
        <div class="container text-center py-5">
            <h2 class="mb-4">พร้อมเริ่มต้นใช้งานแล้วหรือยัง?</h2>
            <p class="lead mb-4">
                สมัครสมาชิกวันนี้และเริ่มใช้งานระบบจัดการเรียนการสอนที่ออกแบบมาเพื่องานวิจัย
            </p>
            <a href="/student/auth/register.php" class="btn btn-light btn-lg px-5">
                <i class="bi bi-person-plus"></i> สมัครสมาชิกเลย
            </a>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
