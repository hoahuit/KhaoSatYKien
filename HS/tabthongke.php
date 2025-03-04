<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$user_id = $_SESSION["user_id"];
$topic_id = isset($_GET['topic']) ? (int)$_GET['topic'] : null;

if (!$topic_id) {
    header("Location: survey.php");
    exit();
}

// Lấy thông tin loại người dùng
$sql_user = "SELECT LoaiNguoiDung FROM TaiKhoan WHERE MaTK = ?";
$stmt_user = sqlsrv_query($conn, $sql_user, array($user_id));
if ($stmt_user === false) {
    die(print_r(sqlsrv_errors(), true));
}
$user_info = sqlsrv_fetch_array($stmt_user, SQLSRV_FETCH_ASSOC);
$user_type = $user_info['LoaiNguoiDung'];

// Lấy danh sách câu hỏi theo chủ đề
$sql_questions = "SELECT ch.IdCauHoi, ch.NoiDungCauHoi, lch.ChuDe, lch.MaLoaiCauHoi 
                 FROM CauHoi ch 
                 JOIN LoaiCauHoi lch ON ch.MaLoaiCauHoi = lch.MaLoaiCauHoi 
                 WHERE ch.MaLoaiCauHoi = ?
                 ORDER BY ch.IdCauHoi";
$stmt_questions = sqlsrv_query($conn, $sql_questions, array($topic_id));
if ($stmt_questions === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Lấy tất cả câu hỏi vào một mảng
$questions = array();
while ($question = sqlsrv_fetch_array($stmt_questions, SQLSRV_FETCH_ASSOC)) {
    $questions[] = $question;
}

if (empty($questions)) {
    header("Location: dashboard.php");
    exit();
}

$fixed_answers = array(
    array('IdPhuongAn' => 1, 'NoiDungTraLoi' => 'Hoàn toàn không đồng ý', 'MucDoDanhGia' => 'Mức độ 1'),
    array('IdPhuongAn' => 2, 'NoiDungTraLoi' => 'Không đồng ý', 'MucDoDanhGia' => 'Mức độ 2'),
    array('IdPhuongAn' => 3, 'NoiDungTraLoi' => 'Đồng ý một phần', 'MucDoDanhGia' => 'Mức độ 3'),
    array('IdPhuongAn' => 4, 'NoiDungTraLoi' => 'Đồng ý', 'MucDoDanhGia' => 'Mức độ 4'),
    array('IdPhuongAn' => 5, 'NoiDungTraLoi' => 'Hoàn toàn đồng ý', 'MucDoDanhGia' => 'Mức độ 5')
);

// Lấy dữ liệu thống kê cho mỗi câu hỏi
$statistics = array();
foreach ($questions as $question) {
    $question_id = $question['IdCauHoi'];
    
    // SQL để lấy số lượng người chọn mỗi phương án cho câu hỏi này
    $sql_stats = "SELECT pa.IdPhuongAn, COUNT(ks.IdPhuongAn) as SoLuong
                 FROM (
                     SELECT 1 as IdPhuongAn UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5
                 ) pa
                 LEFT JOIN KhaoSatSV ks ON pa.IdPhuongAn = ks.IdPhuongAn AND ks.IdCauHoi = ?
                 GROUP BY pa.IdPhuongAn
                 ORDER BY pa.IdPhuongAn";
    
    $stmt_stats = sqlsrv_query($conn, $sql_stats, array($question_id));
    if ($stmt_stats === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    
    $answers_stats = array();
    $total_responses = 0;
    
    // Lấy số lượng người chọn mỗi phương án
    while ($row = sqlsrv_fetch_array($stmt_stats, SQLSRV_FETCH_ASSOC)) {
        $answers_stats[$row['IdPhuongAn']] = $row;
        $total_responses += $row['SoLuong'];
    }
    
    // Tính phần trăm cho mỗi phương án
    foreach ($fixed_answers as $answer) {
        $id = $answer['IdPhuongAn'];
        if (isset($answers_stats[$id])) {
            $count = $answers_stats[$id]['SoLuong'];
            $percentage = $total_responses > 0 ? round(($count / $total_responses) * 100, 1) : 0;
            $answers_stats[$id]['PhanTram'] = $percentage;
            $answers_stats[$id]['NoiDungTraLoi'] = $answer['NoiDungTraLoi'];
        } else {
            $answers_stats[$id] = array(
                'IdPhuongAn' => $id,
                'NoiDungTraLoi' => $answer['NoiDungTraLoi'],
                'SoLuong' => 0,
                'PhanTram' => 0
            );
        }
    }
    
    // Tính tổng phần trăm đồng ý và không đồng ý
    $agree_percentage = 0;
    $disagree_percentage = 0;
    $neutral_percentage = 0;
    
    if (isset($answers_stats[4]) && isset($answers_stats[5])) {
        $agree_percentage = $answers_stats[4]['PhanTram'] + $answers_stats[5]['PhanTram'];
    }
    
    if (isset($answers_stats[1]) && isset($answers_stats[2])) {
        $disagree_percentage = $answers_stats[1]['PhanTram'] + $answers_stats[2]['PhanTram'];
    }
    
    if (isset($answers_stats[3])) {
        $neutral_percentage = $answers_stats[3]['PhanTram'];
    }
    
    $statistics[$question_id] = array(
        'answers' => $answers_stats,
        'total' => $total_responses,
        'agree_percentage' => $agree_percentage,
        'disagree_percentage' => $disagree_percentage,
        'neutral_percentage' => $neutral_percentage
    );
}

ob_start();
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <a href="survey.php" class="text-decoration-none text-muted">
                <i class="fas fa-arrow-left me-2"></i>
            </a>
            Thống kê khảo sát: <?php echo htmlspecialchars($questions[0]['ChuDe']); ?>
        </h4>
    </div>

    <?php foreach ($questions as $index => $question): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title text-primary mb-4">
                    Câu hỏi #<?php echo $question['IdCauHoi']; ?>: <?php echo htmlspecialchars($question['NoiDungCauHoi']); ?>
                </h5>

                <div class="stats-summary mb-3">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h5>Đồng ý</h5>
                                    <h2><?php echo $statistics[$question['IdCauHoi']]['agree_percentage']; ?>%</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h5>Trung lập</h5>
                                    <h2><?php echo $statistics[$question['IdCauHoi']]['neutral_percentage']; ?>%</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h5>Không đồng ý</h5>
                                    <h2><?php echo $statistics[$question['IdCauHoi']]['disagree_percentage']; ?>%</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="stats-detail">
                    <h6 class="mb-3">Chi tiết phản hồi (Tổng số: <?php echo $statistics[$question['IdCauHoi']]['total']; ?> người)</h6>
                    
                    <?php foreach ($fixed_answers as $answer): 
                        $answer_id = $answer['IdPhuongAn'];
                        $stats = $statistics[$question['IdCauHoi']]['answers'][$answer_id];
                        $percentage = $stats['PhanTram'];
                        $count = $stats['SoLuong'];
                        
                        // Xác định màu sắc dựa trên loại câu trả lời
                        $bar_color = '';
                        if ($answer_id <= 2) {
                            $bar_color = 'bg-danger';
                        } elseif ($answer_id == 3) {
                            $bar_color = 'bg-warning';
                        } else {
                            $bar_color = 'bg-success';
                        }
                    ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span><?php echo htmlspecialchars($answer['NoiDungTraLoi']); ?></span>
                                <span><?php echo $count; ?> người (<?php echo $percentage; ?>%)</span>
                            </div>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar <?php echo $bar_color; ?>" role="progressbar" 
                                     style="width: <?php echo $percentage; ?>%" 
                                     aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100">
                                    <?php echo $percentage; ?>%
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<style>
.progress-bar {
    transition: width 1s ease-in-out;
    font-weight: bold;
    text-shadow: 1px 1px 1px rgba(0,0,0,0.3);
}
.stats-summary .card {
    transition: transform 0.3s;
}
.stats-summary .card:hover {
    transform: translateY(-5px);
}
</style>

<?php
$content = ob_get_clean();
$pageTitle = "Thống kê khảo sát - " . htmlspecialchars($questions[0]['ChuDe']);
include 'layout.php';
?> 