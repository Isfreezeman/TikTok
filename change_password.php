<?php
if (isset($_POST["login"], $_POST["oldPass"], $_POST["newPass"])) {
    $login = preg_replace("/[^a-zA-Z0-9_]/", "", $_POST["login"]);
    $path = "users/$login/p.txt";

    if (!file_exists($path)) exit("Ошибка: Пользователь не найден");

    $data = file_get_contents($path);
    preg_match('/p=(.*)/', $data, $match);
    if (!password_verify($_POST["oldPass"], $match[1])) exit("Ошибка: Неверный пароль");

    $newHash = password_hash($_POST["newPass"], PASSWORD_BCRYPT);
    file_put_contents($path, "p=$newHash");
    echo "Пароль успешно изменен";
}
?>