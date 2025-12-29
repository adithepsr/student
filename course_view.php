<?php
/**
 * Admin - View Course Details
 * Research-Oriented LMS
 */

require_once '../config/database.php';
require_once '../config/session.php';

requireRole('admin');

$course_id = $_GET['id'] ?? 0;

// Get course details
$course = fetchOne($pdo, "
    SELECT c.*, u.full_name as teacher_name, u.username as teacher_username
    FROM courses c
    JOIN users u ON c.teacher_id = u.user_id
    WHERE c.course_id = ?
", [$course_id]);

if (!$course) {
    setFlashMessage('error', 'ไม่พบรายวิชา');
    header('Location: dashboard.php');
    exit;
}

// Get statistics
$stats = fetchOne($pdo, "
    SELECT 
        COUNT(DISTINCT ce.student_id) as student_count,
        COUNT(DISTINCT cc.content_id) as content_count,
        COUNT(DISTINCT a.assignment_id) as assignment_count,
        COUNT(DISTINCT e.exam_id) as exam_count
    FROM courses c
    LEFT JOIN course_enrollments ce ON c.course_id = ce.course_id AND ce.status = 'active'
    LEFT JOIN course_content cc ON c.course_id = cc.course_id
    LEFT JOIN assignments a ON c.course_id = a.course_id
    LEFT JOIN exams e ON c.course_id = e.course_id
    WHERE c.course_id = ?
", [$course_id]);

// Get enrolled students
$students = fetchAll($pdo, "
    SELECT u.user_id, u.full_name, u.username, u.email, ce.enrolled_at, ce.status
    FROM course_enrollments ce
    JOIN users u ON ce.student_id = u.user_id
    WHERE ce.course_id = ?
    ORDER BY u.full_name
", [$course_id]);

// Get content
$contents = fetchAll($pdo, "
    SELECT * FROM course_content
    WHERE course_id = ?
    ORDER BY content_order, created_at
", [$course_id]);

// Get assignments
$assignments = fetchAll($pdo, "
    SELECT a.*, 
           COUNT(DISTINCT asub.submission_id) as submission_count
    FROM assignments a
    LEFT JOIN assignment_submissions asub ON a.assignment_id = asub.assignment_id
    WHERE a.course_id = ?
    GROUP BY a.assignment_id
    ORDER BY a.due_date DESC
", [$course_id]);

// Get exams
$exams = fetchAll($pdo, "
    SELECT e.*,
           COUNT(DISTINCT ea.attempt_id) as attempt_count
    FROM exams e
    LEFT JOIN exam_attempts ea ON e.exam_id = ea.exam_id
    WHERE e.course_id = ?
    GROUP BY e.exam_id
    ORDER BY e.created_at DESC
", [$course_id]);

include '../includes/header.php';
?>

<main class="container my-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard.php">แผงควบคุม</a></li>
            <li class="breadcrumb-item active">รายละเอียดรายวิชา</li>
        </ol>
    </nav>

    <!-- Course Header -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h4 class="mb-1">
                        <?php echo htmlspecialchars($course['course_code']); ?> - 
                        <?php echo htmlspecialchars($course['course_name']); ?>
                    </h4>
                    <small class="text-muted">
                        <i class="bi bi-person"></i> อาจารย์: <?php echo htmlspecialchars($course['teacher_name']); ?>
                        (<?php echo htmlspecialchars($course['teacher_username']); ?>)
                    </small>
                </div>
                <div>
                    <?php if ($course['is_active']): ?>
                        <span class="badge bg-success">เปิดใช้งาน</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">ปิดใช้งาน</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php if ($course['course_description']): ?>
                <p class="mb-3"><?php echo nl2br(htmlspecialchars($course['course_description'])); ?></p>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-4">
                    <small class="text-muted d-block">ปีการศึกษา</small>
                    <strong><?php echo htmlspecialchars($course['academic_year']); ?></strong>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">ภาคเรียน</small>
                    <strong>
                        <?php 
                        echo match($course['semester']) {
                            '1' => 'ภาคเรียนที่ 1',
                            '2' => 'ภาคเรียนที่ 2',
                            'summer' => 'ภาคฤดูร้อน',
                            default => 'ไม่ทราบ'
                        };
                        ?>
                    </strong>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">สร้างเมื่อ</small>
                    <strong><?php echo date('d/m/Y', strtotime($course['created_at'])); ?></strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <h3 class="text-primary"><?php echo $stats['student_count']; ?></h3>
                    <small class="text-muted">นักเรียน</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <h3 class="text-success"><?php echo $stats['content_count']; ?></h3>
                    <small class="text-muted">เนื้อหา</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <h3 class="text-warning"><?php echo $stats['assignment_count']; ?></h3>
                    <small class="text-muted">งาน</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <h3 class="text-info"><?php echo $stats['exam_count']; ?></h3>
                    <small class="text-muted">ข้อสอบ</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#students">
                <i class="bi bi-people"></i> นักเรียน (<?php echo count($students); ?>)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#content">
                <i class="bi bi-file-text"></i> เนื้อหา (<?php echo count($contents); ?>)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#assignments">
                <i class="bi bi-clipboard"></i> งาน (<?php echo count($assignments); ?>)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#exams">
                <i class="bi bi-journal-check"></i> ข้อสอบ (<?php echo count($exams); ?>)
            </a>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content">
        <!-- Students Tab -->
        <div class="tab-pane fade show active" id="students">
            <div class="card">
                <div class="card-body">
                    <?php if (empty($students)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 4rem; opacity: 0.3;"></i>
                            <p class="text-muted mt-3">ยังไม่มีนักเรียนลงทะเบียน</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>ชื่อ-นามสกุล</th>
                                        <th>ชื่อผู้ใช้</th>
                                        <th>อีเมล</th>
                                        <th>ลงทะเบียนเมื่อ</th>
                                        <th>สถานะ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $index => $student): ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($student['username']); ?></td>
                                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($student['enrolled_at'])); ?></td>
                                            <td>
                                                <?php if ($student['status'] === 'active'): ?>
                                                    <span class="badge bg-success">กำลังเรียน</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">เรียนจบ</span>
                                                <?php endif; ?>
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

        <!-- Content Tab -->
        <div class="tab-pane fade" id="content">
            <div class="card">
                <div class="card-body">
                    <?php if (empty($contents)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 4rem; opacity: 0.3;"></i>
                            <p class="text-muted mt-3">ยังไม่มีเนื้อหา</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($contents as $content): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <?php
                                                $icon = match($content['content_type']) {
                                                    'document' => 'file-earmark-pdf',
                                                    'video' => 'play-circle',
                                                    'link' => 'link-45deg',
                                                    default => 'file-text'
                                                };
                                                ?>
                                                <i class="bi bi-<?php echo $icon; ?>"></i>
                                                <?php echo htmlspecialchars($content['title']); ?>
                                            </h6>
                                            <?php if ($content['description']): ?>
                                                <p class="mb-1 text-muted small">
                                                    <?php echo htmlspecialchars($content['description']); ?>
                                                </p>
                                            <?php endif; ?>
                                            <small class="text-muted">
                                                อัปโหลดเมื่อ: <?php echo date('d/m/Y', strtotime($content['created_at'])); ?>
                                            </small>
                                        </div>
                                        <div>
                                            <?php if ($content['is_published']): ?>
                                                <span class="badge bg-success">เผยแพร่</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">ไม่เผยแพร่</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Assignments Tab -->
        <div class="tab-pane fade" id="assignments">
            <div class="card">
                <div class="card-body">
                    <?php if (empty($assignments)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 4rem; opacity: 0.3;"></i>
                            <p class="text-muted mt-3">ยังไม่มีงานที่มอบหมาย</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ชื่องาน</th>
                                        <th>กำหนดส่ง</th>
                                        <th>คะแนนเต็ม</th>
                                        <th>จำนวนที่ส่ง</th>
                                        <th>สถานะ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assignments as $assignment): ?>
                                        <?php $isOverdue = strtotime($assignment['due_date']) < time(); ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                                            <td class="<?php echo $isOverdue ? 'text-danger' : ''; ?>">
                                                <?php echo date('d/m/Y H:i', strtotime($assignment['due_date'])); ?>
                                            </td>
                                            <td><?php echo $assignment['max_score']; ?></td>
                                            <td><?php echo $assignment['submission_count']; ?> งาน</td>
                                            <td>
                                                <?php if ($isOverdue): ?>
                                                    <span class="badge bg-danger">เลยกำหนด</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">กำลังเปิดรับ</span>
                                                <?php endif; ?>
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

        <!-- Exams Tab -->
        <div class="tab-pane fade" id="exams">
            <div class="card">
                <div class="card-body">
                    <?php if (empty($exams)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 4rem; opacity: 0.3;"></i>
                            <p class="text-muted mt-3">ยังไม่มีข้อสอบ</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ชื่อข้อสอบ</th>
                                        <th>ประเภท</th>
                                        <th>คะแนนเต็ม</th>
                                        <th>เวลา</th>
                                        <th>จำนวนที่สอบ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($exams as $exam): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($exam['exam_name']); ?></td>
                                            <td>
                                                <?php
                                                $typeText = match($exam['exam_type']) {
                                                    'pretest' => 'Pre-test',
                                                    'posttest' => 'Post-test',
                                                    'quiz' => 'Quiz',
                                                    'midterm' => 'Midterm',
                                                    'final' => 'Final',
                                                    default => 'ทั่วไป'
                                                };
                                                $typeClass = match($exam['exam_type']) {
                                                    'pretest' => 'bg-info',
                                                    'posttest' => 'bg-success',
                                                    'quiz' => 'bg-warning',
                                                    'midterm' => 'bg-primary',
                                                    'final' => 'bg-danger',
                                                    default => 'bg-secondary'
                                                };
                                                ?>
                                                <span class="badge <?php echo $typeClass; ?>"><?php echo $typeText; ?></span>
                                            </td>
                                            <td><?php echo $exam['total_points']; ?></td>
                                            <td><?php echo $exam['duration_minutes']; ?> นาที</td>
                                            <td><?php echo $exam['attempt_count']; ?> ครั้ง</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
