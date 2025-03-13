<?php
header("Content-Type: application/json");

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (!$query) {
    echo json_encode([]);
    exit;
}

function getFirstFrame($videoPath, $thumbnailPath) {
    if (!file_exists($thumbnailPath)) {
        $cmd = "ffmpeg -i \"$videoPath\" -ss 00:00:01 -vframes 1 \"$thumbnailPath\" 2>&1";
        shell_exec($cmd);
    }
    return $thumbnailPath;
}

function searchVideos($query) {
    $videosDir = "videos/";
    $results = [];

    foreach (glob($videosDir . "*", GLOB_ONLYDIR) as $folder) {
        $folderName = basename($folder);
        $infoFile = $folder . "/p.txt";
        $videoFile = $folder . "/video.mp4";
        $thumbFile = $folder . "/thumbnail.jpg";

        if (file_exists($infoFile) && file_exists($videoFile)) {
            $data = file($infoFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $videoData = [];

            foreach ($data as $line) {
                list($key, $value) = explode("=", $line, 2);
                $videoData[trim($key)] = trim($value);
            }

            if (stripos($videoData['o'], $query) !== false) {
                $thumbnail = getFirstFrame($videoFile, $thumbFile);

                $results[] = [
                    "thumbnail" => $thumbnail,
                    "video_url" => "http://a1087175.xsph.ru/home.html?video=" . urlencode($folderName),
                    "date" => date("d M Y", filemtime($infoFile)),
                    "author" => $videoData['a'] ?? "Неизвестный",
                    "description" => mb_substr($videoData['o'], 0, 15) . "...",
                    "likes" => $videoData['l'] ?? 0,
                    "duration" => $videoData['t'] ?? "00:00"
                ];
            }
        }
    }

    return $results;
}

$videos = searchVideos($query);
echo json_encode($videos, JSON_UNESCAPED_UNICODE);
?>