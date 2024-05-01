<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");

$data = file_get_contents("php://input");
$dataBookMark = json_decode($data, true); // Chú ý thêm tham số true để chuyển đổi thành mảng assosiative
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    exit(0);
}

// Kiểm tra xem dữ liệu đã được nhận đúng chưa
if (isset($dataBookMark['id']) && isset($dataBookMark['category']) && isset($dataBookMark['mark'])) {
    
    // Kết nối đến cơ sở dữ liệu (chưa điều chỉnh theo loại cơ sở dữ liệu bạn đang sử dụng)
    require_once(__DIR__ . '/../../../config/database.php');
    $db = DB::connect();

    // Escape và chuẩn bị dữ liệu
    $id = $db->real_escape_string($dataBookMark['id']);
    $category = $db->real_escape_string($dataBookMark['category']);
    $mark = $dataBookMark['mark'] ? 1 : 0;

    // Cập nhật bảng products
    $sqlUpdate = "UPDATE products SET InStock = '$mark' WHERE product_id = '$id' AND category = '$category'";
    $result = $db->query($sqlUpdate); // Sử dụng biến $db thay vì $conn

    if ($result) {
        // Truy vấn thành công, trả về dữ liệu mới của bảng products
        $sqlSelect = "SELECT * FROM products WHERE category = '$category'";
        $resultSelect = $db->query($sqlSelect);

        if ($resultSelect) {
            $productsData = [];
            while ($row = $resultSelect->fetch_assoc()) {
                $productsData[] = $row;
            }

            // Trả về dữ liệu cho frontend
            echo json_encode($productsData);
        } else {
            echo "Lỗi truy vấn: " . $db->error;
        }
    } else {
        echo "Lỗi cập nhật: " . $db->error;
    }

    // Đóng kết nối
    $db->close();

} else {
    echo "Dữ liệu không hợp lệ từ frontend.";
}
?>
