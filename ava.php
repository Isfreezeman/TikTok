<?php
// Получаем имя пользователя из параметра n
$name = isset($_GET['n']) ? preg_replace("/[^a-zA-Z0-9_-]/", "", $_GET['n']) : "";

// Путь к файлу аватарки
$avatarPath = "users/$name/a.png";

// Проверяем, существует ли файл
if (!empty($name) && file_exists($avatarPath)) {
    // Отдаём изображение
    header("Content-Type: image/png");
    readfile($avatarPath);
} else {
    // Перенаправляем на стандартную аватарку
    header("Location: https://i.pravatar.cc/50");
}
?>