<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Kết nối đến cơ sở dữ liệu
require_once(__DIR__ . '/../../config/database.php');
$db = DB::connect();

// Kiểm tra nếu tham số table_number tồn tại trong query string
if (isset($_GET['table_number']) && !empty($_GET['table_number'])) {
    $tableNumber = $db->real_escape_string($_GET['table_number']);

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
    // Trường table_number không tồn tại trong query string
    echo json_encode(array('error' => 'Missing table_number parameter'));
}

$db->close();
?>
