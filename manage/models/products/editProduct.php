<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");

$data = file_get_contents("php://input");
$dataEditProduct = json_decode($data, true);

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    exit(0);
}

// Kiểm tra xem dữ liệu đã được nhận đúng chưa
if (isset($dataEditProduct['id']) && isset($dataEditProduct['description']) && isset($dataEditProduct['image_url']) && isset($dataEditProduct['name']) && isset($dataEditProduct['price'])){
    
    // Kết nối đến cơ sở dữ liệu (chưa điều chỉnh theo loại cơ sở dữ liệu bạn đang sử dụng)
    require_once(__DIR__ . '/../../../config/database.php');
    $db = DB::connect();

    // Sử dụng Prepared Statements để bảo vệ khỏi SQL injection
    $stmt = $db->prepare("UPDATE products SET image_url=?, name=?, price=?, description=? WHERE product_id=?");
    $stmt->bind_param("ssdsi", $image_url, $name, $price, $description, $id);

    // Escape và chuẩn bị dữ liệu
    $id = $dataEditProduct['id'];
    $description = $dataEditProduct['description'];
    $image_url = $dataEditProduct['image_url'];
    $name = $dataEditProduct['name'];
    $price = $dataEditProduct['price'];

    if ($stmt->execute()) {
        // Trả về response dưới dạng JSON
        echo json_encode(array("message" => "Thành công"));
    } else {
        // Trả về response dưới dạng JSON
        echo json_encode(array("message" => "Lỗi cập nhật: " . $stmt->error));
    }

    // Đóng kết nối
    $stmt->close();
    $db->close();

} else {
    // Trả về response dưới dạng JSON
    echo json_encode(array("message" => "Dữ liệu không hợp lệ từ frontend."));
}
?>
