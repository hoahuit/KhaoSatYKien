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
            --primary-dark: #2e59d9;
            --secondary-color: #1cc88a;
            --dark-color: #1a1c23;
            --light-color: #f8f9fc;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
            --info-color: #36b9cc;
            --gray-100: #f8f9fc;
            --gray-200: #eaecf4;
            --gray-300: #dddfeb;
            --gray-800: #2d3748;
            --shadow-sm: 0 2px 4px rgba(0,0,0,.05);
            --shadow-md: 0 4px 6px rgba(0,0,0,.1);
            --shadow-lg: 0 10px 15px rgba(0,0,0,.1);
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
            background: var(--dark-color);
            color: #fff;
            position: fixed;
            height: 100vh;
            padding: 20px 0;
            border-right: 1px solid var(--gray-800);
            box-shadow: var(--shadow-lg);
            transition: all 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
        }
        
        .sidebar h2 {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            margin: -20px 0 20px;
            padding: 20px;
            font-size: 24px;
            text-align: center;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 2px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar a {
            margin: 2px 10px;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 500;
            color: var(--gray-300);
            border-left: 3px solid transparent;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .sidebar a i {
            margin-right: 15px;
            font-size: 18px;
            width: 25px;
            text-align: center;
        }
        
        .sidebar a:hover, .sidebar a.active {
            background: rgba(255,255,255,.05);
            border-left-color: var(--primary-color);
            transform: none;
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
            border-radius: 12px;
            border: 1px solid var(--gray-200);
            box-shadow: var(--shadow-sm);
            margin-bottom: 30px;
            transition: all .2s ease;
        }
        
        .card:hover {
            box-shadow: var(--shadow-md);
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
            border: 1px solid var(--gray-200);
        }
        
        th, td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: var(--gray-800);
            color: #fff;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 13px;
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
        
        /* Thêm hiệu ứng loading */
        .loading-bar {
            height: 3px;
            width: 100%;
            position: fixed;
            top: 0;
            left: 0;
            background: linear-gradient(to right, var(--primary-color), var(--info-color));
            z-index: 9999;
            animation: loading 1s ease-in-out infinite;
        }
        
        @keyframes loading {
            0% { width: 0; }
            50% { width: 65%; }
            100% { width: 100%; }
        }
        
        /* Nút action mới */
        .btn-action {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            transition: all .2s;
        }
        
        .btn-edit {
            background: rgba(78, 115, 223, 0.1);
            color: var(--primary-color);
        }
        
        .btn-delete {
            background: rgba(231, 74, 59, 0.1);
            color: var(--danger-color);
        }
        
        /* Thêm hiệu ứng skeleton loading */
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: skeleton-loading 1.5s infinite;
        }
        
        @keyframes skeleton-loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
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
        <a href="tabthongke.php" <?php echo basename($_SERVER['PHP_SELF']) == 'tabthongke.php' ? 'class="active"' : ''; ?>>
            <i class="fas fa-cog"></i> <span>Thống kê</span>
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
        // Thêm loading bar
        const loadingBar = document.createElement('div');
        loadingBar.className = 'loading-bar';
        document.body.prepend(loadingBar);

        // Hiệu ứng loading khi chuyển trang
        document.addEventListener('click', function(e) {
            if (e.target.tagName === 'A') {
                loadingBar.style.display = 'block';
            }
        });

        // Cải thiện biểu đồ
        if (document.getElementById('userQuestionChart')) {
            Chart.defaults.font.family = "'Roboto', sans-serif";
            Chart.defaults.font.size = 13;
            
            const ctx = document.getElementById('userQuestionChart').getContext('2d');
            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(78,115,223,0.6)');
            gradient.addColorStop(1, 'rgba(78,115,223,0.1)');
            
            const userQuestionChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
                    datasets: [{
                        label: 'Thống Kê',
                        data: [65, 78, 66, 44, 56, 67, 75, 70, 90, 85, 80, 95],
                        fill: true,
                        backgroundColor: gradient,
                        borderColor: 'rgb(78,115,223)',
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#fff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#fff',
                            titleColor: '#2d3748',
                            bodyColor: '#2d3748',
                            borderColor: '#e2e8f0',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return `Số lượng: ${context.parsed.y}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0,0,0,0.05)',
                                drawBorder: false
                            },
                            ticks: {
                                padding: 10
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                padding: 10
                            }
                        }
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