<?php
session_start();
include 'config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error_message = '';
$success_message = '';

// Lấy thông tin tài khoản và sinh viên
$sql = "SELECT tk.*, sv.* FROM TaiKhoan tk 
        LEFT JOIN SinhVien sv ON tk.MaSV = sv.MaSV 
        WHERE tk.MaTK = ?";
$params = array($user_id);
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die("Lỗi truy vấn: " . print_r(sqlsrv_errors(), true));
}

$user_data = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

// Lấy danh sách lớp để hiển thị trong dropdown
$sql_lop = "SELECT * FROM Lop";
$stmt_lop = sqlsrv_query($conn, $sql_lop);

if ($stmt_lop === false) {
    die("Lỗi truy vấn lớp: " . print_r(sqlsrv_errors(), true));
}

// Xử lý cập nhật thông tin cá nhân
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $ten_sv = $_POST['ten_sv'];
    $ngay_sinh = $_POST['ngay_sinh'];
    $que_quan = $_POST['que_quan'] ?? null;
    $gioi_tinh = $_POST['gioi_tinh'] ?? null;
    $ma_lop = $_POST['ma_lop'] ?? null;
    $mail = $_POST['mail'] ?? null;
    $dien_thoai = $_POST['dien_thoai'] ?? null;
    
    // Chuyển đổi định dạng ngày sinh
    $date_parts = explode('-', $ngay_sinh);
    if (count($date_parts) == 3) {
        $ngay_sinh_sql = $date_parts[0] . '-' . $date_parts[1] . '-' . $date_parts[2];
    } else {
        $ngay_sinh_sql = null;
    }
    
    // Cập nhật thông tin sinh viên
    $sql_update = "UPDATE SinhVien SET 
                    TenSV = ?, 
                    NgaySinh = ?, 
                    QueQuan = ?, 
                    GioiTinh = ?, 
                    MaLop = ?, 
                    Mail = ?, 
                    DienThoai = ? 
                    WHERE MaSV = ?";
    
    $params_update = array(
        $ten_sv,
        $ngay_sinh_sql,
        $que_quan,
        $gioi_tinh,
        $ma_lop,
        $mail,
        $dien_thoai,
        $user_data['MaSV']
    );
    
    $stmt_update = sqlsrv_query($conn, $sql_update, $params_update);
    
    if ($stmt_update === false) {
        $error_message = "Lỗi cập nhật thông tin: " . print_r(sqlsrv_errors(), true);
    } else {
        $success_message = "Cập nhật thông tin thành công!";
        
        // Cập nhật lại dữ liệu hiển thị
        $stmt = sqlsrv_query($conn, $sql, $params);
        $user_data = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    }
}

// Xử lý đổi mật khẩu - đã bỏ mã hóa
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Kiểm tra mật khẩu hiện tại - không dùng password_verify
    if ($current_password == $user_data['MatKhau']) {
        // Kiểm tra mật khẩu mới và xác nhận mật khẩu
        if ($new_password === $confirm_password) {
            // Không mã hóa mật khẩu mới
            
            // Cập nhật mật khẩu
            $sql_update_pass = "UPDATE TaiKhoan SET MatKhau = ? WHERE MaTK = ?";
            $params_update_pass = array($new_password, $user_id);
            $stmt_update_pass = sqlsrv_query($conn, $sql_update_pass, $params_update_pass);
            
            if ($stmt_update_pass === false) {
                $error_message = "Lỗi cập nhật mật khẩu: " . print_r(sqlsrv_errors(), true);
            } else {
                $success_message = "Đổi mật khẩu thành công!";
            }
        } else {
            $error_message = "Mật khẩu mới và xác nhận mật khẩu không khớp!";
        }
    } else {
        $error_message = "Mật khẩu hiện tại không đúng!";
    }
}

// Format ngày sinh để hiển thị trong form
$ngay_sinh_display = '';
if (isset($user_data['NgaySinh']) && $user_data['NgaySinh'] instanceof DateTime) {
    $ngay_sinh_display = $user_data['NgaySinh']->format('Y-m-d');
} elseif (isset($user_data['NgaySinh'])) {
    $ngay_sinh_display = date('Y-m-d', strtotime($user_data['NgaySinh']));
}

ob_start();
?>

<style>
    .profile-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .profile-header {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .profile-header h1 {
        color: #2c3e50;
        font-size: 2.5rem;
        margin-bottom: 10px;
    }
    
    .profile-header p {
        color: #7f8c8d;
        font-size: 1.2rem;
    }
    
    .profile-card {
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-bottom: 30px;
    }
    
    .profile-card-header {
        background: linear-gradient(135deg, #3498db, #2980b9);
        color: white;
        padding: 20px;
        font-size: 1.5rem;
        font-weight: 600;
    }
    
    .profile-card-body {
        padding: 30px;
    }
    
    .form-group {
        margin-bottom: 25px;
    }
    
    .form-group label {
        font-weight: 600;
        margin-bottom: 10px;
        display: block;
        color: #2c3e50;
    }
    
    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 16px;
        transition: all 0.3s;
    }
    
    .form-control:focus {
        border-color: #3498db;
        box-shadow: 0 0 5px rgba(52, 152, 219, 0.5);
    }
    
    .btn-primary {
        background-color: #3498db;
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        font-weight: 600;
        transition: all 0.3s;
    }
    
    .btn-primary:hover {
        background-color: #2980b9;
        transform: translateY(-2px);
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
    
    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .profile-tabs {
        display: flex;
        margin-bottom: 20px;
    }
    
    .profile-tab {
        padding: 15px 25px;
        background-color: #f8f9fa;
        border: none;
        cursor: pointer;
        font-size: 16px;
        font-weight: 600;
        color: #7f8c8d;
        transition: all 0.3s;
    }
    
    .profile-tab.active {
        background-color: #3498db;
        color: white;
    }
    
    .profile-tab:first-child {
        border-top-left-radius: 5px;
        border-bottom-left-radius: 5px;
    }
    
    .profile-tab:last-child {
        border-top-right-radius: 5px;
        border-bottom-right-radius: 5px;
    }
    
    .tab-content {
        display: none;
    }
    
    .tab-content.active {
        display: block;
    }
    
    .profile-info {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    
    @media (max-width: 768px) {
        .profile-info {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="profile-container">
    <div class="profile-header">
        <h1>Thông Tin Cá Nhân</h1>
        <p>Quản lý thông tin cá nhân và tài khoản của bạn</p>
    </div>
    
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>
    

 
        <!-- Tab đổi mật khẩu -->
        <div id="password">
            <div class="profile-card-header">
                <i class="fas fa-key"></i> Đổi mật khẩu
            </div>
            <div class="profile-card-body">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="current_password">Mật khẩu hiện tại:</label>
                        <input type="password" id="current_password" name="current_password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">Mật khẩu mới:</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Xác nhận mật khẩu mới:</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn-primary">
                        <i class="fas fa-key"></i> Đổi mật khẩu
                    </button>
                </form>
            </div>
        </div>
</div>



<?php
$content = ob_get_clean();
include 'layout.php';

// Giải phóng tài nguyên
if ($stmt && gettype($stmt) === 'resource') {
    sqlsrv_free_stmt($stmt);
}

if ($stmt_lop && gettype($stmt_lop) === 'resource') {
    sqlsrv_free_stmt($stmt_lop);
}

sqlsrv_close($conn);
?> 