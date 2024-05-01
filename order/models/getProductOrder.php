<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");

$data = file_get_contents("php://input");
$dataOrder = json_decode($data, true);

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    exit(0);
}

// Kiểm tra xem dữ liệu đã được nhận đúng chưa
if (isset($dataOrder['id'])) {
    
    // Kết nối đến cơ sở dữ liệu (chưa điều chỉnh theo loại cơ sở dữ liệu bạn đang sử dụng)
    require_once(__DIR__ . '/../../config/database.php');
    $db = DB::connect();

    // Escape và chuẩn bị dữ liệu
    $id = $db->real_escape_string($dataOrder['id']);

    // Truy vấn cơ sở dữ liệu để lấy thông tin về sản phẩm
    $sqlProduct = "SELECT * FROM products WHERE product_id = '$id'";
    $resultProduct = $db->query($sqlProduct);

    if ($resultProduct) {
        // Chuyển đổi kết quả thành mảng kết hợp
        $product = $resultProduct->fetch_assoc();

        // Truy vấn cơ sở dữ liệu để lấy các comment của sản phẩm dựa trên product_id
        $sqlComments = "SELECT * FROM comments WHERE product_id = '$id'";
        $resultComments = $db->query($sqlComments);
        
        if ($resultComments) {
            // Chuyển đổi kết quả thành một mảng các comment
            $comments = [];
            while ($row = $resultComments->fetch_assoc()) {
                $comments[] = $row;
            }
            // Thêm mảng các comment vào mảng sản phẩm
            $product['comments'] = $comments;
        } else {
            echo "Lỗi trong quá trình truy vấn cơ sở dữ liệu comments.";
        }

        // Trả về kết quả cho frontend
        header('Content-Type: application/json');
        echo json_encode($product);
    } else {
        echo "Lỗi trong quá trình truy vấn cơ sở dữ liệu products.";
    }

    // Đóng kết nối
    $db->close();

} else {
    echo "Dữ liệu không hợp lệ từ frontend.";
}
?>
