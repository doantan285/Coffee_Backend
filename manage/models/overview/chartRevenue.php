<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once(__DIR__ . '/../../../config/database.php');
$db = DB::connect();

$query = "SELECT MONTH(date_order) AS month, YEAR(date_order) AS year, 
                SUM(total_amount) AS total_amount 
          FROM orders
          GROUP BY YEAR(date_order), MONTH(date_order)
          ORDER BY year, month;";

$result = $db->query($query);

if ($result) {
    $data = array();
    $current_month = 1;
    while ($row = $result->fetch_assoc()) {
        $month = (int)$row['month'];
        $year = (int)$row['year'];
        $total_amount = (float)$row['total_amount'];

        // Fill in months with total_amount = 0
        while ($current_month < $month) {
            $data[] = 0;
            $current_month++;
        }

        $data[] = $total_amount;
        $current_month++;
    }

    // Fill in remaining months with total_amount = 0
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

$db->close();
?>
