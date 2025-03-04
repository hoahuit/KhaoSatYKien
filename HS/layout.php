<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION["user_id"])) {
    header("Location: ./HS/index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Hệ thống Khảo sát'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #e9ecef;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: #495057;
        }
        .navbar {
            box-shadow: 0 2px 10px rgba(0,0,0,.2);
            background-color: #007bff;
            position: sticky; /* Ensure the navbar stays at the top */
            top: 0; /* Stick to the top */
            z-index: 1000; /* Ensure it is above other content */
        }
        .navbar-brand {
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .navbar-text {
            font-size: 1.2rem;
        }
        .btn-outline-light {
            transition: all 0.3s ease;
        }
        .btn-outline-light:hover {
            transform: translateY(-2px);
            background-color: rgba(255, 255, 255, 0.3);
            color: #007bff;
        }
        .container {
            max-width: 1200px;
        }
        .card {
            border-radius: 15px;
            transition: all 0.3s ease;
            border: 1px solid #dee2e6;
            background-color: #ffffff;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0,0,0,.2);
        }
        .content-wrapper {
            flex: 1 0 auto;
            display: flex;
            background-color: #ffffff;
            border-radius: 10px;
            margin: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,.1);
            min-height: calc(100vh - 56px - 40px);
        }
        footer {
            flex-shrink: 0;
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
            padding: 20px 0;
        }
        .sidebar {
            width: 280px;
            background: #ffffff;
            border-right: 1px solid #dee2e6;
            height: calc(100vh - 56px);
            position: sticky;
            top: 56px;
            padding: 1rem;
            overflow-y: auto;
        }
        .main-content {
            flex: 1;
            padding: 2rem;
        }
        .list-group-item {
            border: none;
            padding: 15px 20px;
            margin-bottom: 5px;
            border-radius: 10px;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
        }
        .list-group-item:hover {
            background-color: #e2e6ea;
            transform: translateX(5px);
        }
        .list-group-item.active {
            background-color: #007bff;
            color: white;
            box-shadow: 0 2px 8px rgba(13, 110, 253, 0.25);
        }
        .list-group-item i {
            width: 24px;
            text-align: center;
            font-size: 1.5rem;
        }
        @media (max-width: 991.98px) {
            .sidebar {
                display: none;
            }
            .offcanvas {
                width: 280px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <button class="navbar-toggler me-2 d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="navbar-brand" href="#">
                <i class="fas fa-chart-bar me-2"></i>Hệ thống Khảo sát
            </a>
            <div class="d-flex align-items-center">
                <div class="navbar-text text-white me-4">
                    <i class="fas fa-user-circle me-2"></i>
                    Xin chào, <?php echo htmlspecialchars($_SESSION["username"]); ?>!
                </div>
                <a href="logout.php" class="btn btn-outline-light px-4">
                    <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                </a>
            </div>
        </div>
    </nav>

    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="sidebar">
        <div class="offcanvas-header bg-primary text-white">
            <h5 class="offcanvas-title">Menu Chính</h5>
            <button type="button" class="btn-close text-reset btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <div class="list-group list-group-flush">
                <a href="HS/dashboard.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home me-2"></i>Trang chủ
                </a>
                <a href="HS/surveys.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'surveys.php' ? 'active' : ''; ?>">
                    <i class="fas fa-poll me-2"></i>Khảo sát
                </a>
                <a href="HS/history.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'history.php' ? 'active' : ''; ?>">
                    <i class="fas fa-history me-2"></i>Lịch sử
                </a>
       
            </div>
        </div>
    </div>

    <div class="content-wrapper">
        <div class="sidebar d-none d-lg-block">
            <div class="list-group list-group-flush">
                <a href="dashboard.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home me-2"></i>Trang chủ
                </a>
                <a href="surveys.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'surveys.php' ? 'active' : ''; ?>">
                    <i class="fas fa-poll me-2"></i>Khảo sát
                </a>
                <a href="history.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'history.php' ? 'active' : ''; ?>">
                    <i class="fas fa-history me-2"></i>Lịch sử
                </a>
                
            </div>
        </div>
        <div class="main-content">
            <?php echo $content ?? ''; ?>
        </div>
    </div>

    <footer class="bg-white py-4 border-top">
        <div class="container text-center text-muted">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Hệ thống Khảo sát. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
</body>
</html> 
</html> 