<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");

$data = file_get_contents("php://input");
$dataDetailTable = json_decode($data);

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    exit(0);
}

if ($dataDetailTable !== null) {
    require_once(__DIR__ . '/../../../config/database.php');
    $db = DB::connect();

    $id = $dataDetailTable->id;

    // Khởi tạo mảng $ids trước khi sử dụng
    $ids = array($id);

    // Sử dụng prepared statement để tránh tấn công SQL injection
    $stmt = $db->prepare("SELECT * FROM orders WHERE table_number IN (" . implode(',', $ids) . ") AND is_paid = 0");
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Chuyển đổi kết quả thành mảng kết hợp
    $orders = array();
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    // Trả về kết quả dưới dạng JSON
    echo json_encode($orders);
}
?>
