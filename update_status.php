<?php
// Подключение к базе данных
$host = 'localhost';
$dbname = 'postgres';
$username = 'postgres';
$password = 'postgres';

try {
    $db = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Проверка наличия переданных данных
    if (isset($_POST['task_id']) && isset($_POST['task_status'])) {
        $taskId = $_POST['task_id'];
        $taskStatus = $_POST['task_status'];

        // Обновление статуса задачи
        $query = "UPDATE tasks SET статус = :status WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':status', $taskStatus);
        $stmt->bindParam(':id', $taskId);
        $stmt->execute();

        echo 'Статус задачи успешно обновлен.';
        header("Location: index.php");
    } else {
        echo 'Ошибка: Не переданы данные для обновления статуса задачи.';
    }
} catch (PDOException $e) {
    echo 'Ошибка: ' . $e->getMessage();
}

$db = null; // Закрытие соединения
