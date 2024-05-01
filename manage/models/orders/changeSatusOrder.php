<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");

$data = file_get_contents("php://input");
$dataChangeStatusOrders = json_decode($data);

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    exit(0);
}

if ($dataChangeStatusOrders !== null) {
    require_once(__DIR__ . '/../../../config/database.php');
    $db = DB::connect();

    $ids = $dataChangeStatusOrders->ids;
    $isPaid = $dataChangeStatusOrders->isPaid;

    $idsString = implode(",", $ids);
    switch ($isPaid) {
        case 0:
        case 1:
        case 2:
            $isPaidValue = $isPaid;
            break;
        default:
            $isPaidValue = 0; // Giá trị mặc định khi không phù hợp
            break;
    }

    // Truy vấn SQL UPDATE
    $sql = "UPDATE orders SET is_paid = '$isPaidValue' WHERE order_id IN ($idsString)";

    if ($db->query($sql)) {
        echo json_encode(['success' => 'Update successful']);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'Update failed']);
    }

    $db->close();
} else {
    // Handle lỗi khi không thể giải mã JSON
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid JSON data']);
}

?>
