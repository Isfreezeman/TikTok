<?php
$data = json_decode(file_get_contents("php://input"), true);
$id = $data["id"];
$user = htmlspecialchars($data["user"]);
$text = htmlspecialchars($data["text"]);

$dir = "videos/$id";
$cFile = "$dir/c.txt";

if (!is_dir($dir)) {
    http_response_code(400);
    echo json_encode(["error" => "Видео не найдено"]);
    exit;
}

$comment = "$user: $text\n";
file_put_contents($cFile, $comment, FILE_APPEND | LOCK_EX);

echo json_encode(["success" => true]);
?>