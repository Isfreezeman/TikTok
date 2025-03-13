<?php
$upload_dir = 'videos/';

// Генерируем случайное имя папки
$folder_name = uniqid();

// Путь к папке для загрузки видео
$folder_path = $upload_dir . $folder_name;

if (!file_exists($folder_path)) {
    mkdir($folder_path, 0777, true);
}

// Путь к видео и текстовому файлу
$video_path = $folder_path . '/video.mp4';
$p_file_path = $folder_path . '/p.txt';

// Получаем данные из запроса
$username = $_POST['username'] ?? 'unknown';
$description = $_POST['description'] ?? 'Без описания';

if (strlen($description) > 20 || !preg_match("/^[a-zA-Z0-9\s\u{1F600}-\u{1F64F}\u{0410}-\u{044F}ёЁ]+$/u", $description)) {
    echo json_encode(['success' => false, 'message' => 'Описание должно содержать максимум 20 символов и только буквы, цифры, смайлики и русские буквы']);
    exit;
}

// Загрузка видео
if (isset($_FILES['video'])) {
    $video = $_FILES['video'];

    // Проверяем ошибки загрузки
    if ($video['error'] === UPLOAD_ERR_OK) {
        // Проверка на размер файла (не более 5 МБ)
        if ($video['size'] > 5 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'Видео не должно превышать 5 МБ']);
            exit;
        }

        // Сохраняем загруженное видео
        $temp_video_path = $folder_path . '/temp_video.mp4';
        if (move_uploaded_file($video['tmp_name'], $temp_video_path)) {
            // Проверка на длительность видео (не более 30 секунд)
            $ffmpeg_command = "ffmpeg -i $temp_video_path 2>&1";
            exec($ffmpeg_command, $output, $return_var);
            $duration = null;
            foreach ($output as $line) {
                if (preg_match('/Duration: (\d+):(\d+):(\d+)\.(\d+)/', $line, $matches)) {
                    $duration = $matches[1] * 3600 + $matches[2] * 60 + $matches[3];
                    break;
                }
            }

            if ($duration > 30) {
                echo json_encode(['success' => false, 'message' => 'Видео не должно превышать 30 секунд']);
                exit;
            }

            // Получаем исходное разрешение видео
            $ffprobe_command = "ffprobe -v error -select_streams v:0 -show_entries stream=width,height -of csv=s=x:p=0 $temp_video_path";
            exec($ffprobe_command, $resolution_output, $return_var);
            $resolution = trim($resolution_output[0]);

            // Конвертируем видео с сохранением исходного разрешения и уменьшением качества
            $ffmpeg_command = "ffmpeg -i $temp_video_path -vf scale=$resolution -c:v libx264 -crf 28 -preset medium -c:a aac -b:a 128k $video_path";
            exec($ffmpeg_command, $output, $return_var);

            // Проверяем, что конвертация прошла успешно
            if ($return_var === 0) {
                // Удаляем временный файл
                unlink($temp_video_path);

                // Получаем текущее время в формате 17:00
                $current_time = date("H:i");

                // Записываем информацию в файл p.txt
                $file_content = "a=$username\n";
                $file_content .= "o=$description\n";
                $file_content .= "l=0\n";
                $file_content .= "d=0\n";
                $file_content .= "t=$current_time\n";
                file_put_contents($p_file_path, $file_content);

                // Возвращаем успешный ответ
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Ошибка при конвертации видео']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Ошибка при сохранении видео']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка при загрузке файла']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Видео не было отправлено']);
}
?>