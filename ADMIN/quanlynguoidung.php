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

// Truy vấn lấy danh sách người dùng từ bảng TaiKhoan, liên kết với SinhVien và Admin
$sql = "SELECT tk.MaTK, tk.TenDangNhap, tk.MatKhau, tk.LoaiNguoiDung, 
               tk.MaSV, sv.TenSV, tk.MaAdmin, ad.TenNV 
        FROM [dbo].[TaiKhoan] tk 
        INNER JOIN [dbo].[SinhVien] sv ON tk.MaSV = sv.MaSV 
        LEFT JOIN [dbo].[Admin] ad ON tk.MaAdmin = ad.MaAdmin And tk.LoaiNguoiDung = 2 ";

// Xử lý tìm kiếm nếu có
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $_GET['search'];
    $sql .= " WHERE tk.TenDangNhap LIKE '%$search%' 
              OR sv.TenSV LIKE '%$search%' 
              OR tk.MaSV LIKE '%$search%'
              OR tk.MaTK LIKE '%$search%'";
}

$result = sqlsrv_query($conn, $sql);
if ($result === false) {
    die("Query failed: " . print_r(sqlsrv_errors(), true));
}

$page_title = "Quản Lý Người Dùng"; // Đặt tiêu đề trang

// Chuẩn bị nội dung chính để đưa vào layout
ob_start();
?>

<header>
    <h1>Quản Lý Người Dùng</h1>
</header>
<div class="card">
    <h3>Danh Sách Người Dùng</h3>
    
    <!-- Thêm form tìm kiếm -->
    <div class="search-container">
        <form action="" method="GET">
            <input type="text" name="search" placeholder="Tìm kiếm theo tên, mã SV..." 
                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit"><i class="fas fa-search"></i> Tìm kiếm</button>
            <?php if(isset($_GET['search']) && !empty($_GET['search'])): ?>
                <a href="quanlynguoidung.php" class="reset-search">Xóa tìm kiếm</a>
            <?php endif; ?>
        </form>
    </div>
    
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
            <?php while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) { ?>
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
            <?php } ?>
        </tbody>
    </table>
    <br>
    <a href="add_user.php"><i class="fas fa-plus"></i> Thêm Người Dùng</a>
</div>

<?php
$content = ob_get_clean();

// Include layout với nội dung
include 'layout.php';

// Giải phóng tài nguyên
sqlsrv_free_stmt($result);
sqlsrv_close($conn);
?>

<style>
.search-container {
    margin-bottom: 20px;
}

.search-container input[type="text"] {
    padding: 8px;
    width: 300px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.search-container button {
    padding: 8px 12px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.search-container button:hover {
    background-color: #45a049;
}

.reset-search {
    margin-left: 10px;
    color: #666;
    text-decoration: none;
}

.reset-search:hover {
    text-decoration: underline;
}
</style>