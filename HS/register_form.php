<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = password_hash(trim($_POST["password"]), PASSWORD_BCRYPT);

    // Kiểm tra xem tên đăng nhập đã tồn tại chưa
    $check_sql = "SELECT COUNT(*) as count FROM TaiKhoan WHERE TenDangNhap = ?";
    $check_stmt = sqlsrv_query($conn, $check_sql, array($username));
    $row = sqlsrv_fetch_array($check_stmt, SQLSRV_FETCH_ASSOC);

    if ($row['count'] > 0) {
        echo "<div class='alert alert-danger'>Tên đăng nhập đã tồn tại. Vui lòng chọn tên khác.</div>";
    } else {
        $sql = "INSERT INTO TaiKhoan (TenDangNhap, MatKhau) VALUES (?, ?)";
        $params = array($username, $password);
        $stmt = sqlsrv_query($conn, $sql, $params);
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: #6a1b9a;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .register-container {
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }
        .register-container h2 {
            margin-bottom: 20px;
            color: #6a1b9a;
        }
        .register-container input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .register-container button {
            background-color: #6a1b9a;
            color: white;
            border: none;
            padding: 8px; /* Adjusted padding for smaller button */
            border-radius: 5px;
            cursor: pointer;
            width: 80%; /* Adjusted width for centering */
            margin: 10px 0; /* Added margin for spacing */
        }
        .register-container button:hover {
            background-color: #4a148c;
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
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Đăng Ký</h2>
        <form action="" method="POST">
            <input type="text" name="username" placeholder="Tên đăng nhập" required>
            <input type="password" name="password" placeholder="Mật khẩu" required>
            <button type="submit">Đăng Ký</button>
        </form>
        <p><a href="index.php">Đã có tài khoản? Đăng nhập ngay</a></p>
    </div>
</body>
</html>
