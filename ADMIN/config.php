<?php
$serverName = "THLONE\SQLEXPRESS";
$database = "KhaoSatYKien";
$uid = "";  // Để trống vì dùng Windows Authentication
$pwd = "";  // Để trống vì dùng Windows Authentication

$connectionInfo = array(
    "Database" => $database,
    "CharacterSet" => "UTF-8"
);

$conn = sqlsrv_connect($serverName, $connectionInfo);

if (!$conn) {
    die("Kết nối thất bại: " . print_r(sqlsrv_errors(), true));
}
?>
