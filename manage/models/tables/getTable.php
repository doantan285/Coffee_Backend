<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once(__DIR__ . '/../../../config/database.php');
$db = DB::connect();

// Cập nhật trường empty trong bảng tables
$update_query = "UPDATE tables t
                 LEFT JOIN orders o ON t.table_number = o.table_number AND (o.is_paid = 0 || o.is_paid = 2)
                 SET t.empty = IF(o.table_number IS NOT NULL, 0, 1)";
$update_result = $db->query($update_query);

if ($update_result === false) {
    $error = array('error' => $db->error);
    echo json_encode($error);
    error_log($db->error);
    exit(); // Thoát khỏi script nếu có lỗi
}

// Truy vấn để lấy dữ liệu từ bảng tables sau khi cập nhật
$select_query = "SELECT * FROM tables";
$select_result = $db->query($select_query);

if ($select_result) {
    $data = array();
    while ($row = $select_result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
} else {
    $error = array('error' => $db->error);
    echo json_encode($error);
    error_log($db->error);
}

$db->close();
?>
