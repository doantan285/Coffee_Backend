<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");
header("Content-Type: application/json");

// Kiểm tra xem phương thức của yêu cầu có phải là OPTIONS hay không
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // Trả về các tiêu đề CORS cho phương thức OPTIONS
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    exit(0);
}

// Lấy dữ liệu từ yêu cầu
$data = file_get_contents("php://input");
$dataComment = json_decode($data, true);

// Kiểm tra xem dữ liệu có đầy đủ không
if (isset($dataComment['content'], $dataComment['product_id'], $dataComment['timestamp'])) {
    // Kết nối đến cơ sở dữ liệu
    require_once(__DIR__ . '/../../../config/database.php');
    $db = DB::connect();

    // Escape và chuẩn bị dữ liệu với prepared statements
    $content = $db->real_escape_string($dataComment['content']);
    $id = $db->real_escape_string($dataComment['product_id']);
    $time = $dataComment['timestamp'];

    // Thực hiện truy vấn SQL để thêm dữ liệu vào bảng comments
    $query = "INSERT INTO comments (product_id, content, timestamp) VALUES (?, ?, ?)";
    $stmt = $db->prepare($query);

    if ($stmt) {
        $stmt->bind_param("iss", $id, $content, $time);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Execute failed: " . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to prepare statement: " . $db->error]);
    }

    // Đóng kết nối
    $db->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid data"]);
}
?>
