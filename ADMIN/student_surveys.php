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

// Xử lý tìm kiếm
$whereClauses = [];
$params = [];

// Kiểm tra nếu form tìm kiếm được gửi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    if (!empty($_POST['searchMaSV'])) {
        $whereClauses[] = "s.MaSV LIKE ?";
        $params[] = '%' . $_POST['searchMaSV'] . '%';
    }
    
    if (!empty($_POST['searchTenSV'])) {
        $whereClauses[] = "s.TenSV LIKE ?";
        $params[] = '%' . $_POST['searchTenSV'] . '%';
    }
    
    if (!empty($_POST['searchLop'])) {
        $whereClauses[] = "l.TenLop LIKE ?";
        $params[] = '%' . $_POST['searchLop'] . '%';
    }
    
    if (!empty($_POST['searchDonVi'])) {
        $whereClauses[] = "d.TenDonVi LIKE ?";
        $params[] = '%' . $_POST['searchDonVi'] . '%';
    }
}

// Xây dựng câu truy vấn SQL với điều kiện tìm kiếm
$sql_students = "SELECT DISTINCT s.MaSV, s.TenSV, s.Mail, s.DienThoai, l.TenLop, l.NganhHoc, d.TenDonVi,
                (SELECT COUNT(*) FROM KhaoSatSV WHERE MaSV = s.MaSV) AS SoKhaoSat,
                (SELECT MAX(ThoiGian) FROM KhaoSatSV WHERE MaSV = s.MaSV) AS LanKhaoSatCuoi
                FROM SinhVien s
                LEFT JOIN Lop l ON s.MaLop = l.MaLop
                LEFT JOIN DonVi d ON l.MaDonVi = d.MaDonVi
                WHERE EXISTS (SELECT 1 FROM KhaoSatSV WHERE MaSV = s.MaSV)";

// Thêm điều kiện tìm kiếm nếu có
if (!empty($whereClauses)) {
    $sql_students .= " AND " . implode(" AND ", $whereClauses);
}

$sql_students .= " ORDER BY LanKhaoSatCuoi DESC";

// Thực thi truy vấn với tham số
if (!empty($params)) {
    $stmt = sqlsrv_prepare($conn, $sql_students, $params);
    if ($stmt === false) {
        die("Query preparation failed: " . print_r(sqlsrv_errors(), true));
    }
    
    if (sqlsrv_execute($stmt) === false) {
        die("Query execution failed: " . print_r(sqlsrv_errors(), true));
    }
    
    $result_students = $stmt;
} else {
    $result_students = sqlsrv_query($conn, $sql_students);
    if ($result_students === false) {
        die("Query failed: " . print_r(sqlsrv_errors(), true));
    }
}

// Chuẩn bị nội dung chính để đưa vào layout
ob_start();
?>
    <header>
        <h1>Sinh viên tham gia khảo sát</h1>
    </header>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary"><i class="fas fa-users-class me-3"></i>Danh sách sinh viên tham gia khảo sát</h2>
        <a href="dashboard.php" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>Quay lại Dashboard
        </a>
    </div>

    <!-- Thống kê tổng quan -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="fas fa-users fa-2x text-primary"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Tổng số sinh viên</h6>
                        <h3 class="mb-0 fw-bold">
                            <?php
                            $sql_count = "SELECT COUNT(DISTINCT MaSV) AS total FROM KhaoSatSV";
                            $result_count = sqlsrv_query($conn, $sql_count);
                            $row_count = sqlsrv_fetch_array($result_count, SQLSRV_FETCH_ASSOC);
                            echo $row_count['total'];
                            ?>
                        </h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="fas fa-clipboard-check fa-2x text-success"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Tổng số khảo sát</h6>
                        <h3 class="mb-0 fw-bold">
                            <?php
                            $sql_count = "SELECT COUNT(*) AS total FROM KhaoSatSV";
                            $result_count = sqlsrv_query($conn, $sql_count);
                            $row_count = sqlsrv_fetch_array($result_count, SQLSRV_FETCH_ASSOC);
                            echo $row_count['total'];
                            ?>
                        </h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                        <i class="fas fa-calendar-alt fa-2x text-info"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Khảo sát gần nhất</h6>
                        <h3 class="mb-0 fw-bold">
                            <?php
                            $sql_latest = "SELECT MAX(ThoiGian) AS latest FROM KhaoSatSV";
                            $result_latest = sqlsrv_query($conn, $sql_latest);
                            $row_latest = sqlsrv_fetch_array($result_latest, SQLSRV_FETCH_ASSOC);
                            echo $row_latest['latest'] ? $row_latest['latest']->format('d/m/Y') : 'N/A';
                            ?>
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tìm kiếm sinh viên -->
    <div class="card shadow mb-4 border-0 rounded-3">
        <div class="card-header bg-gradient-primary text-white py-3">
            <h5 class="mb-0"><i class="fas fa-search me-2"></i>Tìm kiếm sinh viên</h5>
        </div>
        <div class="card-body bg-light">
            <form id="searchForm" method="POST" action="" class="row g-3">
                <div class="col-md-3">
                    <label for="searchMaSV" class="form-label">Mã sinh viên</label>
                    <input type="text" class="form-control" id="searchMaSV" name="searchMaSV" 
                           placeholder="Nhập mã sinh viên..." 
                           value="<?php echo isset($_POST['searchMaSV']) ? htmlspecialchars($_POST['searchMaSV']) : ''; ?>">
                </div>
                <div class="col-md-3">
                    <label for="searchTenSV" class="form-label">Họ tên</label>
                    <input type="text" class="form-control" id="searchTenSV" name="searchTenSV" 
                           placeholder="Nhập tên sinh viên..."
                           value="<?php echo isset($_POST['searchTenSV']) ? htmlspecialchars($_POST['searchTenSV']) : ''; ?>">
                </div>
                <div class="col-md-3">
                    <label for="searchLop" class="form-label">Lớp</label>
                    <input type="text" class="form-control" id="searchLop" name="searchLop" 
                           placeholder="Nhập tên lớp..."
                           value="<?php echo isset($_POST['searchLop']) ? htmlspecialchars($_POST['searchLop']) : ''; ?>">
                </div>
                <div class="col-md-3">
                    <label for="searchDonVi" class="form-label">Đơn vị</label>
                    <input type="text" class="form-control" id="searchDonVi" name="searchDonVi" 
                           placeholder="Nhập tên đơn vị..."
                           value="<?php echo isset($_POST['searchDonVi']) ? htmlspecialchars($_POST['searchDonVi']) : ''; ?>">
                </div>
                <div class="col-12 text-center mt-4">
                    <button type="submit" name="search" class="btn btn-primary px-4 py-2 me-2">
                        <i class="fas fa-search me-2"></i>Tìm kiếm
                    </button>
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-outline-secondary px-4 py-2">
                        <i class="fas fa-redo me-2"></i>Làm mới
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Danh sách sinh viên -->
    <div class="card shadow border-0 rounded-3">
        <div class="card-header bg-gradient-primary text-white py-3">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Danh sách sinh viên đã tham gia khảo sát</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="studentsTable">
                    <thead class="table-dark">
                        <tr>
                            <th class="border-0">Mã SV</th>
                            <th class="border-0">Họ tên</th>
                            <th class="border-0">Lớp</th>
                            <th class="border-0">Ngành học</th>
                            <th class="border-0">Đơn vị</th>
                            <th class="border-0">Email</th>
                            <th class="border-0">Số điện thoại</th>
                            <th class="border-0">Số khảo sát</th>
                            <th class="border-0">Lần khảo sát cuối</th>
                            <th class="border-0">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = sqlsrv_fetch_array($result_students, SQLSRV_FETCH_ASSOC)): ?>
                            <tr>
                                <td class="fw-bold"><?php echo htmlspecialchars($row['MaSV']); ?></td>
                                <td><?php echo htmlspecialchars($row['TenSV']); ?></td>
                                <td><span class="badge bg-info text-white"><?php echo htmlspecialchars($row['TenLop']); ?></span></td>
                                <td><?php echo htmlspecialchars($row['NganhHoc']); ?></td>
                                <td><?php echo htmlspecialchars($row['TenDonVi']); ?></td>
                                <td><a href="mailto:<?php echo htmlspecialchars($row['Mail']); ?>" class="text-decoration-none"><i class="fas fa-envelope text-muted me-2"></i><?php echo htmlspecialchars($row['Mail']); ?></a></td>
                                <td><a href="tel:<?php echo htmlspecialchars($row['DienThoai']); ?>" class="text-decoration-none"><i class="fas fa-phone text-muted me-2"></i><?php echo htmlspecialchars($row['DienThoai']); ?></a></td>
                                <td><span class="badge bg-success"><?php echo $row['SoKhaoSat']; ?></span></td>
                                <td><?php echo $row['LanKhaoSatCuoi'] ? '<i class="far fa-clock text-muted me-2"></i>' . $row['LanKhaoSatCuoi']->format('d/m/Y H:i') : 'N/A'; ?></td>
                                <td>
                                    <a href="student_survey_detail.php?id=<?php echo $row['MaSV']; ?>" 
                                       class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i>Xem chi tiết
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        
                        <?php if (sqlsrv_has_rows($result_students) === false): ?>
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <div class="alert alert-info d-flex align-items-center">
                                        <i class="fas fa-info-circle me-3 fs-4"></i>
                                        <div>Chưa có sinh viên nào tham gia khảo sát</div>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Thêm Bootstrap CSS và JS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    }
    
    .table th {
        font-weight: 600;
    }
    
    .card {
        overflow: hidden;
    }
    
    .card-header {
        border-bottom: 0;
    }
    
    .text-primary {
        color: #4e73df !important;
    }
    
    .btn-outline-primary {
        color: #4e73df;
        border-color: #4e73df;
    }
    
    .btn-outline-primary:hover {
        background-color: #4e73df;
        border-color: #4e73df;
    }
    
    .btn-primary {
        background-color: #4e73df;
        border-color: #4e73df;
    }
    
    .btn-primary:hover {
        background-color: #224abe;
        border-color: #224abe;
    }
    
    .badge {
        font-weight: 500;
        padding: 0.5em 0.8em;
    }
    
    .table > :not(caption) > * > * {
        padding: 0.75rem 1rem;
    }
    
    .dataTables_wrapper .dataTables_length, 
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 1rem;
    }
    
    .dataTables_wrapper .dataTables_info, 
    .dataTables_wrapper .dataTables_paginate {
        margin-top: 1rem;
    }
    
    .page-item.active .page-link {
        background-color: #4e73df;
        border-color: #4e73df;
    }
    
    .page-link {
        color: #4e73df;
    }
</style>

<script>
$(document).ready(function() {
    // Khởi tạo DataTable với tính năng phân trang
    var table = $('#studentsTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/vi.json'
        },
        responsive: true,
        order: [[8, 'desc']], // Sắp xếp theo lần khảo sát cuối
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Tất cả"]],
        dom: '<"row"<"col-md-6"l><"col-md-6"f>>rtip'
    });
});
</script>

<?php
$content = ob_get_clean();
$pageTitle = "Danh sách sinh viên đã tham gia khảo sát";
include 'layout.php';
?>