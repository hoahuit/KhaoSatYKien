<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ./HS/index.php");
    exit();
}

include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tenDangNhap = $_POST['tenDangNhap'];
    $matKhau = password_hash($_POST['matKhau'], PASSWORD_DEFAULT);
    $loaiNguoiDung = $_POST['loaiNguoiDung'];
    $maSV = ($loaiNguoiDung == 2) ? $_POST['maSV'] : NULL;

    $sql = "INSERT INTO TaiKhoan (TenDangNhap, MatKhau, LoaiNguoiDung, MaSV) VALUES (?, ?, ?, ?)";
    $params = array($tenDangNhap, $matKhau, $loaiNguoiDung, $maSV);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        $error = "Lỗi khi thêm tài khoản.";
    } else {
        header("Location: quanlynguoidung.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Người Dùng</title>
</head>
<body>
    <h1>Thêm Người Dùng</h1>
    <?php if (isset($error)) { echo "<p>$error</p>"; } ?>
    <form method="POST" action="add_user.php">
        <label>Tên Đăng Nhập:</label>
        <input type="text" name="tenDangNhap" required><br>

        <label>Mật Khẩu:</label>
        <input type="password" name="matKhau" required><br>

        <label>Loại Người Dùng:</label>
        <select name="loaiNguoiDung" required>
            <option value="1">Admin</option>
            <option value="2">Sinh Viên</option>
        </select><br>

        <label>Mã Sinh Viên (nếu là sinh viên):</label>
        <input type="number" name="maSV"><br>

        <button type="submit">Thêm</button>
    </form>
</body>
</html>