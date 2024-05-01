<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");

$data = file_get_contents("php://input");
$dataDelete = json_decode($data, true);

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    exit(0);
}

// Kiểm tra xem dữ liệu đã được nhận đúng chưa
if (isset($dataDelete['id']) && is_numeric($dataDelete['id'])) {
    
    // Kết nối đến cơ sở dữ liệu (chưa điều chỉnh theo loại cơ sở dữ liệu bạn đang sử dụng)
    require_once(__DIR__ . '/../../../config/database.php');
    $db = DB::connect();

    // Escape và chuẩn bị dữ liệu
    $id = $db->real_escape_string($dataDelete['id']);

    // Sử dụng prepared statement để tránh SQL injection
    $stmt = $db->prepare("DELETE FROM comments WHERE comment_id = ?");
    $stmt->bind_param("i", $id);

    // Thực hiện câu lệnh SQL
    $resultDelete = $stmt->execute();

    if ($resultDelete) {
        // Trả về dữ liệu mới của bảng products dựa trên category
        // (Nếu cần)

    } else {
        echo "Lỗi xóa dữ liệu: " . $db->error;
    }

    // Đóng kết nối
    $stmt->close();
    $db->close();

} else {
    echo "Dữ liệu không hợp lệ từ frontend.";
}
?>
