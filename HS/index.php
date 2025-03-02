<?php
session_start();
include 'config.php';

// Xử lý yêu cầu đăng nhập
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    // Truy vấn để lấy thông tin tài khoản
    $sql = "SELECT MaTK, MatKhau FROM TaiKhoan WHERE loainguoidung = 1 AND TenDangNhap = ?";
    $params = array($username);
    $stmt = sqlsrv_query($conn, $sql, $params);

    // Kiểm tra kết quả truy vấn
    if ($stmt && $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        // Xác thực mật khẩu
        if ($password== $row["MatKhau"]) {
            // Lưu thông tin người dùng vào session
            $_SESSION["user_id"] = $row["MaTK"];
            $_SESSION["username"] = $username;
            header("Location: dashboard.php");
            exit();
        } else {
            // Thông báo lỗi mật khẩu không đúng
            echo "<div class='alert alert-danger text-center' role='alert'>Sai mật khẩu! Vui lòng thử lại.</div>";
        }
    } else {
        // Thông báo lỗi tài khoản không tồn tại
        echo "<div class='alert alert-warning text-center' role='alert'>Tài khoản không tồn tại! Vui lòng kiểm tra lại.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: #f0f4f8; /* Màu nền sáng và dễ chịu */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            background-color: white;
            padding: 30px; /* Giảm padding */
            border-radius: 10px; /* Giảm bo tròn góc */
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2); /* Giảm độ đổ bóng */
            width: 450px; /* Giảm kích thước */
            text-align: center;
        }
        .login-container img {
            width: 150px; /* Kích thước logo lớn hơn */
            margin-bottom: 20px;
        }
        .login-container h1 {
            margin-bottom: 5px; /* Giảm khoảng cách */
            color: #003366; /* Màu xanh đậm hơn */
            font-size: 28px; /* Giảm kích thước chữ */
        }
        .login-container h2 {
            margin-bottom: 15px; /* Giảm khoảng cách */
            color: #555; 
            font-size: 20px; /* Giảm kích thước chữ */
        }
        .login-container input {
            width: 100%;
            padding: 15px; /* Tăng padding */
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s; /* Hiệu ứng chuyển màu */
        }
        .login-container input:focus {
            border-color: #1976d2; /* Màu viền khi focus */
            outline: none; /* Bỏ viền mặc định */
        }
        .login-container button {
            background-color: #007bff; /* Màu xanh mới */
            color: white;
            border: none;
            padding: 12px; /* Giảm padding */
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            margin-top: 10px;
            transition: background-color 0.3s;
            display: flex; /* Sử dụng flexbox để căn giữa */
            justify-content: center; /* Căn giữa nội dung */
            align-items: center; /* Căn giữa nội dung */
        }
        .login-container button:hover {
            background-color: #0056b3; /* Màu xanh đậm hơn khi hover */
        }
        .alert {
            border-radius: 5px;
            margin-top: 10px;
            padding: 10px;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        p {
            margin-top: 15px;
            font-size: 14px;
        }
        a {
            color: #1976d2; /* Màu xanh chủ đạo */
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        #login-button {
            background-color: #007bff; /* Màu xanh mới */
            width: 45%;
            margin: 0 auto; /* Căn giữa nút */
        }
    
    </style>
</head>
<body>
    <div class="login-container">
        <img src="images.png" alt="Logo Đại học Sư phạm Kỹ thuật Vinh"> <!-- Thêm logo, cần file logo.png -->
        <h1>ĐẠI HỌC SƯ PHẠM KỸ THUẬT VINH</h1>
        <strong>Khảo sát trực tuyến</strong>
        <h2>Đăng nhập</h2>
        <form action="" method="POST">
            <input id="username" type="text" name="username" placeholder="Tên đăng nhập" required>
            <input id="password" type="password" name="password" placeholder="Mật khẩu" required>
            <button id="login-button" type="submit">Đăng nhập</button>
        </form>
    </div>
</body>
</html>