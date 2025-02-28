<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ./HS/index.php");
    exit();
}

include 'config.php'; // Kết nối đến cơ sở dữ liệu

// Thực hiện truy vấn để lấy số lượng câu hỏi
$sqlQuestions = "SELECT COUNT(*) AS total_questions FROM CauHoi";
$resultQuestions = sqlsrv_query($conn, $sqlQuestions);
$rowQuestions = sqlsrv_fetch_array($resultQuestions, SQLSRV_FETCH_ASSOC);
$totalQuestions = $rowQuestions['total_questions'];

// Thực hiện truy vấn để lấy số lượng người dùng
$sqlUsers = "SELECT COUNT(*) AS total_users FROM NhanVien";
$resultUsers = sqlsrv_query($conn, $sqlUsers);
$rowUsers = sqlsrv_fetch_array($resultUsers, SQLSRV_FETCH_ASSOC);
$totalUsers = $rowUsers['total_users'];

// Thực hiện truy vấn để lấy số lượng kết quả khảo sát
$sqlResults = "SELECT COUNT(*) AS total_results FROM KhaoSatNV";
$resultResults = sqlsrv_query($conn, $sqlResults);
$rowResults = sqlsrv_fetch_array($resultResults, SQLSRV_FETCH_ASSOC);
$totalResults = $rowResults['total_results'];

$page_title = "Dashboard Quản Trị Admin"; // Đặt tiêu đề trang
include 'layout.php'; // Include layout
?>

<style>
    .card {
        background-color: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        margin-bottom: 20px;
        transition: transform 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
    }

    .card h3 {
        font-size: 22px;
        color: #007bff;
        margin-bottom: 15px;
    }

    .card p {
        font-size: 16px;
        color: #666;
    }

    .stats {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }

    .stat-item {
        flex: 1;
        min-width: 200px;
        background-color: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        text-align: center;
    }

    .stat-item strong {
        display: block;
        font-size: 28px;
        color: #007bff;
        margin-top: 10px;
    }
</style>

<header>
    <h1>Dashboard Quản Trị</h1>
</header>
<div class="card">
    <h3>Tổng Quan</h3>
    <p>Chào mừng bạn đến với trang quản trị. Tại đây, bạn có thể quản lý các câu hỏi, người dùng và xem kết quả khảo sát.</p>
</div>
<div class="card">
    <h3>Thống Kê</h3>
    <div class="stats">
        <div class="stat-item">
            <p>Số lượng câu hỏi</p>
            <strong><?php echo $totalQuestions; ?></strong>
        </div>
        <div class="stat-item">
            <p>Số lượng người dùng</p>
            <strong><?php echo $totalUsers; ?></strong>
        </div>
        <div class="stat-item">
            <p>Số lượng kết quả khảo sát</p>
            <strong><?php echo $totalResults; ?></strong>
        </div>
    </div>
</div>
<div class="card">
    <h3>Biểu Đồ Thống Kê</h3>
    <canvas id="userQuestionChart"></canvas>
</div>

<footer>
    <p>© <?php echo date("Y"); ?> Your Company. All rights reserved.</p>
</footer>

<script>
    const ctx = document.getElementById('userQuestionChart').getContext('2d');
    const userQuestionChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Số lượng câu hỏi', 'Số lượng người dùng'],
            datasets: [{
                label: 'Thống Kê',
                data: [<?php echo $totalQuestions; ?>, <?php echo $totalUsers; ?>],
                backgroundColor: ['rgba(75, 192, 192, 0.2)', 'rgba(255, 99, 132, 0.2)'],
                borderColor: ['rgba(75, 192, 192, 1)', 'rgba(255, 99, 132, 1)'],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>
</body>
</html>