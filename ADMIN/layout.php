<!-- layout.php -->
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Admin Panel'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #eef2f7;
            color: #333;
            line-height: 1.6;
        }
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, #007bff, #0056b3);
            color: white;
            position: fixed;
            height: 100vh;
            padding: 30px 20px;
            box-shadow: 3px 0 15px rgba(0, 0, 0, 0.1);
        }
        .sidebar h2 {
            text-align: center;
            font-size: 24px;
            margin-bottom: 40px;
            letter-spacing: 1px;
        }
        .sidebar ul {
            list-style: none;
        }
        .sidebar ul li {
            margin: 20px 0;
        }
        .sidebar ul li a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            display: flex;
            align-items: center;
            padding: 12px 15px;
            border-radius: 8px;
            transition: background 0.3s ease, padding-left 0.3s ease;
        }
        .sidebar ul li a i {
            margin-right: 12px;
            font-size: 20px;
        }
        .sidebar ul li a:hover {
            background-color: rgba(255, 255, 255, 0.2);
            padding-left: 20px;
        }
        .main-content {
            margin-left: 250px;
            padding: 30px;
            min-height: 100vh;
            background-color: #f7fafc;
        }
        header {
            background: linear-gradient(90deg, #007bff, #00c4ff);
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        header h1 {
            font-size: 28px;
            font-weight: 600;
        }
        footer {
            text-align: center;
            margin-top: 40px;
            color: #777;
            font-size: 14px;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 80px;
            }
            .sidebar h2, .sidebar ul li a span {
                display: none;
            }
            .sidebar ul li a {
                justify-content: center;
                padding: 10px;
            }
            .main-content {
                margin-left: 80px;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="dashboardadmin.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
            <li><a href="quanlynguoidung.php"><i class="fas fa-users"></i> <span>Người dùng</span></a></li>
            <li><a href="settings.php"><i class="fas fa-cog"></i> <span>Settings</span></a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
        </ul>
    </div>
    <div class="main-content">
        <!-- Nội dung chính của trang sẽ được chèn vào đây -->