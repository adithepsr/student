<?php
/**
 * Admin Dashboard
 * Research-Oriented LMS
 */

require_once '../config/database.php';
require_once '../config/session.php';

requireRole('admin');

// Get system statistics
$totalUsers = fetchOne($pdo, "SELECT COUNT(*) as total FROM users")['total'] ?? 0;
$totalTeachers = fetchOne($pdo, "SELECT COUNT(*) as total FROM users WHERE role = 'teacher'")['total'] ?? 0;
$totalStudents = fetchOne($pdo, "SELECT COUNT(*) as total FROM users WHERE role = 'student'")['total'] ?? 0;
$totalCourses = fetchOne($pdo, "SELECT COUNT(*) as total FROM courses WHERE is_active = TRUE")['total'] ?? 0;

// Get recent users
$recentUsers = fetchAll($pdo, "
    SELECT user_id, username, email, full_name, role, created_at, last_login, is_active
    FROM users
    ORDER BY created_at DESC
    LIMIT 10
");

// Get all courses
$courses = fetchAll($pdo, "
    SELECT c.*, u.full_name as teacher_name,
           COUNT(DISTINCT ce.student_id) as student_count
    FROM courses c
    JOIN users u ON c.teacher_id = u.user_id
    LEFT JOIN course_enrollments ce ON c.course_id = ce.course_id AND ce.status = 'active'
    GROUP BY c.course_id
    ORDER BY c.created_at DESC
");

// Get all users for management
$allUsers = fetchAll($pdo, "
    SELECT user_id, username, email, full_name, role, created_at, last_login, is_active
    FROM users
    ORDER BY created_at DESC
");

include '../includes/header.php';
?>

<main class="container my-4">
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-shield-check"></i> แผงควบคุมผู้ดูแลระบบ</h2>
            <p class="text-muted">ยินดีต้อนรับ, <?php echo htmlspecialchars(getCurrentUserName()); ?></p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">ผู้ใช้ทั้งหมด</h6>
                        <h3 class="mb-0"><?php echo $totalUsers; ?></h3>
                    </div>
                    <div class="stat-icon text-primary">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card stat-card success">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">อาจารย์</h6>
                        <h3 class="mb-0"><?php echo $totalTeachers; ?></h3>
                    </div>
                    <div class="stat-icon text-success">
                        <i class="bi bi-person-badge"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card stat-card warning">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">นักเรียน</h6>
                        <h3 class="mb-0"><?php echo $totalStudents; ?></h3>
                    </div>
                    <div class="stat-icon text-warning">
                        <i class="bi bi-mortarboard"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card stat-card danger">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">รายวิชา</h6>
                        <h3 class="mb-0"><?php echo $totalCourses; ?></h3>
                    </div>
                    <div class="stat-icon text-danger">
                        <i class="bi bi-book"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#users">
                <i class="bi bi-people"></i> จัดการผู้ใช้
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#courses">
                <i class="bi bi-book"></i> รายวิชาทั้งหมด
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#settings">
                <i class="bi bi-gear"></i> ตั้งค่าระบบ
            </a>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content">
        <!-- Users Tab -->
        <div class="tab-pane fade show active" id="users">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">จัดการผู้ใช้งาน</h5>
                    <a href="../auth/register.php" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle"></i> เพิ่มผู้ใช้
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>ชื่อผู้ใช้</th>
                                    <th>ชื่อ-นามสกุล</th>
                                    <th>อีเมล</th>
                                    <th>บทบาท</th>
                                    <th>สถานะ</th>
                                    <th>เข้าสู่ระบบล่าสุด</th>
                                    <th>การดำเนินการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allUsers as $index => $user): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <?php
                                            $roleClass = match($user['role']) {
                                                'admin' => 'bg-danger',
                                                'teacher' => 'bg-success',
                                                'student' => 'bg-primary',
                                                default => 'bg-secondary'
                                            };
                                            $roleText = match($user['role']) {
                                                'admin' => 'ผู้ดูแลระบบ',
                                                'teacher' => 'อาจารย์',
                                                'student' => 'นักเรียน',
                                                default => 'ไม่ทราบ'
                                            };
                                            ?>
                                            <span class="badge <?php echo $roleClass; ?>"><?php echo $roleText; ?></span>
                                        </td>
                                        <td>
                                            <?php if ($user['is_active']): ?>
                                                <span class="badge bg-success">ใช้งาน</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">ระงับ</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($user['last_login']): ?>
                                                <small><?php echo date('d/m/Y H:i', strtotime($user['last_login'])); ?></small>
                                            <?php else: ?>
                                                <small class="text-muted">ยังไม่เคยเข้าสู่ระบบ</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="user_edit.php?id=<?php echo $user['user_id']; ?>" 
                                                   class="btn btn-outline-primary" title="แก้ไข">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <?php if ($user['user_id'] != getCurrentUserId()): ?>
                                                    <?php if ($user['is_active']): ?>
                                                        <a href="user_toggle.php?id=<?php echo $user['user_id']; ?>&action=deactivate" 
                                                           class="btn btn-outline-warning" title="ระงับ"
                                                           onclick="return confirm('คุณแน่ใจหรือไม่ที่จะระงับผู้ใช้นี้?')">
                                                            <i class="bi bi-pause-circle"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="user_toggle.php?id=<?php echo $user['user_id']; ?>&action=activate" 
                                                           class="btn btn-outline-success" title="เปิดใช้งาน">
                                                            <i class="bi bi-play-circle"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="user_delete.php?id=<?php echo $user['user_id']; ?>" 
                                                       class="btn btn-outline-danger" title="ลบ"
                                                       onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบผู้ใช้นี้? การกระทำนี้ไม่สามารถย้อนกลับได้')">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Courses Tab -->
        <div class="tab-pane fade" id="courses">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">รายวิชาทั้งหมดในระบบ</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($courses)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 4rem; opacity: 0.3;"></i>
                            <p class="text-muted mt-3">ยังไม่มีรายวิชาในระบบ</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>รหัสวิชา</th>
                                        <th>ชื่อวิชา</th>
                                        <th>อาจารย์ผู้สอน</th>
                                        <th>ปีการศึกษา</th>
                                        <th>ภาคเรียน</th>
                                        <th>จำนวนนักเรียน</th>
                                        <th>สถานะ</th>
                                        <th>การดำเนินการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($courses as $course): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($course['course_code']); ?></td>
                                            <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                            <td><?php echo htmlspecialchars($course['teacher_name']); ?></td>
                                            <td><?php echo htmlspecialchars($course['academic_year']); ?></td>
                                            <td>
                                                <?php
                                                $semesterText = match($course['semester']) {
                                                    '1' => 'ภาคเรียนที่ 1',
                                                    '2' => 'ภาคเรียนที่ 2',
                                                    'summer' => 'ภาคฤดูร้อน',
                                                    default => 'ไม่ทราบ'
                                                };
                                                echo $semesterText;
                                                ?>
                                            </td>
                                            <td><?php echo $course['student_count']; ?> คน</td>
                                            <td>
                                                <?php if ($course['is_active']): ?>
                                                    <span class="badge bg-success">เปิดใช้งาน</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">ปิดใช้งาน</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="course_view.php?id=<?php echo $course['course_id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> ดู
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Settings Tab -->
        <div class="tab-pane fade" id="settings">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">ตั้งค่าระบบ</h5>
                </div>
                <div class="card-body">
                    <h6 class="mb-3">ข้อมูลระบบ</h6>
                    <table class="table table-sm">
                        <tr>
                            <td width="200"><strong>ชื่อระบบ</strong></td>
                            <td>Research-Oriented LMS</td>
                        </tr>
                        <tr>
                            <td><strong>เวอร์ชัน</strong></td>
                            <td>1.0.0</td>
                        </tr>
                        <tr>
                            <td><strong>PHP Version</strong></td>
                            <td><?php echo phpversion(); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Database</strong></td>
                            <td>MySQL (<?php echo $pdo->getAttribute(PDO::ATTR_SERVER_VERSION); ?>)</td>
                        </tr>
                        <tr>
                            <td><strong>Server Time</strong></td>
                            <td><?php echo date('Y-m-d H:i:s'); ?></td>
                        </tr>
                    </table>

                    <hr class="my-4">

                    <h6 class="mb-3">การจัดการฐานข้อมูล</h6>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> 
                        <strong>คำเตือน:</strong> การดำเนินการเหล่านี้อาจส่งผลกระทบต่อข้อมูลในระบบ
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary" onclick="alert('ฟีเจอร์นี้กำลังพัฒนา')">
                            <i class="bi bi-download"></i> สำรองข้อมูล
                        </button>
                        <button class="btn btn-outline-danger" onclick="alert('ฟีเจอร์นี้กำลังพัฒนา')">
                            <i class="bi bi-arrow-clockwise"></i> รีเซ็ตระบบ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-clock-history"></i> ผู้ใช้ที่สมัครล่าสุด</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>ชื่อผู้ใช้</th>
                            <th>ชื่อ-นามสกุล</th>
                            <th>บทบาท</th>
                            <th>สมัครเมื่อ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentUsers as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td>
                                    <?php
                                    $roleClass = match($user['role']) {
                                        'admin' => 'bg-danger',
                                        'teacher' => 'bg-success',
                                        'student' => 'bg-primary',
                                        default => 'bg-secondary'
                                    };
                                    $roleText = match($user['role']) {
                                        'admin' => 'ผู้ดูแลระบบ',
                                        'teacher' => 'อาจารย์',
                                        'student' => 'นักเรียน',
                                        default => 'ไม่ทราบ'
                                    };
                                    ?>
                                    <span class="badge <?php echo $roleClass; ?>"><?php echo $roleText; ?></span>
                                </td>
                                <td>
                                    <small><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
