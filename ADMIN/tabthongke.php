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

// Lấy danh sách các loại câu hỏi (chủ đề)
$sql_topics = "SELECT MaLoaiCauHoi, ChuDe FROM loaicauhoi ORDER BY MaLoaiCauHoi";
$result_topics = sqlsrv_query($conn, $sql_topics);

if ($result_topics === false) {
    die("Query failed: " . print_r(sqlsrv_errors(), true));
}

$topics = [];
while ($row = sqlsrv_fetch_array($result_topics, SQLSRV_FETCH_ASSOC)) {
    $topics[$row['MaLoaiCauHoi']] = $row['ChuDe'];
}

// Lấy chủ đề được chọn (nếu có)
$selected_topic = isset($_GET['topic']) ? $_GET['topic'] : null;

// Lấy danh sách các câu hỏi theo chủ đề
$sql_questions = "SELECT IdCauHoi, NoiDungCauHoi FROM CauHoi";
if ($selected_topic) {
    $sql_questions .= " WHERE MaLoaiCauHoi = ?";
    $params = array($selected_topic);
} else {
    $params = array();
}
$sql_questions .= " ORDER BY IdCauHoi";

$result_questions = sqlsrv_query($conn, $sql_questions, $params);

if ($result_questions === false) {
    die("Query failed: " . print_r(sqlsrv_errors(), true));
}

$questions = [];
while ($row = sqlsrv_fetch_array($result_questions, SQLSRV_FETCH_ASSOC)) {
    $questions[$row['IdCauHoi']] = $row['NoiDungCauHoi'];
}

// Kiểm tra nếu không có câu hỏi nào
if (empty($questions)) {
    ob_start();
    ?>
    <div class="container py-5">
        <div class="alert alert-warning shadow-sm rounded">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fa-3x me-3 text-warning"></i>
                <div>
                    <h4 class="alert-heading mb-2">Không tìm thấy dữ liệu</h4>
                    <p class="mb-3">Không có câu hỏi nào cho chủ đề này hoặc chủ đề không tồn tại.</p>
                    <a href="tabthongke.php" class="btn btn-primary mt-2 px-4 py-2 rounded-pill">
                        <i class="fas fa-arrow-left me-2"></i>Quay lại
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php
    $content = ob_get_clean();
    $pageTitle = "Không tìm thấy dữ liệu";
    include 'layout.php';
    exit();
}

// Định nghĩa các phương án cố định
$fixed_options = [
    23 => ['text' => 'Hoàn toàn không đồng ý', 'muc_do' => 'Hoàn toàn không đồng ý', 'muc_y_nghia' => 'Hoàn toàn không đồng ý', 'value' => 1.00],
    24 => ['text' => 'Không đồng ý', 'muc_do' => 'Không đồng ý', 'muc_y_nghia' => 'Không đồng ý', 'value' => 2.00],
    25 => ['text' => 'Đồng ý một phần', 'muc_do' => 'Đồng ý một phần', 'muc_y_nghia' => 'Đồng ý một phần', 'value' => 3.00],
    26 => ['text' => 'Đồng ý', 'muc_do' => 'Đồng ý', 'muc_y_nghia' => 'Đồng ý', 'value' => 4.00],
    27 => ['text' => 'Hoàn toàn đồng ý', 'muc_do' => 'Hoàn toàn đồng ý', 'muc_y_nghia' => 'Hoàn toàn đồng ý', 'value' => 5.00]
];

// Lấy thống kê phản hồi cho từng câu hỏi
$statistics = [];
foreach ($questions as $questionId => $questionText) {
    // Lấy tổng số phản hồi cho câu hỏi này từ bảng KhaoSatSV
    $sql_total = "SELECT COUNT(*) AS TotalResponses 
                 FROM KhaoSatSV 
                 WHERE IdCauHoi = ?";
    $params = array($questionId);
    $result_total = sqlsrv_query($conn, $sql_total, $params);
    
    if ($result_total === false) {
        die("Query failed: " . print_r(sqlsrv_errors(), true));
    }
    
    $row_total = sqlsrv_fetch_array($result_total, SQLSRV_FETCH_ASSOC);
    $total_responses = $row_total['TotalResponses'];
    
    // Lấy số lượng phản hồi cho từng phương án cố định
    $options = [];
    foreach ($fixed_options as $optionId => $optionData) {
        $sql_count = "SELECT COUNT(*) AS ResponseCount 
                     FROM KhaoSatSV 
                     WHERE IdCauHoi = ? AND IdPhuongAn = ?";
        $params = array($questionId, $optionId);
        $result_count = sqlsrv_query($conn, $sql_count, $params);
        
        if ($result_count === false) {
            die("Query failed: " . print_r(sqlsrv_errors(), true));
        }
        
        $row_count = sqlsrv_fetch_array($result_count, SQLSRV_FETCH_ASSOC);
        $response_count = $row_count['ResponseCount'];
        
        $percentage = $total_responses > 0 ? round(($response_count / $total_responses) * 100, 2) : 0;
        
        $options[] = [
            'id' => $optionId,
            'text' => $optionData['text'],
            'count' => $response_count,
            'percentage' => $percentage,
            'muc_do' => $optionData['muc_do'],
            'muc_y_nghia' => $optionData['muc_y_nghia'],
            'value' => $optionData['value']
        ];
    }
    
    // Tính điểm trung bình
    $avg_score = 0;
    $total_weighted_responses = 0;
    foreach ($options as $option) {
        $total_weighted_responses += $option['count'] * $option['value'];
    }
    
    if ($total_responses > 0) {
        $avg_score = round($total_weighted_responses / $total_responses, 2);
    }
    
    $statistics[$questionId] = [
        'question' => $questionText,
        'total' => $total_responses,
        'options' => $options,
        'avg_score' => $avg_score
    ];
}

// Lấy tên chủ đề đã chọn (nếu có)
$selected_topic_name = $selected_topic && isset($topics[$selected_topic]) ? $topics[$selected_topic] : "Tất cả chủ đề";
$page_title = "Thống Kê Kết Quả Khảo Sát - " . $selected_topic_name;

// Chuẩn bị nội dung chính để đưa vào layout
ob_start();
?>
<header>
    <h1>Thống Kê Kết Quả Khảo Sát <span><h10> (<?php echo htmlspecialchars($selected_topic_name); ?>)</h10></span> </h1>
   

</header>
<div class="dashboard-container">


    <div class="dashboard-card">
        <!-- Form chọn chủ đề -->
        <div class="topic-selector-container">
            <form action="" method="GET" class="topic-form">
                <div class="form-group">
                    <label for="topic"><i class="fas fa-filter me-2"></i>Chọn chủ đề:</label>
                    
                    <div class="select-wrapper">
                        <select name="topic" id="topic" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Tất cả chủ đề --</option>
                            <?php foreach ($topics as $id => $name): ?>
                            <option value="<?php echo $id; ?>" <?php echo $selected_topic == $id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($name); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </form>
        </div>

        <div class="dashboard-summary">
            <div class="summary-card">
                <div class="summary-icon">
                    <i class="fas fa-question-circle"></i>
                </div>
                <div class="summary-data">
                    <span class="summary-value"><?php echo count($questions); ?></span>
                    <span class="summary-label">Câu hỏi</span>
                </div>
            </div>
            
            <div class="summary-card">
                <div class="summary-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="summary-data">
                    <span class="summary-value"><?php echo array_sum(array_column($statistics, 'total')); ?></span>
                    <span class="summary-label">Tổng phản hồi</span>
                </div>
            </div>
            
            <div class="summary-card">
                <div class="summary-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="summary-data">
                    <?php 
                    $total_avg = 0;
                    $count_questions = 0;
                    foreach ($statistics as $data) {
                        if ($data['total'] > 0) {
                            $total_avg += $data['avg_score'];
                            $count_questions++;
                        }
                    }
                    $overall_avg = $count_questions > 0 ? round($total_avg / $count_questions, 2) : 0;
                    ?>
                    <span class="summary-value"><?php echo $overall_avg; ?>/5</span>
                    <span class="summary-label">Điểm trung bình</span>
                </div>
            </div>
        </div>

        <h3 class="section-title"><i class="fas fa-poll me-2"></i>Kết Quả Phản Hồi Chi Tiết</h3>
        
        <div class="results-container">
            <?php foreach ($statistics as $questionId => $data): ?>
            <div class="question-card">
                <div class="question-header">
                    <h4 class="question-title">
                        <span class="question-number"><?php echo $questionId; ?>.</span> 
                        <?php echo htmlspecialchars($data['question']); ?>
                    </h4>
                    <div class="question-meta">
                        <div class="meta-item">
                            <i class="fas fa-users me-2"></i>
                            <span><?php echo $data['total']; ?> phản hồi</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-star me-2"></i>
                            <span>Điểm TB: <strong><?php echo $data['avg_score']; ?>/5</strong></span>
                        </div>
                    </div>
                </div>
                
                <div class="options-container">
                    <?php 
                    // Định nghĩa màu sắc cho các mức độ đánh giá
                    $colors = [
                        1 => '#dc3545', // Đỏ - Hoàn toàn không đồng ý
                        2 => '#fd7e14', // Cam - Không đồng ý
                        3 => '#ffc107', // Vàng - Đồng ý một phần
                        4 => '#20c997', // Xanh lá nhạt - Đồng ý
                        5 => '#198754'  // Xanh lá đậm - Hoàn toàn đồng ý
                    ];
                    
                    foreach ($data['options'] as $option): 
                    $color = $colors[$option['value']];
                    ?>
                    <div class="option-stat">
                        <div class="option-header">
                            <div class="option-label">
                                <span class="option-dot" style="background-color: <?php echo $color; ?>"></span>
                                <span class="option-text"><?php echo htmlspecialchars($option['text']); ?></span>
                                <span class="option-value">(<?php echo $option['value']; ?> điểm)</span>
                            </div>
                            <div class="option-count">
                                <strong><?php echo $option['count']; ?></strong> phản hồi
                                <span class="option-percentage">(<?php echo $option['percentage']; ?>%)</span>
                            </div>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" style="width: <?php echo $option['percentage']; ?>%; background-color: <?php echo $color; ?>">
                                <?php if ($option['percentage'] > 5): ?>
                                <?php echo $option['percentage']; ?>%
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Include layout với nội dung
include 'layout.php';

// Giải phóng tài nguyên
sqlsrv_free_stmt($result_questions);
if (isset($result_topics)) sqlsrv_free_stmt($result_topics);
sqlsrv_close($conn);
?>

<style>
/* Thiết lập chung */
:root {
    --primary-color: #4361ee;
    --secondary-color: #3f37c9;
    --success-color: #4caf50;
    --info-color: #2196f3;
    --warning-color: #ff9800;
    --danger-color: #f44336;
    --light-color: #f8f9fa;
    --dark-color: #343a40;
    --gray-100: #f8f9fa;
    --gray-200: #e9ecef;
    --gray-300: #dee2e6;
    --gray-400: #ced4da;
    --gray-500: #adb5bd;
    --gray-600: #6c757d;
    --gray-700: #495057;
    --gray-800: #343a40;
    --gray-900: #212529;
    --border-radius: 0.5rem;
    --box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    --transition: all 0.3s ease;
}

.dashboard-container {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 15px;
}

/* Header */
.dashboard-header {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-radius: var(--border-radius);
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: var(--box-shadow);
}

.header-content {
    display: flex;
    align-items: center;
}

.header-icon {
    font-size: 2.5rem;
    margin-right: 1.5rem;
    background-color: rgba(255, 255, 255, 0.2);
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.header-text h1 {
    margin: 0;
    font-size: 2rem;
    font-weight: 600;
}

.subtitle {
    margin: 0.5rem 0 0;
    font-size: 1.1rem;
    opacity: 0.9;
}

/* Card chính */
.dashboard-card {
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 2rem;
    margin-bottom: 2rem;
}

/* Bộ chọn chủ đề */
.topic-selector-container {
    margin-bottom: 2rem;
    background-color: var(--gray-100);
    border-radius: var(--border-radius);
    padding: 1.5rem;
}

.topic-form {
    display: flex;
    align-items: center;
    justify-content: center;
}

.form-group {
    display: flex;
    align-items: center;
    width: 100%;
}

.form-group label {
    font-weight: 600;
    margin-right: 1rem;
    color: var(--gray-700);
    white-space: nowrap;
}

.select-wrapper {
    position: relative;
    flex-grow: 1;
}

.form-select {
    width: 100%;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    border: 1px solid var(--gray-300);
    border-radius: var(--border-radius);
    background-color: white;
    appearance: none;
    cursor: pointer;
    transition: var(--transition);
}

.form-select:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
}

.select-wrapper::after {
    content: '\f078';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    color: var(--gray-600);
}

/* Thẻ tóm tắt */
.dashboard-summary {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.summary-card {
    flex: 1;
    min-width: 200px;
    background-color: white;
    border-radius: var(--border-radius);
    padding: 1.5rem;
    display: flex;
    align-items: center;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border-left: 4px solid var(--primary-color);
    transition: var(--transition);
}

.summary-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.summary-icon {
    font-size: 2rem;
    color: var(--primary-color);
    margin-right: 1rem;
    background-color: rgba(67, 97, 238, 0.1);
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.summary-data {
    display: flex;
    flex-direction: column;
}

.summary-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--gray-800);
    line-height: 1.2;
}

.summary-label {
    font-size: 0.9rem;
    color: var(--gray-600);
    margin-top: 0.25rem;
}

/* Tiêu đề phần */
.section-title {
    font-size: 1.5rem;
    color: var(--gray-800);
    margin: 2rem 0 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid var(--gray-200);
}

/* Container kết quả */
.results-container {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

/* Thẻ câu hỏi */
.question-card {
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    padding: 1.5rem;
    border: 1px solid var(--gray-200);
    transition: var(--transition);
}

.question-card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    border-color: var(--gray-300);
}

.question-header {
    margin-bottom: 1.5rem;
}

.question-title {
    font-size: 1.25rem;
    color: var(--gray-800);
    margin: 0 0 1rem;
    line-height: 1.4;
}

.question-number {
    color: var(--primary-color);
    font-weight: 700;
}

.question-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    margin-top: 0.75rem;
}

.meta-item {
    display: flex;
    align-items: center;
    color: var(--gray-700);
    font-size: 0.95rem;
}

.meta-item i {
    color: var(--primary-color);
}

/* Phương án */
.options-container {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.option-stat {
    margin-bottom: 1rem;
}

.option-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.option-label {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.option-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
}

.option-text {
    font-weight: 500;
    color: var(--gray-800);
}

.option-value {
    color: var(--gray-600);
    font-size: 0.9rem;
}

.option-count {
    font-size: 0.9rem;
    color: var(--gray-700);
}

.option-percentage {
    font-weight: 600;
    color: var(--gray-800);
}

/* Thanh tiến trình */
.progress {
    height: 25px;
    background-color: var(--gray-200);
    border-radius: 50px;
    overflow: hidden;
    box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
}

.progress-bar {
    height: 100%;
    color: white;
    text-align: center;
    line-height: 25px;
    font-weight: 600;
    transition: width 1s ease;
    border-radius: 50px;
}

/* Responsive */
@media (max-width: 768px) {
    .header-content {
        flex-direction: column;
        text-align: center;
    }
    
    .header-icon {
        margin-right: 0;
        margin-bottom: 1rem;
    }
    
    .form-group {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .form-group label {
        margin-bottom: 0.5rem;
    }
    
    .question-meta {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .option-header {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>