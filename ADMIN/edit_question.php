<?php
session_start();
include 'config.php';

// Kiểm tra nếu có ID câu hỏi được truyền vào
if (!isset($_GET['id'])) {
    header('Location: quanlycauhoi.php');
    exit();
}

$question_id = $_GET['id'];

// Xử lý form khi submit để cập nhật câu hỏi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $question_content = $_POST['question_content'];
    $question_type_id = $_POST['question_type_id'];
    $expiry_date = date('Y-m-d H:i:s', strtotime($_POST['expiry_date']));

    // Cập nhật câu hỏi trong cơ sở dữ liệu
    $sql_update = "UPDATE CauHoi SET NoiDungCauHoi = ?, MaLoaiCauHoi = ?, ThoiGianHetHan = ? WHERE IdCauHoi = ?";
    $params_update = array($question_content, $question_type_id, $expiry_date, $question_id);
    $stmt_update = sqlsrv_query($conn, $sql_update, $params_update);

    if ($stmt_update === false) {
        $error_message = "Lỗi khi cập nhật câu hỏi: " . print_r(sqlsrv_errors(), true);
    } else {
        header('Location: quanlycauhoi.php?topic=' . $question_type_id);
        exit();
    }
}

// Lấy thông tin câu hỏi hiện tại
$sql = "SELECT * FROM CauHoi WHERE IdCauHoi = ?";
$params = array($question_id);
$result = sqlsrv_query($conn, $sql, $params);

if ($result === false || !($question = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC))) {
    header('Location: quanlycauhoi.php');
    exit();
}

// Lấy danh sách loại câu hỏi
$sql_types = "SELECT * FROM LoaiCauHoi";
$result_types = sqlsrv_query($conn, $sql_types);

ob_start();
?>

<div class="card fade-in">
    <h3 class="section-title"><i class="fas fa-edit"></i> Chỉnh Sửa Câu Hỏi</h3>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="question_content"><i class="fas fa-question-circle"></i> Nội dung câu hỏi:</label>
            <input type="text" name="question_content" id="question_content" required class="form-control" 
                   value="<?php echo htmlspecialchars($question['NoiDungCauHoi']); ?>">
        </div>

        <div class="form-group">
            <label for="question_type_id"><i class="fas fa-tag"></i> Loại câu hỏi:</label>
            <div class="topic-select">
                <select style="height: 50px;" name="question_type_id" id="question_type_id" class="form-control" required>
                    <?php while ($type = sqlsrv_fetch_array($result_types, SQLSRV_FETCH_ASSOC)) { ?>
                        <option value="<?php echo $type['MaLoaiCauHoi']; ?>" 
                                <?php echo ($type['MaLoaiCauHoi'] == $question['MaLoaiCauHoi']) ? 'selected' : ''; ?>>
                            <?php echo $type['ChuDe']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="expiry_date"><i class="fas fa-calendar"></i> Thời gian hết hạn:</label>
            <input type="datetime-local" name="expiry_date" id="expiry_date" required class="form-control"
                   value="<?php echo $question['ThoiGianHetHan']->format('Y-m-d\TH:i'); ?>">
        </div>

        <div class="action-buttons">
            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i> Lưu Thay Đổi
            </button>
            <a href="quanlycauhoi.php" class="btn-cancel">
                <i class="fas fa-times"></i> Hủy
            </a>
        </div>
    </form>
</div>

<style>
    .action-buttons {
        display: flex;
        gap: 15px;
        margin-top: 20px;
    }
    
    .btn-cancel {
        flex: 1;
        padding: 12px 20px;
        border-radius: 50px;
        text-align: center;
        text-decoration: none;
        font-weight: 600;
        font-size: 18px;
        letter-spacing: 1px;
        transition: all 0.3s ease;
        background: linear-gradient(135deg, #e74c3c, #c0392b);
        color: white;
        border: none;
        box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
    }
    
    .btn-cancel:hover {
        background: linear-gradient(135deg, #c0392b, #e74c3c);
        box-shadow: 0 7px 20px rgba(231, 76, 60, 0.5);
        transform: translateY(-2px);
        color: white;
    }
    
    .btn-submit {
        flex: 1;
    }
</style>

<?php
$content = ob_get_clean();
include 'layout.php';

// Giải phóng tài nguyên
if ($result && gettype($result) === 'resource') {
    sqlsrv_free_stmt($result);
}
if ($result_types && gettype($result_types) === 'resource') {
    sqlsrv_free_stmt($result_types);
}
sqlsrv_close($conn);
?> 