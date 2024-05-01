<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");

$data = file_get_contents("php://input");
$dataDeleteTable = json_decode($data);

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    exit(0);
}

if ($dataDeleteTable !== null) {
    require_once(__DIR__ . '/../../../config/database.php');
    $db = DB::connect();

    $id = $dataDeleteTable->id;

    // Truy vấn SQL DELETE
    $sql = "DELETE FROM tables WHERE table_id = ?";

    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => 'Delete successful']);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'Delete failed']);
    }

    $stmt->close();
    $db->close();
} else {
    // Handle lỗi khi không thể giải mã JSON
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid JSON data']);
}
?>
