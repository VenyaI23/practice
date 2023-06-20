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

        // Подготовка и выполнение SQL-запроса для выборки задачи по id
        $query = "SELECT * FROM tasks WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$id]);
        $task = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($task) {
            // Заполнение переменных данными существующей задачи
            $название = $task['название'];
            $описание = $task['описание'];

            // Вывод информации о задаче
?>

            <!DOCTYPE html>
            <html>
            <div class="container_view">

                <head>
                    <title>Просмотр задачи</title>
                    <link rel="stylesheet" href="csss/bootstrap.css">
                    <link rel="stylesheet" href="csss/style.css">
                </head>

                <body>
                    <h1>Просмотр задачи</h1>

                    <h2><?php echo $название; ?></h2>
                    <p><strong>Описание:</strong> <?php echo $описание; ?></p>

                    <!-- Добавленный блок для скачивания файла -->
                    <?php if (!empty($task['file_path'])) : ?>
                        <a href="<?php echo $task['file_path']; ?>" class="btn btn-primary" download>Скачать</a>
                    <?php endif; ?>
                    <!-- Конец блока скачивания файла -->

                    <h2>Комментарии</h2>

                    <?php
                    // Проверка, была ли отправлена форма для добавления комментария
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        // Проверка, чтобы поле комментария было заполнено
                        if (isset($_POST['комментарий'])) {
                            session_start();
                            $comment = $_SESSION['username'].": ".$_POST['комментарий'];

                            // Подготовка и выполнение SQL-запроса для добавления комментария
                            $query = "INSERT INTO comments (задача_id, комментарий) VALUES (?, ?)";
                            $stmt = $db->prepare($query);
                            $stmt->execute([$id, $comment]);

                            echo 'Комментарий добавлен!';
                        } else {
                            echo 'Пожалуйста, заполните поле комментария.';
                        }
                    }
                    ?>

                    <?php
                    // Выполнение SQL-запроса для получения комментариев к задаче
                    $query = "SELECT * FROM comments WHERE задача_id = ?";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$id]);
                    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (count($comments) > 0) {
                        echo '<ul>';
                        foreach ($comments as $comment) {
                            echo '<li>' . $comment['комментарий'] . '</li>';
                        }
                        echo '</ul>';
                    } else {
                        echo 'Нет комментариев.';
                    }
                    ?>
                    <!-- Форма для добавления комментария -->
                    <form method="POST" action="">
                        <label for="комментарий">Добавить комментарий:</label>
                        <input type="text" name="комментарий" id="комментарий" required><br></br>
                        <input type="submit" value="Добавить" class="btn btn-primary"><br></br>
                        <a href="index.php" class="btn btn-primary">Назад</a>
                    </form>
                </body>
            </div>

            </html>
<?php

        } else {
            echo 'Задача не найдена.';
        }
    } catch (PDOException $e) {
        echo 'Ошибка: ' . $e->getMessage();
    }

    $db = null; // Закрытие соединения

} else {
    echo 'Не указан идентификатор задачи.';
}
?>