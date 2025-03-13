<?php
header("Content-Type: application/json");

// Определяем путь к видео
$dirs = glob("videos/*", GLOB_ONLYDIR);
$randomDir = $dirs[array_rand($dirs)];
$pFile = "$randomDir/p.txt";
$cFile = "$randomDir/comments.txt";

// Получаем данные из p.txt
$pData = parse_ini_file($pFile);
$comments = file_exists($cFile) ? file($cFile, FILE_IGNORE_NEW_LINES) : [];

// Определяем метод запроса
$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    // Отдаем данные о видео
    echo json_encode([
        "id" => basename($randomDir),
        "video" => "$randomDir/video.mp4",
        "author" => $pData["a"],
        "description" => $pData["o"],
        "likes" => $pData["l"],
        "dislikes" => $pData["d"],
        "comments" => count($comments),
        "commentList" => $comments,
        "avatar" => "https://i.pravatar.cc/50"
    ]);
} elseif ($method === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data["id"] ?? null;
    $type = $data["type"] ?? null;
    $user = $data["user"] ?? "Аноним";
    $text = $data["text"] ?? "";

    if (!$id) {
        echo json_encode(["error" => "ID видео не указан"]);
        exit;
    }

    $dir = "videos/$id";
    $pFile = "$dir/p.txt";
    $cFile = "$dir/comments.txt";

    if ($type === "like" || $type === "dislike") {
        $pData = parse_ini_file($pFile);
        if ($type == "like") $pData["l"] += 1;
        if ($type == "dislike") $pData["d"] += 1;
        
        file_put_contents($pFile, "a={$pData["a"]}\no={$pData["o"]}\nl={$pData["l"]}\nd={$pData["d"]}");
        echo json_encode(["success" => "Лайк/дизлайк обновлен"]);
    } elseif (!empty($text)) {
        file_put_contents($cFile, "$user: $text\n", FILE_APPEND);
        echo json_encode(["success" => "Комментарий добавлен"]);
    } else {
        echo json_encode(["error" => "Некорректный запрос"]);
    }
}
?>