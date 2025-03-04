<?php
session_start();
include 'config.php';;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Xử lý form khi submit để thêm câu hỏi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['question_content'])) {
        $question_content = $_POST['question_content'];
        $question_type_id = $_POST['question_type_id'];
        
        // Format expiry date to SQL Server datetime format
        $expiry_date = date('Y-m-d H:i:s', strtotime($_POST['expiry_date']));

        // Thêm câu hỏi vào cơ sở dữ liệu
        $sql_insert = "INSERT INTO CauHoi (NoiDungCauHoi, MaLoaiCauHoi, ThoiGianHetHan) VALUES (?, ?, ?)";
        $params_insert = array($question_content, $question_type_id, $expiry_date);
        $stmt_insert = sqlsrv_query($conn, $sql_insert, $params_insert);

        if ($stmt_insert === false) {
            die("Failed to insert question: " . print_r(sqlsrv_errors(), true));
        } else {
            $success_message = "Thêm câu hỏi thành công!";
        }
    } elseif (isset($_FILES['excel_file'])) {
        // Xử lý upload file Excel/CSV
        $file = $_FILES['excel_file'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Kiểm tra định dạng file
        if ($file_ext == 'xlsx' || $file_ext == 'xls' || $file_ext == 'csv') {
            // Thư mục lưu file tạm
            $upload_dir = 'uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $upload_file = $upload_dir . basename($file_name);
            
            if (move_uploaded_file($file_tmp, $upload_file)) {
                // Xử lý file CSV
                if ($file_ext == 'csv') {
                    $success_count = 0;
                    $error_count = 0;
                    $error_messages = [];
                    
                    // Mở file CSV để đọc
                    if (($handle = fopen($upload_file, "r")) !== FALSE) {
                        // Bỏ qua dòng tiêu đề
                        $header = fgetcsv($handle, 1000, ",");
                        
                        // Đọc từng dòng dữ liệu
                        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                            if (count($data) >= 3) {
                                $question_content = $data[0];
                                $question_type_id = $data[1];
                                
                                // Format expiry date from CSV
                                $expiry_date = date('Y-m-d H:i:s', strtotime($data[2]));
                                
                                // Kiểm tra dữ liệu hợp lệ
                                if (!empty($question_content) && !empty($question_type_id) && !empty($expiry_date)) {
                                    // Thêm câu hỏi vào cơ sở dữ liệu
                                    $sql_insert = "INSERT INTO CauHoi (NoiDungCauHoi, MaLoaiCauHoi, ThoiGianHetHan) VALUES (?, ?, ?)";
                                    $params_insert = array($question_content, $question_type_id, $expiry_date);
                                    $stmt_insert = sqlsrv_query($conn, $sql_insert, $params_insert);
                                    
                                    if ($stmt_insert === false) {
                                        $error_count++;
                                        $error_messages[] = "Lỗi khi thêm câu hỏi: " . $question_content . " - " . print_r(sqlsrv_errors(), true);
                                    } else {
                                        $success_count++;
                                    }
                                } else {
                                    $error_count++;
                                    $error_messages[] = "Dữ liệu không hợp lệ: " . implode(", ", $data);
                                }
                            } else {
                                $error_count++;
                                $error_messages[] = "Định dạng dòng không đúng: " . implode(", ", $data);
                            }
                        }
                        fclose($handle);
                        
                        // Tạo thông báo kết quả
                        if ($success_count > 0) {
                            $import_message = "Đã nhập thành công $success_count câu hỏi.";
                            $import_status = "success";
                            if ($error_count > 0) {
                                $import_message .= " Có $error_count lỗi xảy ra.";
                            }
                        } else {
                            $import_message = "Không có câu hỏi nào được nhập thành công. Có $error_count lỗi xảy ra.";
                            $import_status = "danger";
                        }
                        
                        // Lưu chi tiết lỗi vào session để hiển thị nếu cần
                        if (!empty($error_messages)) {
                            $_SESSION['import_errors'] = $error_messages;
                        }
                    } else {
                        $import_message = "Không thể đọc file CSV. Vui lòng kiểm tra lại.";
                        $import_status = "danger";
                    }
                } else {
                    // Thông báo cho file Excel (xlsx, xls)
                    $import_message = "Hệ thống hiện chỉ hỗ trợ xử lý file CSV. Vui lòng chuyển đổi file Excel sang định dạng CSV.";
                    $import_status = "warning";
                }
                
                // Xóa file tạm sau khi xử lý
                unlink($upload_file);
            } else {
                $import_message = "Không thể upload file. Vui lòng thử lại.";
                $import_status = "danger";
            }
        } else {
            $import_message = "Chỉ chấp nhận file Excel (.xlsx, .xls) hoặc CSV (.csv)";
            $import_status = "danger";
        }
    }
}

// Truy vấn lấy danh sách loại câu hỏi để hiển thị trong dropdown
$sql_types = "SELECT * FROM LoaiCauHoi";
$result_types = sqlsrv_query($conn, $sql_types);
if ($result_types === false) {
    die("Query failed: " . print_r(sqlsrv_errors(), true));
}

// Kiểm tra nếu có chủ đề được chọn
$selected_topic = isset($_GET['topic']) ? $_GET['topic'] : null;

// Truy vấn lấy danh sách câu hỏi theo chủ đề được chọn
if ($selected_topic) {
    $sql = "SELECT ch.IdCauHoi, ch.NoiDungCauHoi, ch.MaLoaiCauHoi, ch.ThoiGianHetHan, lh.ChuDe 
            FROM CauHoi ch 
            JOIN LoaiCauHoi lh ON ch.MaLoaiCauHoi = lh.MaLoaiCauHoi 
            WHERE lh.MaLoaiCauHoi = ?
            ORDER BY lh.ChuDe";
    $params = array($selected_topic);
    $result = sqlsrv_query($conn, $sql, $params);
} else {
    // Nếu không có chủ đề được chọn, không lấy câu hỏi nào
    $result = false;
}

$current_topic = '';

// Thêm hàm tạo file mẫu CSV thay vì Excel
function createCSVTemplate() {
    $templatePath = 'templates/mau_nhap_cau_hoi.csv';
    
    // Kiểm tra nếu thư mục templates chưa tồn tại thì tạo mới
    if (!file_exists('templates')) {
        mkdir('templates', 0777, true);
    }
    
    // Kiểm tra nếu file mẫu chưa tồn tại thì tạo mới
    if (!file_exists($templatePath)) {
        $data = array(
            array('Nội dung câu hỏi', 'Mã loại câu hỏi', 'Thời gian hết hạn'),
            array('Đây là câu hỏi mẫu 1?', '1', '2024-12-31 23:59:59'),
            array('Đây là câu hỏi mẫu 2?', '2', '2024-12-31 23:59:59')
        );
        
        $fp = fopen($templatePath, 'w');
        
        foreach ($data as $row) {
            fputcsv($fp, $row);
        }
        
        fclose($fp);
        
        return true;
    }
    
    return false;
}

// Gọi hàm tạo file mẫu
createCSVTemplate();

ob_start();
?>

<style>
    body {
        background-color: #f8f9fa;
        font-family: 'Roboto', sans-serif;
        color: #333;
    }
    .page-title {
        text-align: center;
        margin-bottom: 30px;
        color: #2c3e50;
        font-size: 2.5rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 2px;
        border-bottom: 3px solid #3498db;
        padding-bottom: 15px;
        position: relative;
    }
    .page-title:after {
        content: '';
        position: absolute;
        bottom: -3px;
        left: 50%;
        width: 100px;
        height: 3px;
        background-color: #e74c3c;
        transform: translateX(-50%);
    }
    .card {
        margin: 25px auto;
        max-width: 800px;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        background-color: #ffffff;
        transition: all 0.3s ease;
        border: none;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    }
    .btn-submit {
        width: 100%;
        background: linear-gradient(135deg, #3498db, #2980b9);
        color: white;
        border: none;
        border-radius: 50px;
        padding: 12px 20px;
        font-size: 18px;
        font-weight: 600;
        letter-spacing: 1px;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        cursor: pointer;
    }
    .btn-submit:hover {
        background: linear-gradient(135deg, #2980b9, #3498db);
        box-shadow: 0 7px 20px rgba(52, 152, 219, 0.5);
        transform: translateY(-2px);
    }
    .btn-submit:active {
        transform: translateY(1px);
    }
    .form-group {
        margin-bottom: 25px;
    }
    .form-group label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 10px;
        display: block;
        font-size: 16px;
    }
    .form-control {
        border-radius: 10px;
        border: 2px solid #e0e0e0;
        padding: 12px 15px;
        font-size: 16px;
        transition: all 0.3s ease;
        background-color: #f9f9f9;
    }
    .form-control:focus {
        border-color: #3498db;
        box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        background-color: #fff;
    }
    .question-group {
        margin-bottom: 30px;
        padding: 20px;
        border-radius: 10px;
        background-color: #f8f9fa;
        box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.05);
    }
    .question-item {
        margin-bottom: 15px;
        padding: 15px;
        border-radius: 10px;
        background-color: #ffffff;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border-left: 4px solid #3498db;
    }
    .question-item:hover {
        transform: translateX(5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    .question-item p {
        margin: 8px 0;
        font-size: 16px;
    }
    .action-links {
        margin-top: 15px;
        display: flex;
        gap: 15px;
    }
    .action-links a {
        display: inline-flex;
        align-items: center;
        padding: 8px 15px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    .action-links a.edit-btn {
        color: #fff;
        background-color: #3498db;
    }
    .action-links a.delete-btn {
        color: #fff;
        background-color: #e74c3c;
    }
    .action-links a:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
    }
    .action-links a i {
        margin-right: 5px;
    }
    .nav-tabs {
        margin-bottom: 25px;
        border-bottom: none;
        display: flex;
        justify-content: center;
        gap: 10px;
    }
    .nav-tabs .nav-link {
        border: none;
        color: #7f8c8d;
        font-weight: 600;
        padding: 12px 25px;
        border-radius: 50px;
        transition: all 0.3s ease;
        background-color: #f1f2f6;
    }
    .nav-tabs .nav-link.active {
        color: #fff;
        background: linear-gradient(135deg, #3498db, #2980b9);
        box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
    }
    .nav-tabs .nav-link:hover:not(.active) {
        background-color: #e0e0e0;
        color: #2c3e50;
    }
    .tab-content {
        padding: 20px 0;
    }
    .alert {
        padding: 15px 20px;
        margin-bottom: 25px;
        border: none;
        border-radius: 10px;
        font-weight: 500;
        display: flex;
        align-items: center;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }
    .alert i {
        margin-right: 10px;
        font-size: 20px;
    }
    .alert-success {
        color: #155724;
        background-color: #d4edda;
        border-left: 4px solid #28a745;
    }
    .alert-danger {
        color: #721c24;
        background-color: #f8d7da;
        border-left: 4px solid #dc3545;
    }
    .section-title {
        font-size: 1.8rem;
        color: #2c3e50;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #3498db;
        position: relative;
    }
    .section-title:after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 50px;
        height: 2px;
        background-color: #e74c3c;
    }
    .topic-select {
        position: relative;
    }
    .topic-select select {
        appearance: none;
        padding-right: 30px;
    }
    .topic-select:after {
        content: '\f107';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
    }
    .empty-state {
        text-align: center;
        padding: 30px;
        color: #7f8c8d;
    }
    .empty-state i {
        font-size: 50px;
        margin-bottom: 15px;
        color: #bdc3c7;
    }
    .guide-list {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        margin-top: 20px;
    }
    .guide-list ol {
        padding-left: 20px;
    }
    .guide-list li {
        margin-bottom: 10px;
        line-height: 1.6;
    }
    .download-btn {
        display: inline-flex;
        align-items: center;
        background-color: #3498db;
        color: white;
        padding: 10px 20px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        margin-top: 15px;
        transition: all 0.3s ease;
    }
    .download-btn:hover {
        background-color: #2980b9;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
    }
    .download-btn i {
        margin-right: 8px;
    }
    .topic-heading {
        background: linear-gradient(135deg, #3498db, #2980b9);
        color: white;
        padding: 10px 15px;
        border-radius: 8px;
        margin: 20px 0 15px;
        font-size: 18px;
        font-weight: 600;
    }
    .fade-in {
        animation: fadeIn 0.5s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .error-details {
        background-color: #f8f9fa;
        border-left: 4px solid #dc3545;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 5px;
    }
    
    .error-details h6 {
        color: #dc3545;
        margin-bottom: 10px;
        font-weight: 600;
    }
    
    .error-details ul {
        margin-bottom: 0;
        padding-left: 20px;
    }
    
    .error-details li {
        margin-bottom: 5px;
        font-size: 14px;
    }
</style>

<header>
    <h1>Quản Lý Câu Hỏi </h1>
</header>

<div class="card fade-in">
    <ul class="nav nav-tabs" id="questionTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="manual-tab" data-toggle="tab" href="#manual" role="tab">
                <i class="fas fa-pencil-alt"></i> Thêm thủ công
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="excel-tab" data-toggle="tab" href="#excel" role="tab">
                <i class="fas fa-file-excel"></i> Nhập từ Excel
            </a>
        </li>
    </ul>
    
    <div class="tab-content" id="questionTabsContent">
        <!-- Tab thêm thủ công -->
        <div class="tab-pane fade show active" id="manual" role="tabpanel">
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="question_content"><i class="fas fa-question-circle"></i> Nội dung câu hỏi:</label>
                    <input type="text" name="question_content" id="question_content" required class="form-control" 
                           placeholder="Nhập nội dung câu hỏi...">
                </div>
                <div class="form-group">
                    <label for="question_type_id"><i class="fas fa-tag"></i> Loại câu hỏi:</label>
                    <div class="topic-select">
                        <select style="height: 50px;" name="question_type_id" id="question_type_id" class="form-control" required>
                            <?php 
                            sqlsrv_free_stmt($result_types);
                            $result_types = sqlsrv_query($conn, $sql_types);
                            while ($type = sqlsrv_fetch_array($result_types, SQLSRV_FETCH_ASSOC)) { 
                            ?>
                                <option value="<?php echo $type['MaLoaiCauHoi']; ?>"><?php echo $type['ChuDe']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="expiry_date"><i class="fas fa-calendar"></i> Thời gian hết hạn:</label>
                    <input type="datetime-local" name="expiry_date" id="expiry_date" required class="form-control">
                </div>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-plus"></i> Thêm Câu Hỏi
                </button>
            </form>
        </div>
        
        <!-- Tab nhập từ Excel -->
        <div class="tab-pane fade" id="excel" role="tabpanel">
            <?php if (isset($import_message)): ?>
                <div class="alert <?php echo strpos($import_message, 'thành công') !== false ? 'alert-success' : 'alert-danger'; ?>">
                    <i class="<?php echo strpos($import_message, 'thành công') !== false ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle'; ?>"></i>
                    <?php echo $import_message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="excel_file"><i class="fas fa-file-upload"></i> Chọn file Excel:</label>
                    <input style="height: 50px;" type="file" name="excel_file" id="excel_file" required class="form-control">
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle"></i> File Excel phải có định dạng: Cột A - Nội dung câu hỏi, Cột B - Mã loại câu hỏi, Cột C - Thời gian hết hạn
                    </small>
                </div>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-file-import"></i> Nhập Từ Excel
                </button>
            </form>
            
            <div class="guide-list">
                <h5><i class="fas fa-book"></i> Hướng dẫn nhập từ Excel:</h5>
                <ol>
                    <li>Tạo file Excel hoặc CSV với 3 cột: Cột A (Nội dung câu hỏi), Cột B (Mã loại câu hỏi), Cột C (Thời gian hết hạn)</li>
                    <li>Dòng đầu tiên là tiêu đề, dữ liệu bắt đầu từ dòng thứ 2</li>
                    <li>Mã loại câu hỏi phải tồn tại trong hệ thống</li>
                    <li>Thời gian hết hạn phải có định dạng YYYY-MM-DD HH:MM:SS</li>
                    <li>Lưu file dưới định dạng .xlsx, .xls hoặc .csv</li>
                </ol>
                <?php
                $templatePath = 'templates/mau_nhap_cau_hoi.csv';
                if (file_exists($templatePath)) {
                    echo '<a href="' . $templatePath . '" download class="download-btn">';
                    echo '<i class="fas fa-download"></i> Tải file mẫu CSV</a>';
                } else {
                    echo '<div class="alert alert-warning">';
                    echo '<i class="fas fa-exclamation-triangle"></i> Không tìm thấy file mẫu. Vui lòng liên hệ quản trị viên.';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<div class="card fade-in">
    <h3 class="section-title"><i class="fas fa-list"></i> Danh Sách Câu Hỏi</h3>
    
    <!-- Form để chọn chủ đề -->
    <div class="form-group">
        <form method="GET" action="">
            <label for="topic"><i class="fas fa-filter"></i> Chọn chủ đề:</label>
            <div class="topic-select">
                <select style="height: 50px;" name="topic" id="topic" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Chọn chủ đề --</option>
                    <?php 
                    // Reset con trỏ của result_types để dùng lại
                    sqlsrv_free_stmt($result_types);
                    $result_types = sqlsrv_query($conn, $sql_types);
                    while ($type = sqlsrv_fetch_array($result_types, SQLSRV_FETCH_ASSOC)) { 
                    ?>
                        <option value="<?php echo $type['MaLoaiCauHoi']; ?>" <?php echo ($selected_topic == $type['MaLoaiCauHoi']) ? 'selected' : ''; ?>>
                            <?php echo $type['ChuDe']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </form>
    </div>
    
    <?php 
    // Hiển thị câu hỏi chỉ khi có chủ đề được chọn
    if ($selected_topic && $result) {
        echo "<div class='question-group fade-in'>";
        $has_questions = false;
        
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $has_questions = true;
            if ($current_topic !== $row['ChuDe']) {
                $current_topic = $row['ChuDe'];
                echo "<div class='topic-heading'><i class='fas fa-bookmark'></i> {$current_topic}</div>";
            }
    ?>
            <div class="question-item">
                <p><strong><i class="fas fa-hashtag"></i> ID:</strong> <?php echo $row['IdCauHoi']; ?></p>
                <p><strong><i class="fas fa-quote-left"></i> Nội Dung:</strong> <?php echo $row['NoiDungCauHoi']; ?></p>
                <p><strong><i class="fas fa-clock"></i> Thời gian hết hạn:</strong> <?php echo $row['ThoiGianHetHan']->format('Y-m-d H:i:s'); ?></p>
                <div class="action-links">
                    <a href="edit_question.php?id=<?php echo $row['IdCauHoi']; ?>" class="edit-btn">
                        <i class="fas fa-edit"></i> Sửa
                    </a>
                    <a href="delete_question.php?id=<?php echo $row['IdCauHoi']; ?>" class="delete-btn"
                       onclick="return confirm('Bạn có chắc chắn muốn xóa câu hỏi này?')">
                        <i class="fas fa-trash-alt"></i> Xóa
                    </a>
                </div>
            </div>
    <?php
        }
        
        if (!$has_questions) {
            echo "<div class='empty-state'><i class='fas fa-search'></i><p>Không có câu hỏi nào cho chủ đề này.</p></div>";
        }
        
        echo "</div>";
    } else if ($selected_topic) {
        echo "<div class='empty-state'><i class='fas fa-search'></i><p>Không có câu hỏi nào cho chủ đề này.</p></div>";
    } else {
        echo "<div class='empty-state'><i class='fas fa-hand-point-up'></i><p>Vui lòng chọn một chủ đề để xem câu hỏi.</p></div>";
    }
    ?>
</div>

<!-- Thêm JavaScript để xử lý tabs và hiệu ứng -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Xử lý chuyển tab
        const tabs = document.querySelectorAll('.nav-link');
        const tabContents = document.querySelectorAll('.tab-pane');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Xóa active class từ tất cả tabs và tab contents
                tabs.forEach(t => t.classList.remove('active'));
                tabContents.forEach(c => {
                    c.classList.remove('show', 'active');
                });
                
                // Thêm active class cho tab được click
                this.classList.add('active');
                
                // Hiển thị tab content tương ứng
                const target = this.getAttribute('href').substring(1);
                document.getElementById(target).classList.add('show', 'active');
            });
        });
        
        // Hiệu ứng cho các phần tử khi trang tải xong
        const fadeElements = document.querySelectorAll('.fade-in');
        fadeElements.forEach((element, index) => {
            setTimeout(() => {
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }, 100 * index);
        });
        
        // Hiệu ứng hover cho các nút
        const buttons = document.querySelectorAll('.btn-submit, .action-links a');
        buttons.forEach(button => {
            button.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });
            button.addEventListener('mouseleave', function() {
                this.style.transform = '';
            });
        });
    });
</script>

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