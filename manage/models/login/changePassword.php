<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");

$data = file_get_contents("php://input");
$dataChangePassword = json_decode($data, true);

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    exit(0);
}

// Include file kết nối cơ sở dữ liệu
require_once(__DIR__ . '/../../../config/database.php');

// Kết nối đến cơ sở dữ liệu
$db = DB::connect();

// Lấy thông tin từ dữ liệu JSON
$email = $dataChangePassword['email'];
$password = $dataChangePassword['password'];
$newPassword = $dataChangePassword['newPassword'];

// Xác thực email và mật khẩu cũ
$stmt = $db->prepare("SELECT * FROM accounts WHERE email = ? AND password = ?");
$stmt->bind_param("ss", $email, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Nếu email và mật khẩu cũ hợp lệ, thực hiện thay đổi mật khẩu
    $stmt_update = $db->prepare("UPDATE accounts SET password = ? WHERE email = ?");
    $stmt_update->bind_param("ss", $newPassword, $email);
    if ($stmt_update->execute()) {
        echo json_encode(array("message" => "Password updated successfully"));
    } else {
        echo json_encode(array("message" => "Error updating password"));
    }
} else {
    echo json_encode(array("message" => "changeError")); // Trả về lỗi nếu email hoặc mật khẩu không hợp lệ
}

// Đóng kết nối cơ sở dữ liệu và statement select
$stmt->close();
$db->close();
?>
