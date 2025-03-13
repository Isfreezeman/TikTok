<?php
// Получаем параметр id из запроса
$videoId = isset($_GET['id']) ? $_GET['id'] : '';

// Функция для получения случайного видео
function getRandomVideo() {
    $dirs = glob("videos/*", GLOB_ONLYDIR);
    if (empty($dirs)) {
        return null; // Если нет видео, возвращаем null
    }
    $randomDir = $dirs[array_rand($dirs)];
    return $randomDir;
}

// Функция для формирования ответа
function getVideoResponse($videoDir) {
    if (!is_dir($videoDir) || !file_exists("$videoDir/p.txt")) {
        return null; // Если директория или файл p.txt отсутствуют, возвращаем null
    }

    // Читаем данные из файла p.txt
    $pData = parse_ini_file("$videoDir/p.txt");

    // Получаем количество комментариев
    $comments = file_exists("$videoDir/c.txt") ? count(file("$videoDir/c.txt")) : 0;

    // Формируем ответ
    return [
        "id" => basename($videoDir),
        "video" => "$videoDir/video.mp4",
        "author" => $pData["a"],
        "description" => $pData["o"],
        "likes" => $pData["l"],
        "dislikes" => $pData["d"],
        "comments" => $comments,
        "avatar" => "http://a1087175.xsph.ru/ava.php?n=" . urlencode($pData["a"]),
    ];
}

// Если параметр id передан, проверяем существование видео
if ($videoId) {
    $videoDir = "videos/$videoId";
    $response = getVideoResponse($videoDir);

    // Если видео не найдено или данные некорректны, выбираем случайное видео
    if (!$response) {
        $randomDir = getRandomVideo();
        if ($randomDir) {
            $response = getVideoResponse($randomDir);
        } else {
            $response = ["error" => "No videos available"];
        }
    }
} else {
    // Если параметр id не передан, выбираем случайное видео
    $randomDir = getRandomVideo();
    if ($randomDir) {
        $response = getVideoResponse($randomDir);
    } else {
        $response = ["error" => "No videos available"];
    }
}

// Выводим ответ в формате JSON
echo json_encode($response);
?>