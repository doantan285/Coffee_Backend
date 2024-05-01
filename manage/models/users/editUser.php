<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");

$data = file_get_contents("php://input");
$dataEditUser = json_decode($data, true);

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    exit(0);
}

// Kiểm tra xem dữ liệu đã được nhận đúng chưa
if (
    isset($dataEditUser['id']) && 
    isset($dataEditUser['email']) && 
    isset($dataEditUser['phone']) && 
    isset($dataEditUser['date']) && 
    isset($dataEditUser['name']) && 
    isset($dataEditUser['role'])
) {
    
    // Kết nối đến cơ sở dữ liệu (chưa điều chỉnh theo loại cơ sở dữ liệu bạn đang sử dụng)
    require_once(__DIR__ . '/../../../config/database.php');
    $db = DB::connect();

    // Sử dụng Prepared Statements để bảo vệ khỏi SQL injection
    $stmt = $db->prepare("UPDATE users SET email=?, hire_date=?, position=?, name=?, phone_number=? WHERE user_id =?");
    
    // Escape và chuẩn bị dữ liệu
    $email = $dataEditUser['email'];
    $date = $dataEditUser['date'];
    $role = $dataEditUser['role'];
    $username = $dataEditUser['name'];
    $phone_number = $dataEditUser['phone'];
    $user_id = $dataEditUser['id'];

    // In ra giá trị để kiểm tra
    echo "Role before binding: " . $role . PHP_EOL;

    $stmt->bind_param("sssssi", $email, $date, $role, $phone_number, $username, $user_id);

    // In ra giá trị sau khi bind param để kiểm tra
    echo "Role after binding: " . $role . PHP_EOL;

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
