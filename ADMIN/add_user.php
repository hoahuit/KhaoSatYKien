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

// Xử lý form khi submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $user_type = $_POST['user_type'];
    
    // Thông tin sinh viên (nếu là sinh viên)
    $student_name = $_POST['student_name'] ?? null;
    $birth_date = $_POST['birth_date'] ?? null;
    $gender = $_POST['gender'] ?? null;
    $phone = $_POST['phone'] ?? null;
    $email = $_POST['email'] ?? null;
    $class_id = $_POST['class_id'] ?? null;

    // Bắt đầu transaction
    sqlsrv_begin_transaction($conn);

    try {
        $maSV = null;
        if ($user_type == 1 && $student_name) { // Nếu là sinh viên
            // Thêm vào bảng SinhVien trước
            $sql_student = "INSERT INTO SinhVien (TenSV, NgaySinh, GioiTinh, DienThoai, Mail, MaLop) 
                           VALUES (?, ?, ?, ?, ?, ?)";
            $params_student = array($student_name, $birth_date, $gender, $phone, $email, $class_id);
            $stmt_student = sqlsrv_query($conn, $sql_student, $params_student);
            
            if ($stmt_student === false) {
                throw new Exception("Failed to insert student: " . print_r(sqlsrv_errors(), true));
            }

            $sql_get_id = "SELECT MaSV FROM SinhVien WHERE TenSV = ? AND DienThoai = ? AND Mail = ?";
            $params_get_id = array($student_name, $phone, $email);
            $stmt_id = sqlsrv_query($conn, $sql_get_id, $params_get_id);
            if ($stmt_id === false) {
                throw new Exception("Failed to get student ID: " . print_r(sqlsrv_errors(), true));
            }
            $row = sqlsrv_fetch_array($stmt_id, SQLSRV_FETCH_ASSOC);
            $maSV = $row['MaSV']; // Lưu vào biến $maSV

            sqlsrv_free_stmt($stmt_id);
            sqlsrv_free_stmt($stmt_student);
        }

        // Thêm vào bảng TaiKhoan với MaSV (nếu có)
        $sql_account = "INSERT INTO TaiKhoan (TenDangNhap, MatKhau, LoaiNguoiDung, MaSV) 
                       VALUES (?, ?, ?, ?)";
        $params_account = array($username, $password, $user_type, $maSV);
        $stmt_account = sqlsrv_query($conn, $sql_account, $params_account);

        if ($stmt_account === false) {
            throw new Exception("Failed to insert account: " . print_r(sqlsrv_errors(), true));
        }

        // Commit transaction
        sqlsrv_commit($conn);
        header("Location: user_management.php?success=1");
        exit();
    } catch (Exception $e) {
        // Rollback nếu có lỗi
        sqlsrv_rollback($conn);
        $error = $e->getMessage();
    }
}

// Lấy danh sách lớp học
$sql_classes = "SELECT MaLop, TenLop FROM Lop";
$result_classes = sqlsrv_query($conn, $sql_classes);
if ($result_classes === false) {
    die("Failed to fetch classes: " . print_r(sqlsrv_errors(), true));
}

ob_start();
?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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
<header>
    <h1 class="page-title">Thêm Người Dùng</h1>
</header>
<div class="card">
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST" action="" class="user-form">
        <div class="form-group">
            <label for="username">Tên đăng nhập:</label>
            <input type="text" name="username" id="username" required class="form-control">
        </div>
        <div class="form-group">
            <label for="password">Mật khẩu:</label>
            <input type="password" name="password" id="password" required class="form-control">
        </div>
        <div class="form-group">
            <label for="user_type">Loại người dùng:</label>
            <select name="user_type" id="user_type" class="form-control" onchange="toggleStudentFields()">
                <option value="1">Sinh viên</option>
                <option value="2">Admin</option>
            </select>
        </div>

        <!-- Thông tin sinh viên -->
        <div id="student_fields" class="student-fields">
            <div class="form-group">
                <label for="student_name">Tên sinh viên:</label>
                <input type="text" name="student_name" id="student_name" class="form-control">
            </div>
            <div class="form-group">
                <label for="birth_date">Ngày sinh:</label>
                <input type="date" name="birth_date" id="birth_date" class="form-control">
            </div>
            <div class="form-group">
                <label for="gender">Giới tính:</label>
                <select name="gender" id="gender" class="form-control">
                    <option value="Nam">Nam</option>
                    <option value="Nữ">Nữ</option>
                </select>
            </div>
            <div class="form-group">
                <label for="phone">Số điện thoại:</label>
                <input type="text" name="phone" id="phone" class="form-control">
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" class="form-control">
            </div>
            <div class="form-group">
                <label for="class_id">Lớp:</label>
                <select name="class_id" id="class_id" class="form-control">
                    <?php 
                    while ($class = sqlsrv_fetch_array($result_classes, SQLSRV_FETCH_ASSOC)) { 
                    ?>
                        <option value="<?php echo $class['MaLop']; ?>"><?php echo $class['TenLop']; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <button type="submit" class="btn-submit">Thêm Người Dùng</button>
    </form>
</div>

<script>
function toggleStudentFields() {
    var userType = document.getElementById('user_type').value;
    var studentFields = document.getElementById('student_fields');
    studentFields.style.display = (userType == '1') ? 'block' : 'none';
}
</script>

<?php
$content = ob_get_clean();
include 'layout.php';

// Giải phóng tài nguyên
if (isset($result_classes) && $result_classes !== false) {
    sqlsrv_free_stmt($result_classes);
}
sqlsrv_close($conn);
?>