<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

// Bật báo cáo lỗi để debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

$user_id = $_SESSION["user_id"];

// Xác định loại người dùng từ bảng TaiKhoan
$sql_user = "SELECT LoaiNguoiDung, MaSV FROM TaiKhoan WHERE MaTK = ?";
$stmt_user = sqlsrv_query($conn, $sql_user, array($user_id));
if ($stmt_user === false) {
    die(print_r(sqlsrv_errors(), true));
}
$user_info = sqlsrv_fetch_array($stmt_user, SQLSRV_FETCH_ASSOC);
$user_type = $user_info['LoaiNguoiDung']; // 1: Sinh viên, 2: Nhân viên

// Bắt đầu buffer đầu ra
ob_start();
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0">
                        <i class="fas fa-clipboard-list text-primary me-2"></i>
                        Danh sách bài khảo sát
                    </h3>
                    <p class="text-muted mt-2 mb-0">Vui lòng chọn một chủ đề khảo sát để bắt đầu</p>
                </div>
                <div class="d-none d-md-block">
                    <img src="https://cdn-icons-png.flaticon.com/512/2620/2620969.png" alt="Survey" class="survey-icon" height="60">
                </div>
            </div>
            <hr class="my-4">
        </div>
    </div>
    
    <div class="row g-4">
        <?php
        // Truy vấn danh sách chủ đề khảo sát
        $sql = "SELECT lch.MaLoaiCauHoi, lch.ChuDe,
        (SELECT COUNT(*) FROM CauHoi 
         WHERE MaLoaiCauHoi = lch.MaLoaiCauHoi 
         AND (ThoiGianHetHan IS NULL OR ThoiGianHetHan > GETDATE())) as TongSoCau,
        (SELECT COUNT(*) 
         FROM CauHoi ch 
         WHERE ch.MaLoaiCauHoi = lch.MaLoaiCauHoi 
         AND (ch.ThoiGianHetHan IS NULL OR ch.ThoiGianHetHan > GETDATE())
         AND ch.IdCauHoi IN (
            SELECT IdCauHoi FROM KhaoSatSV WHERE MaSV = ? AND ? = 1
         )
        ) as SoCauHoiDaLam
        FROM LoaiCauHoi lch
        WHERE EXISTS (
            SELECT 1 FROM CauHoi 
            WHERE MaLoaiCauHoi = lch.MaLoaiCauHoi 
            AND (ThoiGianHetHan IS NULL OR ThoiGianHetHan > GETDATE())
        )
        ORDER BY lch.MaLoaiCauHoi";
        
        $params = array(
            $user_type == 1 ? $user_info['MaSV'] : 0,
            $user_type

        );
        $stmt = sqlsrv_query($conn, $sql, $params);
        
        if ($stmt === false) {
            echo '<div class="alert alert-danger">';
            echo 'Lỗi truy vấn: ';
            print_r(sqlsrv_errors(), true);
            echo '</div>';
            die();
        }

        $hasTopics = false;
        while ($topic = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $hasTopics = true;
            $progress = ($topic['TongSoCau'] > 0) ? ($topic['SoCauHoiDaLam'] / $topic['TongSoCau']) * 100 : 0;
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 survey-card">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="survey-icon-wrapper me-3">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <h5 class="card-title mb-0"><?php echo htmlspecialchars($topic['ChuDe']); ?></h5>
                        </div>
                        
                        <div class="progress mb-3" style="height: 8px;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: <?php echo $progress; ?>%" 
                                 aria-valuenow="<?php echo $progress; ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="survey-stats">
                                <span class="text-muted">
                                    <i class="fas fa-tasks me-1"></i>
                                    <?php echo $topic['SoCauHoiDaLam']; ?>/<?php echo $topic['TongSoCau']; ?> câu
                                </span>
                            </div>
                            <a href="take_survey.php?topic=<?php echo $topic['MaLoaiCauHoi']; ?>" 
                               class="btn btn-primary btn-sm">
                                <i class="fas fa-arrow-right me-1"></i>Bắt đầu
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
        
        if (!$hasTopics) {
            ?>
            <div class="col-12">
                <div class="text-center py-5 empty-state">
                    <img src="assets/images/empty-survey.png" alt="No surveys" class="mb-4" height="120">
                    <h5 class="text-muted">Chưa có bài khảo sát nào</h5>
                    <p class="text-muted">Vui lòng quay lại sau</p>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</div>

<style>
.survey-card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    transition: all 0.3s ease;
}

.survey-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.survey-icon-wrapper {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background-color: #e8f3ff;
    display: flex;
    align-items: center;
    justify-content: center;
}

.survey-icon-wrapper i {
    color: #0d6efd;
    font-size: 1.2rem;
}

.survey-stats {
    font-size: 0.9rem;
}

.empty-state img {
    opacity: 0.5;
}

.progress {
    border-radius: 10px;
    background-color: #e9ecef;
}

.progress-bar {
    border-radius: 10px;
}

.survey-icon {
    opacity: 0.8;
}
</style>

<?php
$content = ob_get_clean();
$pageTitle = "Danh sách bài khảo sát";
include 'layout.php';
?>