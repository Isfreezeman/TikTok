<?php
if (isset($_POST["login"], $_POST["password"], $_POST["newLogin"])) {
    $oldLogin = preg_replace("/[^a-zA-Z0-9_]/", "", $_POST["login"]);
    $newLogin = preg_replace("/[^a-zA-Z0-9_]/", "", $_POST["newLogin"]);
    $path = "users/$oldLogin/p.txt";

    if (!file_exists($path)) exit("Ошибка: Пользователь не найден");

    $data = file_get_contents($path);
    preg_match('/p=(.*)/', $data, $match);
    if (!password_verify($_POST["password"], $match[1])) exit("Ошибка: Неверный пароль");

    if (file_exists("users/$newLogin")) exit("Ошибка: Логин занят");

    // Переименование папки пользователя
    rename("users/$oldLogin", "users/$newLogin");

    // Обновление логина в видеофайлах
    $videoDirs = glob("videos/*", GLOB_ONLYDIR);
    foreach ($videoDirs as $dir) {
        $videoFile = "$dir/p.txt";
        if (file_exists($videoFile)) {
            $content = file_get_contents($videoFile);
            if (strpos($content, "a=$oldLogin") !== false) {
                $newContent = str_replace("a=$oldLogin", "a=$newLogin", $content);
                file_put_contents($videoFile, $newContent);
            }
        }
    }

    echo "Логин изменен";
}
?>