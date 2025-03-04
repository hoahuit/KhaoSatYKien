<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ./HS/index.php");
    exit();
}

include 'config.php';

// Lấy thông tin loại câu hỏi cần sửa
$id = isset($_GET['id']) ? $_GET['id'] : null;
if (!$id) {
    header("Location: quanlyloaicauhoi.php");
    exit();
}

// Xử lý form submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $question_type_name = $_POST['question_type_name'];
    
    $sql_update = "UPDATE LoaiCauHoi SET ChuDe = ? WHERE MaLoaiCauHoi = ?";
    $params = array($question_type_name, $id);
    $stmt = sqlsrv_query($conn, $sql_update, $params);

    if ($stmt === false) {
        $error_message = "Lỗi cập nhật: " . print_r(sqlsrv_errors(), true);
    } else {
        header("Location: quanlyloaicauhoi.php?success=updated");
        exit();
    }
}

// Lấy thông tin hiện tại của loại câu hỏi
$sql = "SELECT * FROM LoaiCauHoi WHERE MaLoaiCauHoi = ?";
$params = array($id);
$result = sqlsrv_query($conn, $sql, $params);
$question_type = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);

if (!$question_type) {
    header("Location: quanlyloaicauhoi.php");
    exit();
}

ob_start();
?>

<header>
    <h1>Sửa Loại Câu Hỏi </h1>
</header>

<style>
    .edit-form-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .page-header {
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 2px solid var(--primary-color);
    }
    
    .page-header h2 {
        color: var(--dark-color);
        font-size: 24px;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .form-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: var(--shadow-md);
        padding: 30px;
        margin-bottom: 30px;
    }
    
    .form-group {
        margin-bottom: 25px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: var(--gray-800);
    }
    
    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid var(--gray-300);
        border-radius: 8px;
        font-size: 15px;
        transition: all 0.3s;
    }
    
    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.1);
        outline: none;
    }
    
    .btn-container {
        display: flex;
        gap: 15px;
        margin-top: 30px;
    }
    
    .btn {
        padding: 12px 25px;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
        border: none;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-primary {
        background: var(--primary-color);
        color: white;
    }
    
    .btn-primary:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
    }
    
    .btn-secondary {
        background: var(--gray-200);
        color: var(--gray-800);
    }
    
    .btn-secondary:hover {
        background: var(--gray-300);
        transform: translateY(-2px);
    }
    
    .error-message {
        color: var(--danger-color);
        background: rgba(231, 74, 59, 0.1);
        padding: 12px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
</style>

<div class="edit-form-container">
    <div class="page-header">
        <h2>
            <i class="fas fa-edit"></i>
            Chỉnh Sửa Loại Câu Hỏi
        </h2>
    </div>

    <?php if (isset($error_message)): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST" action="" id="editForm">
            <div class="form-group">
                <label for="question_type_name">Tên loại câu hỏi:</label>
                <input 
                    type="text" 
                    id="question_type_name" 
                    name="question_type_name" 
                    class="form-control"
                    value="<?php echo htmlspecialchars($question_type['ChuDe']); ?>"
                    required
                >
            </div>

            <div class="btn-container">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Lưu Thay Đổi
                </button>
                <a href="quanlyloaicauhoi.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Quay Lại
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('editForm').addEventListener('submit', function(e) {
    const questionTypeName = document.getElementById('question_type_name').value.trim();
    
    if (questionTypeName === '') {
        e.preventDefault();
        alert('Vui lòng nhập tên loại câu hỏi');
        return;
    }
    
    // Thêm loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';
    submitBtn.disabled = true;
});
</script>

<?php
$content = ob_get_clean();
include 'layout.php';

// Giải phóng tài nguyên
sqlsrv_free_stmt($result);
sqlsrv_close($conn);
?> 