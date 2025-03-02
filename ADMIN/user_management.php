<?php
session_start();

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION["user_id"])) {
    header("Location: ./HS/index.php");
    exit();
}

// Kết nối cơ sở dữ liệu
include 'config.php';
if ($conn === false) {
    die("Connection failed: " . print_r(sqlsrv_errors(), true));
}

// Truy vấn lấy danh sách người dùng
$sql = "SELECT tk.MaTK, tk.TenDangNhap, tk.MatKhau, tk.LoaiNguoiDung, 
               tk.MaSV, sv.TenSV, tk.MaAdmin, ad.TenNV 
        FROM [dbo].[TaiKhoan] tk 
        LEFT JOIN [dbo].[SinhVien] sv ON tk.MaSV = sv.MaSV 
        LEFT JOIN [dbo].[Admin] ad ON tk.MaAdmin = ad.MaAdmin";
$result = sqlsrv_query($conn, $sql);

if ($result === false) {
    die("Query failed: " . print_r(sqlsrv_errors(), true));
}

$page_title = "Quản Lý Người Dùng";
ob_start();
?>

<header>
    <h1>Quản Lý Người Dùng</h1>
</header>

<!-- Thêm thông báo thành công -->
<?php
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 1:
            echo "<p class='success'>Thêm người dùng thành công!</p>";
            break;
        case 2:
            echo "<p class='success'>Cập nhật thông tin người dùng thành công!</p>";
            break;
        case 3:
            echo "<p class='success'>Xóa người dùng thành công!</p>";
            break;
    }
}
?>

<div class="card">
    <h3>Danh Sách Người Dùng</h3>
    <table>
        <thead>
            <tr>
                <th>Mã TK</th>
                <th>Tên Đăng Nhập</th>
                <th>Mật Khẩu</th>
                <th>Loại Người Dùng</th>
                <th>Mã SV</th>
                <th>Tên SV</th>
                <th>Hành Động</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if ($result && sqlsrv_has_rows($result)) {
                while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) { 
            ?>
                <tr>
                    <td><?php echo $row['MaTK']; ?></td>
                    <td><?php echo $row['TenDangNhap']; ?></td>
                    <td><?php echo $row['MatKhau']; ?></td>
                    <td><?php echo $row['LoaiNguoiDung'] == 1 ? 'Sinh Viên' : 'Admin'; ?></td>
                    <td><?php echo $row['MaSV'] ? $row['MaSV'] : 'N/A'; ?></td>
                    <td><?php echo $row['TenSV'] ? $row['TenSV'] : 'N/A'; ?></td>
                    <td>
                        <a href="edit_user.php?id=<?php echo $row['MaTK']; ?>"><i class="fas fa-edit"></i> Sửa</a>
                        <a href="delete_user.php?id=<?php echo $row['MaTK']; ?>" 
                           onclick="return confirm('Bạn có chắc chắn muốn xóa tài khoản này?')">
                           <i class="fas fa-trash"></i> Xóa</a>
                    </td>
                </tr>
            <?php 
                }
            } else {
                echo "<tr><td colspan='9'>Không có dữ liệu người dùng.</td></tr>";
            }
            ?>
        </tbody>
    </table>
    <a href="add_user.php" class="btn-add"><i class="fas fa-plus"></i> Thêm Người Dùng</a>
</div>

<?php
$content = ob_get_clean();

// Include layout với nội dung
include 'layout.php';

// Giải phóng tài nguyên chỉ khi $result tồn tại và là resource hợp lệ
if ($result !== false) {
    sqlsrv_free_stmt($result);
}
sqlsrv_close($conn);
?>