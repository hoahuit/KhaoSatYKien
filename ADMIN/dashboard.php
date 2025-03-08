<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ./HS/index.php");
    exit();
}

include 'config.php'; // Kết nối đến cơ sở dữ liệu
if ($conn === false) {
    die("Connection failed: " . print_r(sqlsrv_errors(), true));
}

// Thực hiện truy vấn để lấy số lượng câu hỏi
$sqlQuestions = "SELECT COUNT(*) AS total_questions FROM [dbo].[CauHoi]";
$resultQuestions = sqlsrv_query($conn, $sqlQuestions);
if ($resultQuestions === false) {
    die("Query failed (CauHoi): " . print_r(sqlsrv_errors(), true));
}
$rowQuestions = sqlsrv_fetch_array($resultQuestions, SQLSRV_FETCH_ASSOC);
$totalQuestions = $rowQuestions['total_questions'];

// Thực hiện truy vấn để lấy số lượng người dùng
$sqlUsers = "SELECT COUNT(*) AS total_users FROM [dbo].[SinhVien]";
$resultUsers = sqlsrv_query($conn, $sqlUsers);
if ($resultUsers === false) {
    die("Query failed (SinhVien): " . print_r(sqlsrv_errors(), true));
}
$rowUsers = sqlsrv_fetch_array($resultUsers, SQLSRV_FETCH_ASSOC);
$totalUsers = $rowUsers['total_users'];

// Thực hiện truy vấn để lấy số lượng kết quả khảo sát
$sqlResults = "SELECT COUNT(*) AS total_results FROM [dbo].[KhaoSatSV]";
$resultResults = sqlsrv_query($conn, $sqlResults);
if ($resultResults === false) {
    die("Query failed (KhaoSatSV): " . print_r(sqlsrv_errors(), true));
}
$rowResults = sqlsrv_fetch_array($resultResults, SQLSRV_FETCH_ASSOC);
$totalResults = $rowResults['total_results'];

// Lấy dữ liệu cho biểu đồ - số lượng khảo sát theo tháng
$sqlSurveysByMonth = "SELECT MONTH(ThoiGian) as month, COUNT(*) as count 
                      FROM [dbo].[KhaoSatSV] 
                      WHERE YEAR(ThoiGian) = YEAR(GETDATE()) 
                      GROUP BY MONTH(ThoiGian) 
                      ORDER BY month";
$resultSurveysByMonth = sqlsrv_query($conn, $sqlSurveysByMonth);
if ($resultSurveysByMonth === false) {
    die("Query failed (KhaoSatSV by month): " . print_r(sqlsrv_errors(), true));
}

$months = [];
$surveyCounts = [];
while ($row = sqlsrv_fetch_array($resultSurveysByMonth, SQLSRV_FETCH_ASSOC)) {
    $monthNames = ['', 'Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 
                   'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'];
    $months[] = $monthNames[$row['month']];
    $surveyCounts[] = $row['count'];
}

// Thêm truy vấn để lấy dữ liệu cho biểu đồ phân bố câu hỏi theo chủ đề
$sqlQuestionsByTopic = "SELECT l.ChuDe, COUNT(c.IdCauHoi) as count 
                        FROM [dbo].[LoaiCauHoi] l
                        LEFT JOIN [dbo].[CauHoi] c ON l.MaLoaiCauHoi = c.MaLoaiCauHoi
                        GROUP BY l.ChuDe
                        ORDER BY count DESC";
$resultQuestionsByTopic = sqlsrv_query($conn, $sqlQuestionsByTopic);
if ($resultQuestionsByTopic === false) {
    die("Query failed (Questions by Topic): " . print_r(sqlsrv_errors(), true));
}

$topics = [];
$questionCounts = [];
while ($row = sqlsrv_fetch_array($resultQuestionsByTopic, SQLSRV_FETCH_ASSOC)) {
    $topics[] = $row['ChuDe'];
    $questionCounts[] = $row['count'];
}

// Truy vấn để lấy dữ liệu cho biểu đồ phân bố đánh giá - SỬA LỖI
$sqlRatingDistribution = "SELECT p.MucDoDanhGia, COUNT(k.IdKhaoSatSV) as count 
                          FROM [dbo].[PhuongAnTraLoi] p
                          JOIN [dbo].[KhaoSatSV] k ON p.IdPhuongAn = k.IdPhuongAn
                          GROUP BY p.MucDoDanhGia
                          ORDER BY MIN(p.GiaTriTrungBinh)";
$resultRatingDistribution = sqlsrv_query($conn, $sqlRatingDistribution);
if ($resultRatingDistribution === false) {
    die("Query failed (Rating Distribution): " . print_r(sqlsrv_errors(), true));
}

$ratings = [];
$ratingCounts = [];
while ($row = sqlsrv_fetch_array($resultRatingDistribution, SQLSRV_FETCH_ASSOC)) {
    $ratings[] = $row['MucDoDanhGia'];
    $ratingCounts[] = $row['count'];
}

// Truy vấn để lấy dữ liệu cho biểu đồ sinh viên theo lớp
$sqlStudentsByClass = "SELECT l.TenLop, COUNT(s.MaSV) as count 
                       FROM [dbo].[Lop] l
                       LEFT JOIN [dbo].[SinhVien] s ON l.MaLop = s.MaLop
                       GROUP BY l.TenLop";
$resultStudentsByClass = sqlsrv_query($conn, $sqlStudentsByClass);
if ($resultStudentsByClass === false) {
    die("Query failed (Students by Class): " . print_r(sqlsrv_errors(), true));
}

$classes = [];
$studentCounts = [];
while ($row = sqlsrv_fetch_array($resultStudentsByClass, SQLSRV_FETCH_ASSOC)) {
    $classes[] = $row['TenLop'];
    $studentCounts[] = $row['count'];
}

// Truy vấn để lấy dữ liệu cho biểu đồ tỷ lệ giới tính sinh viên
$sqlGenderDistribution = "SELECT GioiTinh, COUNT(*) as count 
                          FROM [dbo].[SinhVien]
                          WHERE GioiTinh IS NOT NULL
                          GROUP BY GioiTinh";
$resultGenderDistribution = sqlsrv_query($conn, $sqlGenderDistribution);
if ($resultGenderDistribution === false) {
    die("Query failed (Gender Distribution): " . print_r(sqlsrv_errors(), true));
}

$genders = [];
$genderCounts = [];
while ($row = sqlsrv_fetch_array($resultGenderDistribution, SQLSRV_FETCH_ASSOC)) {
    $genders[] = $row['GioiTinh'];
    $genderCounts[] = $row['count'];
}

$page_title = "Dashboard Quản Trị Admin"; // Đặt tiêu đề trang

// Chuẩn bị nội dung chính để đưa vào layout
ob_start();
?>

<header>
    <h1>Dashboard Quản Trị</h1>
</header>

<div class="dashboard-container">
    <div class="row">
        <div class="col-md-12">
            <div class="card welcome-card">
                <div class="card-body">
                    <h3><i class="fas fa-tachometer-alt mr-2"></i> Tổng Quan Hệ Thống</h3>
                    <p>Chào mừng bạn đến với trang quản trị. Tại đây, bạn có thể quản lý các câu hỏi, người dùng và xem kết quả khảo sát.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card stat-card bg-gradient-primary">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo number_format($totalQuestions); ?></h3>
                        <p>Câu hỏi</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card bg-gradient-success">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo number_format($totalUsers); ?></h3>
                        <p>Người dùng</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card bg-gradient-info">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo number_format($totalResults); ?></h3>
                        <p>Kết quả khảo sát</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card chart-card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-line mr-2"></i> Biểu Đồ Khảo Sát Theo Tháng</h3>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="surveyChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Thêm biểu đồ phân bố câu hỏi theo chủ đề -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card chart-card">
                <div class="card-header">
                    <h3><i class="fas fa-pie-chart mr-2"></i> Phân Bố Câu Hỏi Theo Chủ Đề</h3>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="topicChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Thêm biểu đồ phân bố đánh giá -->
        <div class="col-md-6">
            <div class="card chart-card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-bar mr-2"></i> Phân Bố Đánh Giá</h3>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="ratingChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Thêm biểu đồ sinh viên theo lớp và tỷ lệ giới tính -->
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card chart-card">
                <div class="card-header">
                    <h3><i class="fas fa-users mr-2"></i> Sinh Viên Theo Lớp</h3>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="classChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card chart-card">
                <div class="card-header">
                    <h3><i class="fas fa-venus-mars mr-2"></i> Tỷ Lệ Giới Tính</h3>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="genderChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-cogs mr-2"></i> Quản lý hệ thống</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="student_surveys.php" class="btn btn-info btn-block">
                                <i class="fas fa-users mr-2"></i> Xem khảo sát theo sinh viên
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-container {
    padding: 20px;
}

.stat-card {
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.bg-gradient-primary {
    background: linear-gradient(45deg, #4e73df, #6f86d6);
    color: white;
}

.bg-gradient-success {
    background: linear-gradient(45deg, #1cc88a, #36b9cc);
    color: white;
}

.bg-gradient-info {
    background: linear-gradient(45deg, #36b9cc, #4e73df);
    color: white;
}

.stat-card .card-body {
    display: flex;
    align-items: center;
    padding: 25px;
}

.stat-icon {
    font-size: 2.5rem;
    margin-right: 20px;
    opacity: 0.8;
}

.stat-details h3 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 5px;
}

.stat-details p {
    font-size: 1rem;
    margin: 0;
    opacity: 0.8;
}

.chart-card {
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.chart-card .card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
    padding: 15px 20px;
}

.chart-card .card-header h3 {
    margin: 0;
    font-size: 1.25rem;
    color: #4e73df;
}

.chart-container {
    padding: 15px;
    height: 300px;
}

.welcome-card {
    background: linear-gradient(120deg, #f6f9fc, #edf2f7);
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}

.welcome-card h3 {
    color: #2c3e50;
    margin-bottom: 15px;
}

.welcome-card p {
    color: #7f8c8d;
    font-size: 1.1rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Biểu đồ khảo sát theo tháng (đã có sẵn)
    var months = <?php echo json_encode($months); ?>;
    var surveyCounts = <?php echo json_encode($surveyCounts); ?>;
    
    var ctx = document.getElementById('surveyChart').getContext('2d');
    var surveyChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: months,
            datasets: [{
                label: 'Số lượng khảo sát',
                data: surveyCounts,
                backgroundColor: 'rgba(78, 115, 223, 0.7)',
                borderColor: 'rgba(78, 115, 223, 1)',
                borderWidth: 1,
                borderRadius: 5,
                maxBarThickness: 50
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        precision: 0
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    backgroundColor: 'rgba(44, 62, 80, 0.9)',
                    titleFont: {
                        size: 14
                    },
                    bodyFont: {
                        size: 13
                    },
                    padding: 10,
                    cornerRadius: 5
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeOutQuart'
            }
        }
    });
    
    // Biểu đồ phân bố câu hỏi theo chủ đề
    var topics = <?php echo json_encode($topics); ?>;
    var questionCounts = <?php echo json_encode($questionCounts); ?>;
    
    var topicCtx = document.getElementById('topicChart').getContext('2d');
    var topicChart = new Chart(topicCtx, {
        type: 'pie',
        data: {
            labels: topics,
            datasets: [{
                data: questionCounts,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)',
                    'rgba(255, 159, 64, 0.7)',
                    'rgba(199, 199, 199, 0.7)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(199, 199, 199, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        padding: 20,
                        boxWidth: 15
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(44, 62, 80, 0.9)',
                    titleFont: {
                        size: 14
                    },
                    bodyFont: {
                        size: 13
                    },
                    padding: 10,
                    cornerRadius: 5
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeOutQuart'
            }
        }
    });
    
    // Biểu đồ phân bố đánh giá
    var ratings = <?php echo json_encode($ratings); ?>;
    var ratingCounts = <?php echo json_encode($ratingCounts); ?>;
    
    var ratingCtx = document.getElementById('ratingChart').getContext('2d');
    var ratingChart = new Chart(ratingCtx, {
        type: 'bar',
        data: {
            labels: ratings,
            datasets: [{
                label: 'Số lượng đánh giá',
                data: ratingCounts,
                backgroundColor: 'rgba(75, 192, 192, 0.7)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1,
                borderRadius: 5,
                maxBarThickness: 50
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        precision: 0
                    }
                },
                y: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    backgroundColor: 'rgba(44, 62, 80, 0.9)',
                    titleFont: {
                        size: 14
                    },
                    bodyFont: {
                        size: 13
                    },
                    padding: 10,
                    cornerRadius: 5
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeOutQuart'
            }
        }
    });
    
    // Biểu đồ sinh viên theo lớp
    var classes = <?php echo json_encode($classes); ?>;
    var studentCounts = <?php echo json_encode($studentCounts); ?>;
    
    var classCtx = document.getElementById('classChart').getContext('2d');
    var classChart = new Chart(classCtx, {
        type: 'bar',
        data: {
            labels: classes,
            datasets: [{
                label: 'Số lượng sinh viên',
                data: studentCounts,
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
                borderRadius: 5,
                maxBarThickness: 80
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        precision: 0
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    backgroundColor: 'rgba(44, 62, 80, 0.9)',
                    titleFont: {
                        size: 14
                    },
                    bodyFont: {
                        size: 13
                    },
                    padding: 10,
                    cornerRadius: 5
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeOutQuart'
            }
        }
    });
    
    // Biểu đồ tỷ lệ giới tính
    var genders = <?php echo json_encode($genders); ?>;
    var genderCounts = <?php echo json_encode($genderCounts); ?>;
    
    var genderCtx = document.getElementById('genderChart').getContext('2d');
    var genderChart = new Chart(genderCtx, {
        type: 'doughnut',
        data: {
            labels: genders,
            datasets: [{
                data: genderCounts,
                backgroundColor: [
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(255, 206, 86, 0.7)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(255, 206, 86, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        boxWidth: 15
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(44, 62, 80, 0.9)',
                    titleFont: {
                        size: 14
                    },
                    bodyFont: {
                        size: 13
                    },
                    padding: 10,
                    cornerRadius: 5
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeOutQuart'
            }
        }
    });
});
</script>

<?php
$content = ob_get_clean();

// Include layout với nội dung
include 'layout.php';

// Giải phóng tài nguyên
sqlsrv_free_stmt($resultQuestions);
sqlsrv_free_stmt($resultUsers);
sqlsrv_free_stmt($resultResults);
sqlsrv_free_stmt($resultSurveysByMonth);
sqlsrv_free_stmt($resultQuestionsByTopic);
sqlsrv_free_stmt($resultRatingDistribution);
sqlsrv_free_stmt($resultStudentsByClass);
sqlsrv_free_stmt($resultGenderDistribution);
sqlsrv_close($conn);
?>