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
    $searchValue = $dataSearch->value;
    $category = $dataSearch->category;

    // Khởi tạo điều kiện truy vấn SQL
    $sqlConditions = [];

    // Kiểm tra xem có dữ liệu trong searchValue không
    if (!empty($searchValue)) {
        // Thêm điều kiện tìm kiếm theo name hoặc product_id
        $sqlConditions[] = "(name LIKE '%$searchValue%' OR product_id LIKE '%$searchValue%')";
    }

    // Kiểm tra xem có dữ liệu trong category không
    if (!empty($category)) {
        $sqlConditions[] = "category = '$category'";
    }

    // Thêm điều kiện chỉ lấy những sản phẩm có InStock = 1
    $sqlConditions[] = "InStock = 1";

    // Gộp tất cả điều kiện với AND
    $sqlConditionsString = implode(' AND ', $sqlConditions);

    // Tạo truy vấn SQL
    $sql = "SELECT * FROM products";

    // Thêm điều kiện WHERE nếu có điều kiện tìm kiếm
    if (!empty($sqlConditions)) {
        $sql .= " WHERE $sqlConditionsString";
    }

    // Thực hiện truy vấn SQL
    $result = $db->query($sql);

    if ($result) {
        // Chuyển đổi kết quả thành mảng và trả về dưới dạng JSON
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($rows);
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
