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

// Xóa tài khoản (do có foreign key với ON DELETE CASCADE, SinhVien sẽ tự động bị xóa)
$sql = "DELETE FROM TaiKhoan WHERE MaTK = ?";
$params = array($id);
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die("Delete failed: " . print_r(sqlsrv_errors(), true));
}

sqlsrv_close($conn);
header("Location: user_management.php?success=3");
exit();
?>