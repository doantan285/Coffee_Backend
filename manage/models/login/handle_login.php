<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");

$data = file_get_contents("php://input");
$datalogin = json_decode($data);
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    exit(0);
}

if ($datalogin !== null) {
    if (isset($datalogin->username) && isset($datalogin->password)) {
        $username = $datalogin->username;
        $password = $datalogin->password;
        require_once(__DIR__ . '/../../../config/database.php');
        $db = DB::connect();

        // Escape các giá trị để ngăn chặn tấn công SQL injection
        $escapedUsername = $db->real_escape_string($username);
        $escapedPassword = $db->real_escape_string($password);

        // Truy vấn cơ sở dữ liệu để kiểm tra đăng nhập
        $query = "SELECT email, role FROM accounts WHERE username = '$escapedUsername' AND password = '$escapedPassword'";
        $result = $db->query($query);

        if ($result) {
            // Kiểm tra xem có bản ghi nào trả về hay không
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $email = $row['email'];
                $role = $row['role'];
                $authToken = bin2hex(random_bytes(32));
            
                // Đăng nhập thành công
                echo json_encode([
                    'login' => true, 
                    'email' =>  $email,
                    'role' => $role,
                    'authToken' => $authToken
                ]);
                setcookie("authToken", $authToken, time() + 60, "/", "", true, true);
            } else {
                // Sai thông tin đăng nhập
                echo json_encode(['login' => false]);
            }
        } else {
            // Xử lý lỗi truy vấn
            echo json_encode(['error' => 'Database query error']);
        }

        // Đóng kết nối cơ sở dữ liệu
        $db->close();
    }
} else {
    // Handle lỗi khi không thể giải mã JSON
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid JSON data']);
}
?>