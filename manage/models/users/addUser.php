<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");

$data = file_get_contents("php://input");
$dataUser = json_decode($data, true);

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    exit(0);
}

// Kiểm tra xem dữ liệu đã được nhận đúng chưa
if (!empty($dataUser['name']) && !empty($dataUser['date']) && !empty($dataUser['email']) && !empty($dataUser['phone']) && !empty($dataUser['role'])) {
    
    // Kết nối đến cơ sở dữ liệu (chưa điều chỉnh theo loại cơ sở dữ liệu bạn đang sử dụng)
    require_once(__DIR__ . '/../../../config/database.php');
    $db = DB::connect();

    // Escape và chuẩn bị dữ liệu
    $phone = $db->real_escape_string($dataUser['phone']);
    $name = $db->real_escape_string($dataUser['name']);
    $date = $db->real_escape_string($dataUser['date']);
    $email = $db->real_escape_string($dataUser['email']);
    $role = $db->real_escape_string($dataUser['role']);



    // Sử dụng prepared statement để tránh SQL injection và loại bỏ trường ID từ câu lệnh SQL
    $stmt = $db->prepare("INSERT INTO users (phone_number, name, hire_date, email, position) 
                          VALUES (?, ?, ?, ?, ?)");

    // Kiểm tra xem prepare có thành công hay không
    if ($stmt === false) {
        die('Error in SQL statement: ' . $db->error);
    }

    // Binds parameters và gán giá trị
    $stmt->bind_param("sssss", $phone, $name, $date, $email, $role);

    // Thực hiện truy vấn
    $resultProduct = $stmt->execute();

    // Kiểm tra lỗi và thông báo
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
