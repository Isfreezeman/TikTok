<?php
$id = $_GET["id"] ?? "";
$dir = "videos/$id";
$cFile = "$dir/c.txt";

if (!is_dir($dir) || !file_exists($cFile)) {
    echo json_encode([]);
    exit;
}

$lines = file($cFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$comments = [];

foreach ($lines as $line) {
    [$user, $text] = explode(": ", $line, 2);
    $comments[] = ["user" => $user, "text" => $text];
}

echo json_encode($comments);
?>