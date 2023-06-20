<?php
// Проверка, был ли передан параметр id в URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Подключение к базе данных
    $host = 'localhost';
    $dbname = 'postgres';
    $username = 'postgres';
    $password = 'postgres';

    try {
        $db = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Проверка, есть ли связанные комментарии
        $query = "SELECT COUNT(*) FROM comments WHERE задача_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$id]);
        $commentCount = $stmt->fetchColumn();

        if ($commentCount > 0) {
            // Удаление связанных комментариев
            $query = "DELETE FROM comments WHERE задача_id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$id]);
        }

        // Удаление задачи
        $query = "DELETE FROM tasks WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$id]);

        echo 'Задача успешно удалена.';
        header("Location: index.php");
    } catch (PDOException $e) {
        echo 'Ошибка: ' . $e->getMessage();
    }

    $db = null; // Закрытие соединения

} else {
    echo 'Не указан идентификатор задачи.';
}
