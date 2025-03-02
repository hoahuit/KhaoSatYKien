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

$id = $_GET['id'];

// Lấy thông tin người dùng hiện tại
$sql = "SELECT tk.*, sv.TenSV, sv.NgaySinh, sv.GioiTinh, sv.DienThoai, sv.Mail, sv.MaLop 
        FROM TaiKhoan tk 
        LEFT JOIN SinhVien sv ON tk.MaSV = sv.MaSV 
        WHERE tk.MaTK = ?";
$params = array($id);
$stmt = sqlsrv_query($conn, $sql, $params);
$user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

// Lấy danh sách lớp học
$sql_classes = "SELECT MaLop, TenLop FROM Lop";
$result_classes = sqlsrv_query($conn, $sql_classes);

// Xử lý form khi submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $user_type = $_POST['user_type'];
    
    $student_name = isset($_POST['student_name']) ? $_POST['student_name'] : null;
    $birth_date = isset($_POST['birth_date']) ? $_POST['birth_date'] : null;
    $gender = isset($_POST['gender']) ? $_POST['gender'] : null;
    $phone = isset($_POST['phone']) ? $_POST['phone'] : null;
    $email = isset($_POST['email']) ? $_POST['email'] : null;
    $class_id = isset($_POST['class_id']) ? $_POST['class_id'] : null;

    sqlsrv_begin_transaction($conn);

    try {
        if ($user_type == 1) { // Sinh viên
            if ($user['MaSV']) {
                // Cập nhật thông tin sinh viên
                $sql_update_sv = "UPDATE SinhVien SET TenSV = ?, NgaySinh = ?, GioiTinh = ?, 
                                DienThoai = ?, Mail = ?, MaLop = ? WHERE MaSV = ?";
                $params_sv = array($student_name, $birth_date, $gender, $phone, $email, $class_id, $user['MaSV']);
                $stmt_sv = sqlsrv_query($conn, $sql_update_sv, $params_sv);
                if ($stmt_sv === false) {
                    throw new Exception("Failed to update student: " . print_r(sqlsrv_errors(), true));
                }
            }
        }

        // Cập nhật tài khoản
        $sql_update_tk = "UPDATE TaiKhoan SET TenDangNhap = ?, MatKhau = ?, LoaiNguoiDung = ? WHERE MaTK = ?";
        $params_tk = array($username, $password, $user_type, $id);
        $stmt_tk = sqlsrv_query($conn, $sql_update_tk, $params_tk);
        if ($stmt_tk === false) {
            throw new Exception("Failed to update account: " . print_r(sqlsrv_errors(), true));
        }

        sqlsrv_commit($conn);
        header("Location: user_management.php?success=2");
        exit();
    } catch (Exception $e) {
        sqlsrv_rollback($conn);
        $error = $e->getMessage();
    }
}

ob_start();
?>

<div class="card">
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST" action="" class="user-form">
        <div class="form-group">
            <label for="username">Tên đăng nhập:</label>
            <input type="text" name="username" id="username" value="<?php echo $user['TenDangNhap']; ?>" required class="form-control">
        </div>
        <div class="form-group">
            <label for="password">Mật khẩu:</label>
            <input type="password" name="password" id="password" value="<?php echo $user['MatKhau']; ?>" required class="form-control">
        </div>
        <div class="form-group">
            <label for="user_type">Loại người dùng:</label>
            <select name="user_type" id="user_type" class="form-control" onchange="toggleStudentFields()">
                <option value="1" <?php echo $user['LoaiNguoiDung'] == 1 ? 'selected' : ''; ?>>Sinh viên</option>
                <option value="2" <?php echo $user['LoaiNguoiDung'] == 2 ? 'selected' : ''; ?>>Admin</option>
            </select>
        </div>

        <div id="student_fields" class="student-fields" style="display: <?php echo $user['LoaiNguoiDung'] == 1 ? 'block' : 'none'; ?>;">
            <div class="form-group">
                <label for="student_name">Tên sinh viên:</label>
                <input type="text" name="student_name" id="student_name" value="<?php echo $user['TenSV']; ?>" class="form-control">
            </div>
            <div class="form-group">
                <label for="birth_date">Ngày sinh:</label>
                <input type="date" name="birth_date" id="birth_date" value="<?php echo $user['NgaySinh'] ? date_format($user['NgaySinh'], 'Y-m-d') : ''; ?>" class="form-control">
            </div>
            <div class="form-group">
                <label for="gender">Giới tính:</label>
                <select name="gender" id="gender" class="form-control">
                    <option value="Nam" <?php echo $user['GioiTinh'] == 'Nam' ? 'selected' : ''; ?>>Nam</option>
                    <option value="Nữ" <?php echo $user['GioiTinh'] == 'Nữ' ? 'selected' : ''; ?>>Nữ</option>
                </select>
            </div>
            <div class="form-group">
                <label for="phone">Số điện thoại:</label>
                <input type="text" name="phone" id="phone" value="<?php echo $user['DienThoai']; ?>" class="form-control">
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" value="<?php echo $user['Mail']; ?>" class="form-control">
            </div>
            <div class="form-group">
                <label for="class_id">Lớp:</label>
                <select name="class_id" id="class_id" class="form-control">
                    <?php while ($class = sqlsrv_fetch_array($result_classes, SQLSRV_FETCH_ASSOC)) { ?>
                        <option value="<?php echo $class['MaLop']; ?>" <?php echo $user['MaLop'] == $class['MaLop'] ? 'selected' : ''; ?>>
                            <?php echo $class['TenLop']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <button type="submit" class="btn-submit">Cập nhật</button>
    </form>
</div>

<script>
function toggleStudentFields() {
    var userType = document.getElementById('user_type').value;
    var studentFields = document.getElementById('student_fields');
    studentFields.style.display = (userType == '1') ? 'block' : 'none';
}
</script>

<style>
    body {
        background-color: #e9ecef;
        font-family: 'Arial', sans-serif;
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
        max-width: 600px;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        background-color: #ffffff;
    }
    .btn-submit {
        width: 100%;
        background-color: #28a745;
        color: white;
        border: none;
        border-radius: 5px;
        padding: 12px;
        font-size: 18px;
        transition: background-color 0.3s ease;
    }
    .btn-submit:hover {
        background-color: #218838;
    }
    .error {
        color: #dc3545;
        text-align: center;
        margin-bottom: 15px;
        font-weight: bold;
    }
    .form-group label {
        font-weight: bold;
        color: #495057;
    }
    .form-control {
        border-radius: 5px;
        border: 1px solid #ced4da;
        transition: border-color 0.3s ease;
    }
    .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
</style>

<?php
$content = ob_get_clean();
include 'layout.php';
sqlsrv_close($conn);
?>