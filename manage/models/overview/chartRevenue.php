<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once(__DIR__ . '/../../../config/database.php');
$db = DB::connect();

$current_year = date("Y");

$query = "SELECT MONTH(date_order) AS month, 
                 SUM(total_amount) AS total_amount 
          FROM orders 
          WHERE is_paid = 1 AND YEAR(date_order) = ?
          GROUP BY MONTH(date_order)
          ORDER BY MONTH(date_order);";

$stmt = $db->prepare($query);
$stmt->bind_param("i", $current_year);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    $data = array();
    $current_month = 1;
    while ($row = $result->fetch_assoc()) {
        $month = (int)$row['month'];
        $total_amount = (float)$row['total_amount'];

        // Fill in months with total_amount = 0
        while ($current_month < $month) {
            $data[] = 0;
            $current_month++;
        }

        $data[] = $total_amount;
        $current_month++;
    }
    while ($current_month <= 12) {
        $data[] = 0;
        $current_month++;
    }

    echo json_encode($data);
} else {
    $error = array('error' => $db->error);
    echo json_encode($error);
    error_log($db->error);
}

$stmt->close();
$db->close();
?>
