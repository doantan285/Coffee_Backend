<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");

$data = file_get_contents("php://input");
$idDetailOrder = json_decode($data);

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    exit(0);
}

if ($idDetailOrder !== null) {
    require_once(__DIR__ . '/../../../config/database.php');
    $db = DB::connect();

    $id = $idDetailOrder->id;

    $sql = "SELECT * FROM orders WHERE order_id = '$id'";

    $result = $db->query($sql);

    if ($result) {
        // Chuyển đổi kết quả thành mảng và trả về dưới dạng JSON
        $row = $result->fetch_assoc();
        echo json_encode($row);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'Query failed: ' . $db->error]);
    }

    $db->close();
} else {
    // Handle lỗi khi không thể giải mã JSON
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid JSON data']);
}
?>
