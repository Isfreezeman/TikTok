<?php
$author = $_GET["author"] ?? "";

$videos = [];
$dirs = glob("videos/*", GLOB_ONLYDIR);

foreach ($dirs as $dir) {
    $pData = parse_ini_file("$dir/p.txt");
    if ($pData["a"] === $author) {
        $comments = file_exists("$dir/c.txt") ? count(file("$dir/c.txt")) : 0;
        $videos[] = [
            "id" => basename($dir),
            "video" => "$dir/video.mp4",
            "description" => $pData["o"],
            "likes" => $pData["l"],
            "dislikes" => $pData["d"],
            "comments" => $comments,
            "avatar" => "https://i.pravatar.cc/50",
        ];
    }
}

echo json_encode($videos);
?>