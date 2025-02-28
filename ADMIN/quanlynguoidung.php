<?php
session_start();

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION["user_id"])) {
    header("Location: ./HS/index.php");
    exit();
}

// Kết nối cơ sở dữ liệu
include 'config.php';

// Truy vấn lấy danh sách người dùng từ bảng TaiKhoan và SinhVien
$sql = "SELECT tk.MaTK, tk.TenDangNhap, tk.LoaiNguoiDung, tk.MaNV, tk.MaSV, sv.TenSV 
        FROM TaiKhoan tk 
        LEFT JOIN SinhVien sv ON tk.MaSV = sv.MaSV";
$result = sqlsrv_query($conn, $sql);

if ($result === false) {
    die(print_r(sqlsrv_errors(), true)); // Hiển thị lỗi nếu truy vấn thất bại
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Người Dùng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .main-content {
            padding: 20px;
        }
        .card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        a {
            text-decoration: none;
            color: #007bff;
            margin-right: 10px;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php include 'layout.php'; // Include file giao diện chung nếu có ?>
    <div class="main-content">
        <header>
            <h1>Quản Lý Người Dùng</h1>
        </header>
        <div class="card">
            <h3>Danh Sách Người Dùng</h3>
            <table>
                <thead>
                    <tr>
                        <th>Mã TK</th>
                        <th>Tên Đăng Nhập</th>
                        <th>Loại Người Dùng</th>
                        <th>Mã SV</th>
                        <th>Tên SV</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) { ?>
                        <tr>
                            <td><?php echo $row['MaTK']; ?></td>
                            <td><?php echo $row['TenDangNhap']; ?></td>
                            <td><?php echo $row['LoaiNguoiDung']; ?></td>
                            <td><?php echo $row['MaSV'] ? $row['MaSV'] : 'N/A'; ?></td>
                            <td><?php echo $row['TenSV'] ? $row['TenSV'] : 'N/A'; ?></td>
                            <td>
                                <a href="edit_user.php?id=<?php echo $row['MaTK']; ?>">Sửa</a>
                                <a href="delete_user.php?id=<?php echo $row['MaTK']; ?>" 
                                   onclick="return confirm('Bạn có chắc chắn muốn xóa?')">Xóa</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <a href="add_user.php">Thêm Người Dùng</a>
        </div>
    </div>
</body>
</html>

<?php
// Giải phóng tài nguyên
sqlsrv_free_stmt($result);
sqlsrv_close($conn);
?>