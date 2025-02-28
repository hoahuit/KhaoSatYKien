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

$sql_user = "SELECT LoaiNguoiDung, MaSV, MaNV FROM TaiKhoan WHERE MaTK = ?";
$stmt_user = sqlsrv_query($conn, $sql_user, array($user_id));
if ($stmt_user === false) {
    die(print_r(sqlsrv_errors(), true));
}
$user_info = sqlsrv_fetch_array($stmt_user, SQLSRV_FETCH_ASSOC);
$user_type = $user_info['LoaiNguoiDung'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $answers = isset($_POST["answers"]) ? $_POST["answers"] : array();
    
    foreach ($answers as $question_id => $selected_answer) {
        $params = array($question_id, $selected_answer);
        
        if ($user_type == 1) {
            $params[] = $user_info['MaSV'];
            $sql_insert = "INSERT INTO KhaoSatSV (IdCauHoi, IdPhuongAn, MaSV, ThoiGian) VALUES (?, ?, ?, GETDATE())";
        } else if ($user_type == 2) {
            $params[] = $user_info['MaNV'];
            $sql_insert = "INSERT INTO KhaoSatNV (IdCauHoi, IdPhuongAn, MaNV, ThoiGian) VALUES (?, ?, ?, GETDATE())";
        }

        $stmt_insert = sqlsrv_query($conn, $sql_insert, $params);
        if ($stmt_insert === false) {
            die(print_r(sqlsrv_errors(), true));
        }
    }

    header("Location: surveys.php");
    exit();
}

// Thay thế câu SQL lấy một câu hỏi bằng câu SQL lấy tất cả câu hỏi chưa trả lời
$sql_questions = "SELECT ch.IdCauHoi, ch.NoiDungCauHoi, lch.ChuDe, lch.MaLoaiCauHoi 
                 FROM CauHoi ch 
                 JOIN LoaiCauHoi lch ON ch.MaLoaiCauHoi = lch.MaLoaiCauHoi 
                 WHERE ch.MaLoaiCauHoi = ?
                 AND ch.IdCauHoi NOT IN (
                    SELECT IdCauHoi FROM KhaoSatSV WHERE MaSV = ? AND ? = 1
                    UNION
                    SELECT IdCauHoi FROM KhaoSatNV WHERE MaNV = ? AND ? = 2
                 )
                 ORDER BY ch.IdCauHoi";
$params = array($topic_id, $user_type == 1 ? $user_info['MaSV'] : 0, $user_type, $user_type == 2 ? $user_info['MaNV'] : 0, $user_type);
$stmt_questions = sqlsrv_query($conn, $sql_questions, $params);
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

ob_start();
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <a href="survey.php" class="text-decoration-none text-muted">
                <i class="fas fa-arrow-left me-2"></i>
            </a>
            <?php echo htmlspecialchars($questions[0]['ChuDe']); ?>
        </h4>
    </div>

    <form method="POST" action="">
        <?php foreach ($questions as $index => $question): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title text-primary mb-4">
                        Câu hỏi #<?php echo $question['IdCauHoi']; ?>: <?php echo htmlspecialchars($question['NoiDungCauHoi']); ?>
                    </h5>

                    <div class="mb-4">
                        <?php foreach ($fixed_answers as $answer): ?>
                            <div class="form-check mb-3">
                                <input class="form-check-input" 
                                       type="radio" 
                                       name="answers[<?php echo $question['IdCauHoi']; ?>]" 
                                       id="answer<?php echo $question['IdCauHoi']; ?>_<?php echo $answer['IdPhuongAn']; ?>" 
                                       value="<?php echo $answer['IdPhuongAn']; ?>" 
                                       required>
                                <label class="form-check-label" 
                                       for="answer<?php echo $question['IdCauHoi']; ?>_<?php echo $answer['IdPhuongAn']; ?>">
                                    <?php echo htmlspecialchars($answer['NoiDungTraLoi']) . " (" . htmlspecialchars($answer['MucDoDanhGia']) . ")"; ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-2"></i>Gửi tất cả câu trả lời
        </button>
    </form>
</div>

<style>
.form-check-input:checked {
    background-color: #007bff;
    border-color: #007bff;
}
.form-check-label {
    cursor: pointer;
}
</style>

<?php
$content = ob_get_clean();
$pageTitle = "Tham gia khảo sát - " . htmlspecialchars($questions[0]['ChuDe']);
include 'layout.php';
?>