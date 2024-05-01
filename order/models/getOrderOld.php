<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once(__DIR__ . '/../../config/database.php');
$db = DB::connect();

// Lấy dữ liệu gửi lên từ frontend
$data = json_decode(file_get_contents("php://input"), true);

// Kiểm tra nếu dữ liệu được gửi lên không rỗng và tồn tại trường tableNumber
if (!empty($data['table_number'])) {
    $tableNumber = $db->real_escape_string($data['table_number']);

    // Truy vấn SQL để lấy dữ liệu từ bảng orders
    $query = "SELECT * FROM orders WHERE table_number = '$tableNumber' AND is_paid <> 1";

    $result = $db->query($query);

    if ($result) {
        $data = array();
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
    } else {
        $error = array('error' => $db->error);
        echo json_encode($error);
        error_log($db->error);
    }

    $result->free();
} else {
    // Trường tableNumber không tồn tại trong dữ liệu gửi lên
    echo json_encode(array('error' => 'Missing tableNumber field'));
}

$db->close();
?>
