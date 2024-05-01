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
    $searchValue = $dataSearch->valueSearch; // Sửa từ $dataSearch->data thành $dataSearch->valueSearch

    $sql = "SELECT * FROM users 
            WHERE (user_id LIKE '%$searchValue%' OR name LIKE '%$searchValue%'  OR email LIKE '%$searchValue%' OR position LIKE '%$searchValue%')";

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
