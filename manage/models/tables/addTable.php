<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");

$data = file_get_contents("php://input");
$dataTable = json_decode($data, true);

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

// Truy vấn để lấy ra table_id lớn nhất từ bảng tables
$query_max_id = "SELECT MAX(table_id) AS max_id FROM tables";
$result_max_id = $db->query($query_max_id);

if ($result_max_id && $result_max_id->num_rows > 0) {
    $row_max_id = $result_max_id->fetch_assoc();
    $max_table_id = $row_max_id['max_id'];
    $new_table_id = $max_table_id + 1;
} else {
    // Nếu không có bản ghi nào trong bảng hoặc không thể thực hiện truy vấn, gán giá trị mới là 1
    $new_table_id = 1;
}

// Thêm dữ liệu mới vào bảng tables với table_id mới
$sql = "INSERT INTO tables (table_id, table_number, empty) VALUES (?, ?, 1)";
$stmt = $db->prepare($sql);

if ($stmt) {
    // Xác định giá trị table_number mới dựa trên giá trị của table_id và các giá trị đã có
    $query_existing_numbers = "SELECT table_number FROM tables";
    $result_existing_numbers = $db->query($query_existing_numbers);
    $existing_table_numbers = array();
    if ($result_existing_numbers && $result_existing_numbers->num_rows > 0) {
        while ($row = $result_existing_numbers->fetch_assoc()) {
            $existing_table_numbers[] = $row['table_number'];
        }
    }
    $new_table_number = max($existing_table_numbers) + 1;

    $stmt->bind_param("ii", $new_table_id, $new_table_number); // Giả sử table_id và table_number là kiểu INT

    if ($stmt->execute()) {
        echo "Dữ liệu đã được chèn thành công vào bảng tables với table_id mới là: " . $new_table_id;
    } else {
        echo "Lỗi khi thêm dữ liệu vào bảng tables: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Lỗi trong quá trình chuẩn bị truy vấn: " . $db->error;
}

$db->close();
?>
