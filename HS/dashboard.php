<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

// Thêm error reporting để debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Lấy thông tin người dùng và thống kê câu hỏi
$user_id = $_SESSION["user_id"];
$sql = "SELECT 
            t.LoaiNguoiDung, 
            t.MaSV, 
            t.TenDangNhap,
            (SELECT COUNT(*) FROM CauHoi) as TongSoCauHoi,
            (SELECT COUNT(*) FROM CauHoi WHERE ThoiGianHetHan > GETDATE()) as CauHoiConHan,
            (SELECT COUNT(*) FROM CauHoi WHERE ThoiGianHetHan <= GETDATE()) as CauHoiHetHan,
            (SELECT COUNT(*) FROM KhaoSatSV ks 
             INNER JOIN CauHoi ch ON ks.IdCauHoi = ch.IdCauHoi 
             WHERE ks.MaSV = t.MaSV AND ch.ThoiGianHetHan > GETDATE()) as SoCauDaLam
        FROM TaiKhoan t 
        WHERE t.MaTK = ?";

$params = array($user_id);
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$row = sqlsrv_fetch_array($stmt);
if ($row === false) {
    die("Error fetching user type");
}

$user_type = $row["LoaiNguoiDung"];
$total_questions = $row["TongSoCauHoi"];
$active_questions = $row["CauHoiConHan"];
$expired_questions = $row["CauHoiHetHan"];
$answered_questions = $row["SoCauDaLam"];
$user_name = $row["TenDangNhap"];
$completion_rate = ($active_questions > 0) ? round(($answered_questions / $active_questions) * 100) : 0;

ob_start();
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_name); ?>&background=random" 
                             class="rounded-circle me-3" style="width: 60px; height: 60px;">
                        <div>
                            <h4 class="mb-1">Xin chào, <?php echo htmlspecialchars($user_name); ?>!</h4>
                            <p class="mb-0">Chào mừng bạn quay trở lại với hệ thống khảo sát</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card hover-card h-100">
                <div class="card-body text-center">
                    <img src="https://cdn-icons-png.flaticon.com/512/3126/3126647.png" 
                         alt="Total Questions" style="width: 64px; margin-bottom: 15px;">
                    <h5 class="card-title">Tổng số câu hỏi</h5>
                    <h2 class="text-primary"><?php echo $total_questions; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card hover-card h-100">
                <div class="card-body text-center">
                    <img src="https://cdn-icons-png.flaticon.com/512/3094/3094700.png" 
                         alt="Active Questions" style="width: 64px; margin-bottom: 15px;">
                    <h5 class="card-title">Câu hỏi còn hạn</h5>
                    <h2 class="text-success"><?php echo $active_questions; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card hover-card h-100">
                <div class="card-body text-center">
                    <img src="https://cdn-icons-png.flaticon.com/512/1441/1441333.png" 
                         alt="Answered Questions" style="width: 64px; margin-bottom: 15px;">
                    <h5 class="card-title">Đã trả lời</h5>
                    <h2 class="text-info"><?php echo $answered_questions; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card hover-card h-100">
                <div class="card-body text-center">
                    <img src="https://cdn-icons-png.flaticon.com/512/6188/6188804.png" 
                         alt="Expired Questions" style="width: 64px; margin-bottom: 15px;">
                    <h5 class="card-title">Câu hỏi hết hạn</h5>
                    <h2 class="text-warning"><?php echo $expired_questions; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card hover-card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Tiến độ hoàn thành (Chỉ tính câu hỏi còn hạn)</h5>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" 
                             style="width: <?php echo $completion_rate; ?>%"
                             aria-valuenow="<?php echo $completion_rate; ?>" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            <?php echo $completion_rate; ?>%
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card hover-card">
                <div class="card-body text-center">
                    <img src="https://cdn-icons-png.flaticon.com/512/2620/2620178.png" 
                         alt="Survey" style="width: 100px; margin-bottom: 15px;">
                    <h5 class="card-title">Bắt đầu khảo sát</h5>
                    <a href="surveys.php" class="btn btn-primary mt-2">
                        <i class="fas fa-play me-2"></i>Làm khảo sát ngay
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hover-card {
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.progress {
    border-radius: 15px;
    background-color: #e9ecef;
}

.progress-bar {
    border-radius: 15px;
    background-image: linear-gradient(45deg, #007bff, #00bcd4);
}

.card {
    border-radius: 15px;
}
</style>

<?php
$content = ob_get_clean();
$pageTitle = 'Dashboard - Hệ thống Khảo sát';
include 'layout.php';
?>
