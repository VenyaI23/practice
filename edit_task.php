<?php
// Проверка, была ли отправлена форма
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверка, чтобы все необходимые поля были заполнены
    if (
        isset($_POST['id']) && isset($_POST['название']) && isset($_POST['описание']) && isset($_POST['тип'])
        && isset($_POST['статус']) && isset($_POST['приоритет']) && isset($_POST['assignee'])
    ) {
        // Получение данных из формы
        $id = $_POST['id'];
        $название = $_POST['название'];
        $описание = $_POST['описание'];
        $тип = $_POST['тип'];
        $статус = $_POST['статус'];
        $приоритет = $_POST['приоритет'];
        $исполнитель = $_POST['assignee'];

        // Подключение к базе данных
        $host = 'localhost';
        $dbname = 'postgres';
        $username = 'postgres';
        $password = 'postgres';

        try {
            $db = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Получение нового файла
            $newFile = $_FILES['new_file'];

            // Проверка наличия нового файла
            if ($newFile['name']) {
                // Подготовка и выполнение SQL-запроса для получения пути старого файла
                $query = "SELECT file_path FROM tasks WHERE id = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$id]);
                $oldFilePath = $stmt->fetchColumn();

                // Загрузка нового файла
                $file_path = 'uploads/' . $newFile['name'];
                move_uploaded_file($newFile['tmp_name'], $file_path);

                // Подготовка и выполнение SQL-запроса для обновления информации о задаче и пути файла
                $query = "UPDATE tasks SET название = ?, описание = ?, тип = ?, статус = ?, приоритет = ?, file_path = ?, assignee = ? WHERE id = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$название, $описание, $тип, $статус, $приоритет, $file_path, $исполнитель, $id]);

                // Удаление старого файла, если существует
                if ($oldFilePath && file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }

                echo 'Задача успешно обновлена!';
                header("Location: index.php");
                exit();
            } else {
                // Подготовка и выполнение SQL-запроса для обновления информации о задаче без изменения пути файла
                $query = "UPDATE tasks SET название = ?, описание = ?, тип = ?, статус = ?, приоритет = ?, assignee = ? WHERE id = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$название, $описание, $тип, $статус, $приоритет, $исполнитель, $id]);

                echo 'Задача успешно обновлена!';
                header("Location: index.php");
                exit();
            }
        } catch (PDOException $e) {
            echo 'Ошибка: ' . $e->getMessage();
        }

        $db = null; // Закрытие соединения
    } else {
        echo 'Пожалуйста, заполните все поля формы.';
    }
} else {
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
                // Заполнение формы данными существующей задачи
                $название = $task['название'];
                $описание = $task['описание'];
                $тип = $task['тип'];
                $статус = $task['статус'];
                $приоритет = $task['приоритет'];
                $file_path = $task['file_path'];
                $assignee = $task['assignee'];

                // Вывод формы для редактирования задачи
?>
                <!DOCTYPE html>
                <html>
                <div class="container">

                    <head>
                        <title>Редактировать задачу</title>
                        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
                        <link rel="stylesheet" href="css/style.css">
                    </head>

                    <body>
                        <h2>Редактировать задачу</h2>

                        <form method="POST" action="" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">

                            <label for="название">Название:</label>
                            <input type="text" name="название" id="название" value="<?php echo $название; ?>" required><br>

                            <label for="описание">Описание:</label><br>
                            <textarea name="описание" id="описание" class="form-control" rows="2" required><?php echo $описание; ?></textarea><br>

                            <label for="тип">Тип:</label><br>
                            <select name="тип" id="тип" required>
                                <option value="тестирование" <?php if ($тип === 'тестирование') echo 'selected'; ?>>Тестирование</option>
                                <option value="разработка" <?php if ($тип === 'разработка') echo 'selected'; ?>>Разработка</option>
                            </select><br>

                            <label for="статус">Статус:</label><br>
                            <select name="статус" id="статус" required>
                                <option value="новая" <?php if ($статус === 'новая') echo 'selected'; ?>>Новая</option>
                                <option value="в разработке" <?php if ($статус === 'в разработке') echo 'selected'; ?>>В разработке</option>
                                <option value="завершена" <?php if ($статус === 'завершена') echo 'selected'; ?>>Завершена</option>
                            </select><br>

                            <label for="приоритет">Приоритет:</label><br>
                            <select name="приоритет" id="приоритет" required>
                                <option value="высокий" <?php if ($приоритет === 'высокий') echo 'selected'; ?>>Высокий</option>
                                <option value="средний" <?php if ($приоритет === 'средний') echo 'selected'; ?>>Средний</option>
                                <option value="низкий" <?php if ($приоритет === 'низкий') echo 'selected'; ?>>Низкий</option>
                            </select><br>

                            <!-- Новые поля -->
                            <label for="file_path">Загрузить новый файл:</label>
                            <input type="file" name="new_file" id="file_path"><br>

                            <label for="assignee">Исполнитель:</label><br>
                            <select name="assignee" id="assignee" required>
                                <?php
                                // Подключение к базе данных
                                $host = 'localhost';
                                $dbname = 'postgres';
                                $username = 'postgres';
                                $password = 'postgres';

                                try {
                                    $db = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
                                    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                                    // Получение списка пользователей из базы данных
                                    $query = "SELECT * FROM users";
                                    $stmt = $db->query($query);

                                    // Вывод каждого пользователя в виде варианта выбора
                                    session_start();
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        if ($row['username'] != $_SESSION['username'])
                                            echo '<option value="' . $row['username'] . '">' . $row['username'] . '</option>';
                                    }
                                } catch (PDOException $e) {
                                    echo 'Ошибка: ' . $e->getMessage();
                                }

                                $db = null; // Закрытие соединения
                                ?>
                            </select><br></br>
                            <!-- Конец новых полей -->

                            <input type="submit" value="Сохранить" class="btn btn-primary"><br></br>
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
}
?>