<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ./HS/index.php");
    exit();
}

include 'config.php';
if ($conn === false) {
    die("Connection failed: " . print_r(sqlsrv_errors(), true));
}

// Xử lý form khi submit để thêm loại câu hỏi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $question_type_name = $_POST['question_type_name'];

    // Thêm loại câu hỏi vào cơ sở dữ liệu
    $sql_insert = "INSERT INTO LoaiCauHoi (ChuDe) VALUES (?)";
    $params_insert = array($question_type_name);
    $stmt_insert = sqlsrv_query($conn, $sql_insert, $params_insert);

    if ($stmt_insert === false) {
        die("Failed to insert question type: " . print_r(sqlsrv_errors(), true));
    } else {
        // Thêm thông báo thành công
        $success_message = "Thêm loại câu hỏi thành công!";
    }
}

// Truy vấn lấy danh sách loại câu hỏi
$sql = "SELECT * FROM LoaiCauHoi ORDER BY MaLoaiCauHoi DESC";
$result = sqlsrv_query($conn, $sql);
if ($result === false) {
    die("Query failed: " . print_r(sqlsrv_errors(), true));
}

ob_start();
?>

<style>
    body {
        background-color: #f5f8fa;
        font-family: 'Roboto', sans-serif;
    }
    .page-title {
        text-align: center;
        margin-bottom: 30px;
        color: #343a40;
        font-size: 2.5rem;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        border-bottom: 2px solid #28a745;
        padding-bottom: 10px;
    }
    .card {
        margin: 20px auto;
        max-width: 800px;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        background-color: #ffffff;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 20px rgba(0, 0, 0, 0.15);
    }
    .btn-submit {
        width: 100%;
        background-color: #28a745;
        color: white;
        border: none;
        border-radius: 5px;
        padding: 12px;
        font-size: 18px;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .btn-submit:hover {
        background-color: #218838;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .btn-submit:active {
        transform: translateY(0);
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        font-weight: bold;
        color: #495057;
        margin-bottom: 8px;
        display: block;
    }
    .form-control {
        border-radius: 5px;
        border: 1px solid #ced4da;
        padding: 12px;
        width: 100%;
        font-size: 16px;
        transition: all 0.3s ease;
    }
    .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        outline: none;
    }
    .table-responsive {
        overflow-x: auto;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }
    th, td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #e9ecef;
    }
    th {
        background-color: #f8f9fa;
        color: #495057;
        font-weight: 600;
    }
    tr:hover {
        background-color: #f8f9fa;
    }
    .action-links a {
        display: inline-block;
        margin-right: 10px;
        color: #4e73df;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    .action-links a:hover {
        color: #2e59d9;
        transform: translateY(-2px);
    }
    .action-links a.delete {
        color: #e74a3b;
    }
    .action-links a.delete:hover {
        color: #c72a1c;
    }
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 15px;
    }
    .card-header h3 {
        margin: 0;
        color: #343a40;
        font-weight: 600;
    }
    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 5px;
    }
    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    .badge {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 600;
        text-align: center;
    }
    .badge-primary {
        background-color: #4e73df;
        color: white;
    }
</style>

<header>
    <h1 style="text-align: center; color:White; font-size: 2.5rem; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #28a745; padding-bottom: 10px;">Quản Lý Loại Câu Hỏi</h1>
</header>

<?php if(isset($success_message)): ?>
<div class="card">
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-plus-circle"></i> Thêm Loại Câu Hỏi Mới</h3>
    </div>
    <form method="POST" action="">
        <div class="form-group">
            <label for="question_type_name">Tên loại câu hỏi:</label>
            <input type="text" name="question_type_name" id="question_type_name" required class="form-control" placeholder="Nhập tên loại câu hỏi...">
        </div>
        <button type="submit" class="btn-submit">
            <i class="fas fa-save"></i> Thêm Loại Câu Hỏi
        </button>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-list"></i> Danh Sách Loại Câu Hỏi</h3>
        <span class="badge badge-primary">
            <?php 
            $row_count = 0;
            if ($result) {
                $temp_result = sqlsrv_query($conn, "SELECT COUNT(*) AS count FROM LoaiCauHoi");
                if ($temp_result) {
                    $count_row = sqlsrv_fetch_array($temp_result, SQLSRV_FETCH_ASSOC);
                    $row_count = $count_row['count'];
                    sqlsrv_free_stmt($temp_result);
                }
            }
            echo $row_count . " loại câu hỏi";
            ?>
        </span>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th width="10%">ID</th>
                    <th width="60%">Tên Loại Câu Hỏi</th>
                    <th width="30%">Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $has_records = false;
                while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) { 
                    $has_records = true;
                ?>
                    <tr>
                        <td><?php echo $row['MaLoaiCauHoi']; ?></td>
                        <td><?php echo $row['ChuDe']; ?></td>
                        <td class="action-links">
                            <a href="edit_question_type.php?id=<?php echo $row['MaLoaiCauHoi']; ?>" title="Sửa">
                                <i class="fas fa-edit"></i> Sửa
                            </a>
                            <a href="delete_question_type.php?id=<?php echo $row['MaLoaiCauHoi']; ?>" 
                               class="delete"
                               onclick="return confirm('Bạn có chắc chắn muốn xóa loại câu hỏi này?')"
                               title="Xóa">
                               <i class="fas fa-trash"></i> Xóa
                            </a>
                        </td>
                    </tr>
                <?php } ?>
                
                <?php if (!$has_records): ?>
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 30px;">
                            <i class="fas fa-info-circle" style="font-size: 24px; color: #6c757d;"></i>
                            <p style="margin-top: 10px; color: #6c757d;">Chưa có loại câu hỏi nào. Hãy thêm loại câu hỏi mới!</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';

// Giải phóng tài nguyên
sqlsrv_free_stmt($result);
sqlsrv_close($conn);
?> 