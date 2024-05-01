<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");

$data = file_get_contents("php://input");
$dataDeleteOrders = json_decode($data);

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    exit(0);
}

if ($dataDeleteOrders !== null) {
    require_once(__DIR__ . '/../../../config/database.php');
    $db = DB::connect();

    $ids = $dataDeleteOrders->ids;

    // Chuyển mảng $ids thành chuỗi để sử dụng trong truy vấn SQL
    $idsString = implode(",", $ids);

    // Truy vấn SQL DELETE
    $sql = "DELETE FROM orders WHERE order_id IN ($idsString)";

    if ($db->query($sql)) {
        echo json_encode(['success' => 'Delete successful']);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'Delete failed']);
    }

    $db->close();
} else {
    // Handle lỗi khi không thể giải mã JSON
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid JSON data']);
}
?>
