<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");

$data = file_get_contents("php://input");
$dataDelete = json_decode($data, true);

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    exit(0);
}

// Kiểm tra xem dữ liệu đã được nhận đúng chưa
if (isset($dataDelete['id']) && isset($dataDelete['category'])) {
    
    // Kết nối đến cơ sở dữ liệu (chưa điều chỉnh theo loại cơ sở dữ liệu bạn đang sử dụng)
    require_once(__DIR__ . '/../../../config/database.php');
    $db = DB::connect();

    // Escape và chuẩn bị dữ liệu
    $id = $db->real_escape_string($dataDelete['id']);
    $category = $db->real_escape_string($dataDelete['category']);

    // Xóa dữ liệu từ bảng products dựa trên id
    $sqlDelete = "DELETE FROM products WHERE product_id = '$id'";
    $resultDelete = $db->query($sqlDelete);

    if ($resultDelete) {
        // Trả về dữ liệu mới của bảng products dựa trên category
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
        echo "Lỗi xóa dữ liệu: " . $db->error;
    }

    // Đóng kết nối
    $db->close();

} else {
    echo "Dữ liệu không hợp lệ từ frontend.";
}
?>
