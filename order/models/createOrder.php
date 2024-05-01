<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");

// Lấy dữ liệu từ frontend
$data = file_get_contents("php://input");
$dataOrder = json_decode($data, true);

// Kiểm tra xem phương thức là OPTIONS (cho CORS) và kết thúc kịch bản nếu là OPTIONS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    exit(0);
}

// Kiểm tra tính hợp lệ của dữ liệu đầu vào
if (!empty($data) && $dataOrder !== null && isset($dataOrder['product_name']) && isset($dataOrder['customer_name']) && isset($dataOrder['note']) && isset($dataOrder['table_number']) && isset($dataOrder['total_amount']) && isset($dataOrder['is_paid'])) {

    // Kết nối đến cơ sở dữ liệu (chưa điều chỉnh theo loại cơ sở dữ liệu bạn đang sử dụng)
    require_once(__DIR__ . '/../../config/database.php');
    $db = DB::connect();

    // Chuẩn bị truy vấn sử dụng tham số hóa
    $insert_query = $db->prepare("INSERT INTO orders (product_name, customer_name, note, table_number, total_amount, is_paid, date_order) 
                                  VALUES (?, ?, ?, ?, ?, ?, NOW())");

    // Bind parameters và thực thi truy vấn
    $insert_query->bind_param("sssiid", $dataOrder['product_name'], $dataOrder['customer_name'], $dataOrder['note'], $dataOrder['table_number'], $dataOrder['total_amount'], $dataOrder['is_paid']);
    if ($insert_query->execute()) {
        // Xóa các bản ghi trước đó có cùng table_number và is_paid khác 1, chỉ giữ lại bản ghi mới nhất
        $delete_query = $db->prepare("DELETE FROM orders 
                                      WHERE table_number = ? AND is_paid <> 1 AND date_order < (SELECT MAX(date_order) FROM orders WHERE table_number = ? AND is_paid <> 1)");
        $delete_query->bind_param("ii", $dataOrder['table_number'], $dataOrder['table_number']);
        $delete_query->execute();
        $delete_query->close();

        http_response_code(200);
        echo json_encode(array("message" => "Thành công."));
    } else {
        http_response_code(404);
        error_log("Lỗi khi chèn dữ liệu vào cơ sở dữ liệu: " . $db->error);
        echo json_encode(array("message" => "Thất bại."));
    }
    // Đóng kết nối và statement
    $insert_query->close();
    $db->close();

} else {
    // Ghi log lỗi vào file
    error_log("Dữ liệu không hợp lệ từ phía frontend: " . $data);
    echo "Dữ liệu không hợp lệ từ phía frontend.";
}
?>
