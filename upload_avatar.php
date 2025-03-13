<?php
if ($_FILES["avatar"]["error"] == 0 && isset($_POST["login"])) {
    $login = preg_replace("/[^a-zA-Z0-9_]/", "", $_POST["login"]);
    $dir = "users/$login/";

    if (!file_exists($dir)) mkdir($dir, 0777, true);
    
    move_uploaded_file($_FILES["avatar"]["tmp_name"], "$dir/a.png");
    echo "Аватарка обновлена!";
}
?>