<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once(__DIR__ . '/../../../config/database.php');
$db = DB::connect();

$query = "SELECT * FROM products WHERE category = 'snacks' AND InStock = 1";
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