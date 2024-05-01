<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

function getUserInfo($email) {
    require_once(__DIR__ . '/../../../config/database.php');
    $db = DB::connect();

    // Xử lý chuỗi email để loại bỏ khoảng trắng và ký tự đặc biệt không mong muốn
    $email = trim($email);
    $escapedEmail = $db->real_escape_string($email);

    // Sử dụng prepared statements để tránh SQL injection
    $query = "SELECT account_id, img, username, role, email FROM accounts WHERE email = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("s", $escapedEmail);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result) {
        if ($result->num_rows > 0) {
            $userData = $result->fetch_assoc();
            echo json_encode(['userData' => $userData]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Email not found']);
        }
    } else {
        echo json_encode(['error' => 'Database query error']);
    }

    $stmt->close();
    $db->close();
}

// Kiểm tra xem có tham số email được truyền vào không
if (isset($_GET['email'])) {
    $email = $_GET['email'];
    getUserInfo($email);
} else {
    // Không có tham số email, trả về thông báo lỗi
    echo json_encode(['error' => 'Email parameter is missing']);
}
?>
