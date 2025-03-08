<?php
session_start();

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION["user_id"])) {
    header("Location: ./HS/index.php");
    exit();
}

// Kiểm tra ID sinh viên
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: student_surveys.php");
    exit();
}

$student_id = $_GET['id'];

// Kết nối cơ sở dữ liệu
include 'config.php';
if ($conn === false) {
    die("Connection failed: " . print_r(sqlsrv_errors(), true));
}

// Lấy thông tin sinh viên
$sql_student = "SELECT s.MaSV, s.TenSV, s.NgaySinh, s.GioiTinh, s.QueQuan, s.Mail, s.DienThoai, l.TenLop, l.NganhHoc, d.TenDonVi
                FROM SinhVien s
                LEFT JOIN Lop l ON s.MaLop = l.MaLop
                LEFT JOIN DonVi d ON l.MaDonVi = d.MaDonVi
                WHERE s.MaSV = ?";
$params = array($student_id);
$result_student = sqlsrv_query($conn, $sql_student, $params);

if ($result_student === false) {
    die("Query failed: " . print_r(sqlsrv_errors(), true));
}

if (sqlsrv_has_rows($result_student) === false) {
    header("Location: student_surveys.php");
    exit();
}

$student = sqlsrv_fetch_array($result_student, SQLSRV_FETCH_ASSOC);

// Lấy danh sách khảo sát của sinh viên
$sql_surveys = "SELECT k.IdKhaoSatSV, k.IdCauHoi, k.ThoiGian, c.NoiDungCauHoi, 
                p.NoiDungTraLoi, p.MucDoDanhGia, l.ChuDe, k.YKienRieng
                FROM KhaoSatSV k
                JOIN CauHoi c ON k.IdCauHoi = c.IdCauHoi
                JOIN PhuongAnTraLoi p ON k.IdPhuongAn = p.IdPhuongAn
                JOIN LoaiCauHoi l ON c.MaLoaiCauHoi = l.MaLoaiCauHoi
                WHERE k.MaSV = ?
                ORDER BY k.ThoiGian DESC";
$params = array($student_id);
$result_surveys = sqlsrv_query($conn, $sql_surveys, $params);

if ($result_surveys === false) {
    die("Query failed: " . print_r(sqlsrv_errors(), true));
}

// Nhóm khảo sát theo chủ đề
$surveys_by_topic = array();
while ($row = sqlsrv_fetch_array($result_surveys, SQLSRV_FETCH_ASSOC)) {
    $topic = $row['ChuDe'];
    if (!isset($surveys_by_topic[$topic])) {
        $surveys_by_topic[$topic] = array();
    }
    $surveys_by_topic[$topic][] = $row;
}

// Chuẩn bị nội dung chính để đưa vào layout
ob_start();
?>

    <header>
        <h1>Chi tiết Sinh viên</h1>
    </header>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary"><i class="fas fa-user-graduate me-3"></i>Chi tiết khảo sát của sinh viên</h2>
        <a href="student_surveys.php" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách
        </a>
    </div>

    <!-- Thông tin sinh viên -->
    <div class="card shadow mb-4 border-0 rounded-3">
        <div class="card-header bg-gradient-primary text-white py-3">
            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Thông tin sinh viên</h5>
        </div>
        <div class="card-body bg-light">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="bg-white p-4 rounded-3 shadow-sm h-100">
                        <h6 class="border-bottom pb-2 mb-3 text-primary">Thông tin cá nhân</h6>
                        <table class="table table-borderless">
                            <tr>
                                <th width="30%" class="text-secondary"><i class="fas fa-id-card me-2"></i>Mã sinh viên:</th>
                                <td class="fw-bold"><?php echo htmlspecialchars($student['MaSV']); ?></td>
                            </tr>
                            <tr>
                                <th class="text-secondary"><i class="fas fa-user me-2"></i>Họ tên:</th>
                                <td class="fw-bold"><?php echo htmlspecialchars($student['TenSV']); ?></td>
                            </tr>
                            <tr>
                                <th class="text-secondary"><i class="fas fa-birthday-cake me-2"></i>Ngày sinh:</th>
                                <td><?php echo $student['NgaySinh']->format('d/m/Y'); ?></td>
                            </tr>
                            <tr>
                                <th class="text-secondary"><i class="fas fa-venus-mars me-2"></i>Giới tính:</th>
                                <td><?php echo htmlspecialchars($student['GioiTinh']); ?></td>
                            </tr>
                            <tr>
                                <th class="text-secondary"><i class="fas fa-map-marker-alt me-2"></i>Quê quán:</th>
                                <td><?php echo htmlspecialchars($student['QueQuan'] ?? 'Không có thông tin'); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="bg-white p-4 rounded-3 shadow-sm h-100">
                        <h6 class="border-bottom pb-2 mb-3 text-primary">Thông tin học tập & liên hệ</h6>
                        <table class="table table-borderless">
                            <tr>
                                <th width="30%" class="text-secondary"><i class="fas fa-users me-2"></i>Lớp:</th>
                                <td><?php echo htmlspecialchars($student['TenLop']); ?></td>
                            </tr>
                            <tr>
                                <th class="text-secondary"><i class="fas fa-graduation-cap me-2"></i>Ngành học:</th>
                                <td><?php echo htmlspecialchars($student['NganhHoc']); ?></td>
                            </tr>
                            <tr>
                                <th class="text-secondary"><i class="fas fa-university me-2"></i>Đơn vị:</th>
                                <td><?php echo htmlspecialchars($student['TenDonVi']); ?></td>
                            </tr>
                            <tr>
                                <th class="text-secondary"><i class="fas fa-envelope me-2"></i>Email:</th>
                                <td><a href="mailto:<?php echo htmlspecialchars($student['Mail']); ?>"><?php echo htmlspecialchars($student['Mail']); ?></a></td>
                            </tr>
                            <tr>
                                <th class="text-secondary"><i class="fas fa-phone me-2"></i>Điện thoại:</th>
                                <td><a href="tel:<?php echo htmlspecialchars($student['DienThoai']); ?>"><?php echo htmlspecialchars($student['DienThoai']); ?></a></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Danh sách khảo sát -->
    <div class="card shadow border-0 rounded-3">
        <div class="card-header bg-gradient-primary text-white py-3">
            <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Danh sách khảo sát đã tham gia</h5>
        </div>
        <div class="card-body">
            <?php if (empty($surveys_by_topic)): ?>
                <div class="alert alert-info d-flex align-items-center">
                    <i class="fas fa-info-circle me-3 fs-4"></i>
                    <div>Sinh viên chưa tham gia khảo sát nào</div>
                </div>
            <?php else: ?>
                <!-- Sử dụng ID duy nhất cho mỗi tab để tránh xung đột -->
                <ul class="nav nav-pills mb-4" id="surveyTabs" role="tablist">
                    <?php $first = true; $index = 0; foreach ($surveys_by_topic as $topic => $surveys): $index++; ?>
                        <li class="nav-item me-2 mb-2" role="presentation">
                            <button class="nav-link <?php echo $first ? 'active' : ''; ?>" 
                                    id="tab-<?php echo $index; ?>" 
                                    data-bs-toggle="tab" 
                                    data-bs-target="#content-<?php echo $index; ?>" 
                                    type="button" 
                                    role="tab" 
                                    aria-controls="content-<?php echo $index; ?>" 
                                    aria-selected="<?php echo $first ? 'true' : 'false'; ?>">
                                <i class="fas fa-clipboard me-2"></i>
                                <?php echo htmlspecialchars($topic); ?> 
                                <span class="badge bg-white text-primary ms-2"><?php echo count($surveys); ?></span>
                            </button>
                        </li>
                    <?php $first = false; endforeach; ?>
                </ul>
                
                <div class="tab-content bg-light p-4 rounded-3" id="surveyTabsContent">
                    <?php $first = true; $index = 0; foreach ($surveys_by_topic as $topic => $surveys): $index++; ?>
                        <div class="tab-pane fade <?php echo $first ? 'show active' : ''; ?>" 
                             id="content-<?php echo $index; ?>" 
                             role="tabpanel" 
                             aria-labelledby="tab-<?php echo $index; ?>">
                            
                            <div class="table-responsive">
                                <table class="table table-hover bg-white rounded-3 shadow-sm">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="border-0">Câu hỏi</th>
                                            <th class="border-0">Câu trả lời</th>
                                            <th class="border-0">Mức độ đánh giá</th>
                                            <th class="border-0">Ý kiến riêng</th>
                                            <th class="border-0">Thời gian</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($surveys as $survey): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($survey['NoiDungCauHoi']); ?></td>
                                                <td><?php echo htmlspecialchars($survey['NoiDungTraLoi']); ?></td>
                                                <td>
                                                    <?php 
                                                        $badgeClass = '';
                                                        $icon = '';
                                                        switch($survey['MucDoDanhGia']) {
                                                            case 'Hoàn toàn không đồng ý': 
                                                                $badgeClass = 'danger'; 
                                                                $icon = 'fa-thumbs-down';
                                                                break;
                                                            case 'Không đồng ý': 
                                                                $badgeClass = 'warning'; 
                                                                $icon = 'fa-thumbs-down';
                                                                break;
                                                            case 'Đồng ý một phần': 
                                                                $badgeClass = 'info'; 
                                                                $icon = 'fa-thumbs-up';
                                                                break;
                                                            case 'Đồng ý': 
                                                                $badgeClass = 'success'; 
                                                                $icon = 'fa-thumbs-up';
                                                                break;
                                                            case 'Hoàn toàn đồng ý': 
                                                                $badgeClass = 'primary'; 
                                                                $icon = 'fa-thumbs-up';
                                                                break;
                                                            default: 
                                                                $badgeClass = 'secondary';
                                                                $icon = 'fa-question';
                                                        }
                                                    ?>
                                                    <span class="badge bg-<?php echo $badgeClass; ?> text-white">
                                                        <i class="fas <?php echo $icon; ?> me-1"></i>
                                                        <?php echo htmlspecialchars($survey['MucDoDanhGia']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if (!empty($survey['YKienRieng'])): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-primary view-opinion" 
                                                                data-opinion="<?php echo htmlspecialchars($survey['YKienRieng']); ?>"
                                                                data-question="<?php echo htmlspecialchars($survey['NoiDungCauHoi']); ?>">
                                                            <i class="fas fa-comment-dots me-1"></i>Xem ý kiến
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="text-muted"><i class="fas fa-ban me-1"></i>Không có</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><i class="far fa-clock me-1"></i><?php echo $survey['ThoiGian']->format('d/m/Y H:i'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php $first = false; endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Thêm Bootstrap CSS và JS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    }
    
    .nav-pills .nav-link.active {
        background-color: #4e73df;
    }
    
    .nav-pills .nav-link {
        color: #4e73df;
        font-weight: 500;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
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
</style>

<!-- Thêm một modal duy nhất ở cuối trang -->
<div class="modal fade" id="opinionModal" tabindex="-1" aria-labelledby="opinionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="opinionModalLabel"><i class="fas fa-comment-alt me-2"></i>Ý kiến riêng</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <p class="fw-bold text-primary mb-2"><i class="fas fa-question-circle me-2"></i>Câu hỏi:</p>
                    <div class="p-3 bg-light rounded" id="modalQuestion"></div>
                </div>
                <div>
                    <p class="fw-bold text-primary mb-2"><i class="fas fa-comment me-2"></i>Ý kiến riêng:</p>
                    <div class="p-3 bg-light rounded border-start border-primary border-4" id="modalOpinion"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Khởi tạo các tab Bootstrap
    var triggerTabList = [].slice.call(document.querySelectorAll('#surveyTabs button'))
    triggerTabList.forEach(function(triggerEl) {
        var tabTrigger = new bootstrap.Tab(triggerEl)
        triggerEl.addEventListener('click', function(event) {
            event.preventDefault()
            tabTrigger.show()
        })
    })
    
    // Xử lý nút xem ý kiến
    document.querySelectorAll('.view-opinion').forEach(function(button) {
        button.addEventListener('click', function() {
            var opinion = this.getAttribute('data-opinion');
            var question = this.getAttribute('data-question');
            
            document.getElementById('modalQuestion').textContent = question;
            document.getElementById('modalOpinion').innerHTML = opinion.replace(/\n/g, '<br>');
            
            var opinionModal = new bootstrap.Modal(document.getElementById('opinionModal'));
            opinionModal.show();
        });
    });
    
    // Đảm bảo nút đóng hoạt động
    document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(function(button) {
        button.addEventListener('click', function() {
            var modalElement = this.closest('.modal');
            var modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            } else {
                // Fallback nếu không lấy được instance
                var modal = new bootstrap.Modal(modalElement);
                modal.hide();
            }
        });
    });
})
</script>

<?php
$content = ob_get_clean();
$pageTitle = "Chi tiết khảo sát của sinh viên: " . htmlspecialchars($student['TenSV']);
include 'layout.php';
?> 