<?php
header('Content-Type: application/json');

$users_dir = __DIR__ . "/users";
if (!file_exists($users_dir)) {
    mkdir($users_dir, 0777, true);
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function getIP() {
    return $_SERVER['REMOTE_ADDR'];
}

$data = json_decode(file_get_contents('php://input'), true);
$login = trim($data['login']);
$password = trim($data['password']);
$action = $data['action'];

if (empty($login) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "Введите логин и пароль"]);
    exit;
}

$user_dir = "$users_dir/$login";
$user_file = "$user_dir/p.txt";

if ($action === "register") {
    if (file_exists($user_file)) {
        echo json_encode(["status" => "error", "message" => "Пользователь уже существует"]);
        exit;
    }

    mkdir($user_dir, 0777, true);
    $hashed_password = hashPassword($password);
    $ip = getIP();
    
    file_put_contents($user_file, "l=$login\np=$hashed_password\nli=$ip");
    echo json_encode(["status" => "success", "message" => "Регистрация успешна"]);
    exit;
}

if ($action === "login") {
    if (!file_exists($user_file)) {
        echo json_encode(["status" => "error", "message" => "Пользователь не найден"]);
        exit;
    }

    $user_data = file_get_contents($user_file);
    preg_match('/p=(.+)/', $user_data, $matches);
    $stored_hash = $matches[1] ?? "";

    if (!verifyPassword($password, $stored_hash)) {
        echo json_encode(["status" => "error", "message" => "Неверный пароль"]);
        exit;
    }

    $ip = getIP();
    file_put_contents($user_file, preg_replace('/li=.+/', "li=$ip", $user_data));

    echo json_encode(["status" => "success", "message" => "Вход успешен"]);
    exit;
}

echo json_encode(["status" => "error", "message" => "Неверное действие"]);
exit;
?>