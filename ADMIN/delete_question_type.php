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

// Lấy ID loại câu hỏi từ tham số GET
$id = $_GET['id'];

// Xóa loại câu hỏi khỏi cơ sở dữ liệu
$sql_delete = "DELETE FROM LoaiCauHoi WHERE MaLoaiCauHoi = ?";
$params_delete = array($id);
$stmt_delete = sqlsrv_query($conn, $sql_delete, $params_delete);

if ($stmt_delete === false) {
    die("Failed to delete question type: " . print_r(sqlsrv_errors(), true));
}

// Chuyển hướng về trang quản lý loại câu hỏi với thông báo thành công
header("Location: quanlyloaicauhoi.php?success=1");
exit();

// Giải phóng tài nguyên
sqlsrv_free_stmt($stmt_delete);
sqlsrv_close($conn);
?> 