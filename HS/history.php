<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

// Thêm error reporting để debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Lấy thông tin người dùng
$user_id = $_SESSION["user_id"];
$sql = "SELECT t.LoaiNguoiDung, t.MaSV
        FROM TaiKhoan t 
        WHERE t.MaTK = ?";
$params = array($user_id);
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die("Error fetching user type: " . print_r(sqlsrv_errors(), true));
}

$row = sqlsrv_fetch_array($stmt);
if ($row === false) {
    die("Error fetching user type");
}

$user_type = $row["LoaiNguoiDung"];
$user_identifier = $row["MaSV"]; // Chỉ lấy MaSV vì chỉ có bảng KhaoSatSV

// Truy vấn lịch sử khảo sát - chỉ từ bảng KhaoSatSV
$sql_history = "
    SELECT 
        lch.ChuDe,
        ch.NoiDungCauHoi,
        k.ThoiGian AS NgayThucHien,
        pa.NoiDungTraLoi AS DapAn
    FROM KhaoSatSV k
    JOIN CauHoi ch ON k.IdCauHoi = ch.IdCauHoi
    JOIN LoaiCauHoi lch ON ch.MaLoaiCauHoi = lch.MaLoaiCauHoi
    JOIN PhuongAnTraLoi pa ON k.IdPhuongAn = pa.IdPhuongAn
    WHERE k.MaSV = ?
    ORDER BY NgayThucHien DESC";

$params_history = array($user_identifier);
$stmt_history = sqlsrv_query($conn, $sql_history, $params_history);

// Kiểm tra xem truy vấn có thành công không
if ($stmt_history === false) {
    die("Error fetching survey history: " . print_r(sqlsrv_errors(), true));
}

ob_start();
?>

<div class="container py-5">
    <h3 class="mb-4">Lịch sử khảo sát của bạn</h3>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Chủ đề</th>
                    <th>Câu hỏi</th>
                    <th>Ngày thực hiện</th>
                    <th>Kết quả</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (sqlsrv_has_rows($stmt_history)) {
                    while ($history = sqlsrv_fetch_array($stmt_history, SQLSRV_FETCH_ASSOC)) {
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($history['ChuDe']); ?></td>
                            <td><?php echo htmlspecialchars($history['NoiDungCauHoi']); ?></td>
                            <td><?php echo date_format($history['NgayThucHien'], 'd/m/Y H:i'); ?></td>
                            <td><?php echo htmlspecialchars($history['DapAn']); ?></td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="4" class="text-center">Bạn chưa tham gia khảo sát nào.</td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.table {
    border-radius: 10px;
    overflow: hidden;
}

.table th, .table td {
    vertical-align: middle;
}

.table th {
    background-color: #007bff;
    color: white;
}

.table-striped tbody tr:nth-of-type(odd) {
    background-color: #f9f9f9;
}
</style>

<?php
$content = ob_get_clean();
$pageTitle = 'Lịch sử khảo sát - Hệ thống Khảo sát';
include 'layout.php';
?> 