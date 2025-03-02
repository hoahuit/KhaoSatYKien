<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #1cc88a;
            --dark-color: #2c3e50;
            --light-color: #f8f9fc;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
            --info-color: #36b9cc;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: var(--light-color);
            transition: all 0.3s ease;
        }
        
        header {
            background: linear-gradient(135deg, var(--primary-color), var(--info-color));
            color: #fff;
            padding: 25px;
            text-align: center;
            font-size: 28px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        footer {
            text-align: center;
            padding: 20px;
            background-color: var(--dark-color);
            color: #fff;
            margin-top: auto;
            border-radius: 0;
            box-shadow: 0 -4px 10px rgba(0,0,0,0.1);
        }
        
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, var(--dark-color), #1a252f);
            color: #fff;
            position: fixed;
            height: 100vh;
            padding: 20px 0;
            box-shadow: 4px 0 10px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
        }
        
        .sidebar h2 {
            font-size: 28px;
            text-align: center;
            margin-bottom: 30px;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 2px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            padding: 15px 25px;
            display: flex;
            align-items: center;
            font-size: 16px;
            border-radius: 5px;
            margin: 8px 15px;
            transition: all 0.3s ease;
        }
        
        .sidebar a i {
            margin-right: 15px;
            font-size: 18px;
            width: 25px;
            text-align: center;
        }
        
        .sidebar a:hover, .sidebar a.active {
            background-color: var(--primary-color);
            color: #fff;
            transform: translateX(5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .main-content {
            margin-left: 280px;
            padding: 30px;
            flex: 1;
            background-color: var(--light-color);
            transition: all 0.3s ease;
        }
        
        .card {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            border: none;
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .card h3 {
            font-size: 24px;
            color: var(--primary-color);
            margin-bottom: 20px;
            font-weight: 600;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
            display: inline-block;
        }
        
        .card p {
            font-size: 16px;
            color: #555;
            line-height: 1.6;
        }
        
        .stats {
            display: flex;
            gap: 25px;
            flex-wrap: wrap;
        }
        
        .stat-item {
            flex: 1;
            min-width: 240px;
            background-color: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            text-align: center;
            transition: all 0.3s ease;
            border-left: 5px solid var(--primary-color);
        }
        
        .stat-item:nth-child(2) {
            border-left-color: var(--secondary-color);
        }
        
        .stat-item:nth-child(3) {
            border-left-color: var(--warning-color);
        }
        
        .stat-item:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .stat-item i {
            font-size: 36px;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .stat-item strong {
            display: block;
            font-size: 36px;
            color: var(--dark-color);
            margin: 10px 0;
            font-weight: 700;
        }
        
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 20px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        th, td {
            padding: 18px 25px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background-color: var(--primary-color);
            color: #fff;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 14px;
            letter-spacing: 1px;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        tr:hover {
            background-color: rgba(78, 115, 223, 0.05);
        }
        
        td a {
            text-decoration: none;
            color: var(--primary-color);
            margin-right: 15px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
        }
        
        td a i {
            margin-right: 5px;
        }
        
        td a:hover {
            color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .btn-add {
            display: inline-block;
            padding: 12px 25px;
            background: linear-gradient(135deg, var(--primary-color), #3a5cbe);
            color: #fff;
            border-radius: 50px;
            margin-top: 20px;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(78, 115, 223, 0.3);
            font-weight: 500;
            display: inline-flex;
            align-items: center;
        }
        
        .btn-add i {
            margin-right: 8px;
        }
        
        .btn-add:hover {
            background: linear-gradient(135deg, #3a5cbe, var(--primary-color));
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(78, 115, 223, 0.4);
            color: #fff;
            text-decoration: none;
        }
        
        /* Success message styling */
        .success {
            background-color: rgba(28, 200, 138, 0.1);
            color: var(--secondary-color);
            padding: 15px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid var(--secondary-color);
            font-weight: 500;
        }
        
        /* Responsive design */
        @media (max-width: 992px) {
            .sidebar {
                width: 220px;
            }
            .main-content {
                margin-left: 220px;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }
            .sidebar h2 {
                font-size: 0;
                margin-bottom: 40px;
            }
            .sidebar h2::first-letter {
                font-size: 24px;
            }
            .sidebar a span {
                display: none;
            }
            .sidebar a {
                padding: 15px;
                justify-content: center;
            }
            .sidebar a i {
                margin-right: 0;
                font-size: 20px;
            }
            .main-content {
                margin-left: 70px;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 15px;
            }
            th, td {
                padding: 12px 15px;
            }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="dashboard.php" <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'class="active"' : ''; ?>>
            <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
        </a>
        <a href="quanlynguoidung.php" <?php echo basename($_SERVER['PHP_SELF']) == 'quanlynguoidung.php' ? 'class="active"' : ''; ?>>
            <i class="fas fa-users"></i> <span>Người dùng</span>
        </a>
        <a href="quanlyloaicauhoi.php" <?php echo basename($_SERVER['PHP_SELF']) == 'quanlyloaicauhoi.php' ? 'class="active"' : ''; ?>>
            <i class="fas fa-list-alt"></i> <span>Loại câu hỏi</span>
        </a>
        <a href="quanlycauhoi.php" <?php echo basename($_SERVER['PHP_SELF']) == 'quanlycauhoi.php' ? 'class="active"' : ''; ?>>
            <i class="fas fa-question-circle"></i> <span>Câu hỏi</span>
        </a>
        <a href="#" <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'class="active"' : ''; ?>>
            <i class="fas fa-cog"></i> <span>Cài đặt</span>
        </a>
        <a href="logout.php">
            <i class="fas fa-sign-out-alt"></i> <span>Đăng xuất</span>
        </a>
    </div>
    
    <div class="main-content">
        <?php echo $content ?? ''; ?>
    </div>
    
    <footer>
        &copy; <?php echo date("Y"); ?> Hệ thống quản lý khảo sát | Thiết kế bởi <strong>Admin Team</strong>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Biểu đồ cho dashboard.php
        if (document.getElementById('userQuestionChart')) {
            const ctx = document.getElementById('userQuestionChart').getContext('2d');
            const userQuestionChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Số lượng câu hỏi', 'Số lượng người dùng', 'Số lượng kết quả khảo sát'],
                    datasets: [{
                        label: 'Thống Kê',
                        data: [<?php echo $totalQuestions ?? 0; ?>, <?php echo $totalUsers ?? 0; ?>, <?php echo $totalResults ?? 0; ?>],
                        backgroundColor: [
                            'rgba(78, 115, 223, 0.7)',
                            'rgba(28, 200, 138, 0.7)',
                            'rgba(54, 185, 204, 0.7)'
                        ],
                        borderColor: [
                            'rgb(78, 115, 223)',
                            'rgb(28, 200, 138)',
                            'rgb(54, 185, 204)'
                        ],
                        borderWidth: 2,
                        borderRadius: 5
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
                            display: false
                        }
                    },
                    animation: {
                        duration: 2000,
                        easing: 'easeOutQuart'
                    }
                }
            });
        }
        
        // Hiệu ứng cho thông báo
        $(document).ready(function() {
            // Tự động ẩn thông báo sau 5 giây
            setTimeout(function() {
                $('.success').slideUp(500);
            }, 5000);
            
            // Hiệu ứng hover cho các phần tử
            $('.card').hover(
                function() {
                    $(this).css('transform', 'translateY(-5px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );
        });
    </script>
</body>
</html>