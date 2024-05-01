<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");

$data = file_get_contents("php://input");
$dataProduct = json_decode($data, true);

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    exit(0);
}

// Kiểm tra xem dữ liệu đã được nhận đúng chưa
if (!empty($dataProduct['name']) && !empty($dataProduct['price']) && !empty($dataProduct['category'])) {
    
    // Kết nối đến cơ sở dữ liệu (chưa điều chỉnh theo loại cơ sở dữ liệu bạn đang sử dụng)
    require_once(__DIR__ . '/../../../config/database.php');
    $db = DB::connect();

    // Escape và chuẩn bị dữ liệu
    $image = $db->real_escape_string($dataProduct['image_url']);
    $category = $db->real_escape_string($dataProduct['category']);
    $name = $db->real_escape_string($dataProduct['name']);
    $price = $db->real_escape_string($dataProduct['price']);
    $description = $db->real_escape_string($dataProduct['description']);

    // Mặc định InStock là 1
    $inStock = 1;

    // Sử dụng prepared statement để tránh SQL injection và loại bỏ trường ID từ câu lệnh SQL
    $stmt = $db->prepare("INSERT INTO products (image_url, category, name, price, description, InStock) 
                          VALUES (?, ?, ?, ?, ?, ?)");

    // Binds parameters và gán giá trị
    $stmt->bind_param("sssssi", $image, $category, $name, $price, $description, $inStock);

    // Thực hiện truy vấn
    $resultProduct = $stmt->execute();

    if ($resultProduct) {
        echo json_encode(array("message" => "success"));
    } else {
        echo json_encode(array("message" => "error", "error_details" => $stmt->error));
    }

    // Đóng kết nối
    $stmt->close();
    $db->close();

} else {
    echo json_encode(array("message" => "Invalid data from frontend."));
}
?>
