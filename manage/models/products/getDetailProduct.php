<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$data = file_get_contents("php://input");
$id = json_decode($data, true);

require_once(__DIR__ . '/../../../config/database.php');
$db = DB::connect();

if (is_array($id) && isset($id['id'])) {
    $idValue = $id['id'];

    // Truy vấn sản phẩm
    $productQuery = "SELECT * FROM products WHERE product_id = '$idValue'";
    $productResult = $db->query($productQuery);

    if ($productResult) {
        $productData = array();

        // Check if any product data is found
        if ($productResult->num_rows > 0) {
            while ($productRow = $productResult->fetch_assoc()) {
                $productData[] = $productRow;
            }

            // Truy vấn bình luận cho sản phẩm
            $commentQuery = "SELECT * FROM comments WHERE product_id = '$idValue'";
            $commentResult = $db->query($commentQuery);

            if ($commentResult) {
                $commentData = array();
                while ($commentRow = $commentResult->fetch_assoc()) {
                    $commentData[] = $commentRow;
                }

                // Thêm dữ liệu sản phẩm và bình luận vào $data
                $data = array();
                $data['product'] = $productData;
                $data['comments'] = $commentData;

                echo json_encode($data);
            } else {
                $error = array('error' => $db->error);
                echo json_encode($error);
                error_log($db->error);
            }
        } else {
            // No product data found
            echo json_encode(['error' => 'No product data found']);
        }
    } else {
        $error = array('error' => $db->error);
        echo json_encode($error);
        error_log($db->error);
    }
} else {
    echo json_encode(array('error' => 'Invalid or missing ID'));
}

$db->close();
?>
