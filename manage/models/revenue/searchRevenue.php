<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");

$data = file_get_contents("php://input");
$dataSearch = json_decode($data);

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    exit(0);
}

if ($dataSearch !== null) {
    require_once(__DIR__ . '/../../../config/database.php');
    $db = DB::connect();

    // Lấy giá trị từ dữ liệu JSON
    $searchValue = $db->real_escape_string($dataSearch->searchValue);
    $time_gte = $dataSearch->time_gte;
    $time_lte = $dataSearch->time_lte;

    // Khởi tạo điều kiện truy vấn SQL
    $sqlConditions = [];

    // Kiểm tra xem có dữ liệu trong searchValue không
    if (!empty($searchValue)) {
        // Thêm điều kiện tìm kiếm theo order_id hoặc table_number
        $sqlConditions[] = "(order_id LIKE '%$searchValue%' OR table_number LIKE '%$searchValue%')";
    }

    // Kiểm tra xem có dữ liệu trong time_gte và time_lte không
    if ($time_gte !== 0 && $time_lte !== 0) {
        $sqlConditions[] = "UNIX_TIMESTAMP(date_order) BETWEEN $time_gte / 1000 AND $time_lte / 1000";
    }

    // Thêm điều kiện tìm kiếm theo trường is_paid bằng 1
    $sqlConditions[] = "is_paid = 1";

    // Gộp tất cả điều kiện với AND
    $sqlConditionsString = implode(' AND ', $sqlConditions);

    // Tạo truy vấn SQL
    $sql = "SELECT * FROM orders";
    
    // Thêm điều kiện WHERE nếu có điều kiện tìm kiếm
    if (!empty($sqlConditions)) {
        $sql .= " WHERE $sqlConditionsString";
    }

    // Thực hiện truy vấn SQL
    $result = $db->query($sql);
 
    if ($result) {
        // Chuyển đổi kết quả thành mảng và trả về dưới dạng JSON
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        if (!empty($rows)) {
            echo json_encode($rows);
        } else {
            http_response_code(404); // Not Found
            echo json_encode(['error' => 'No data found for the specified time range and search value.']);
        }
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
