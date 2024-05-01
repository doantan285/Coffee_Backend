<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once(__DIR__ . '/../../../config/database.php');
$db = DB::connect();

// Lấy thời gian hiện tại theo định dạng timestamp
$currentTimestamp = time();

// Lấy thời gian 24 giờ trước thời điểm hiện tại
$twentyFourHoursAgoTimestamp = $currentTimestamp - (24 * 3600); // 24 giờ = 24 * 3600 giây

$query = "SELECT * FROM orders WHERE is_paid = '0' OR is_paid = '2' OR date_order >= FROM_UNIXTIME($twentyFourHoursAgoTimestamp) AND date_order <= FROM_UNIXTIME($currentTimestamp)";
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

$db->close();
?>
