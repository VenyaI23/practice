<?php
// Проверка, была ли отправлена форма
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверка, чтобы все необходимые поля были заполнены
    if (isset($_POST['название']) && isset($_POST['описание']) && isset($_POST['тип']) && isset($_POST['статус']) && isset($_POST['приоритет'])) {
        // Получение данных из формы
        $название = $_POST['название'];
        $описание = $_POST['описание'];
        $тип = $_POST['тип'];
        $статус = $_POST['статус'];
        $приоритет = $_POST['приоритет'];

        // Обработка загрузки файла
        $file_path = '';
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['file'];
            $uploadDir = 'uploads/';
            $filename = basename($file['name']);
            $uploadPath = $uploadDir . $filename;
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $file_path = $uploadPath;
            } else {
                echo 'Ошибка при загрузке файла.';
            }
        }

        // Дополнительные поля
        $assignee = $_POST['assignee'];

        // Получение автора из сессии
        session_start();
        $author = isset($_SESSION['username']) ? $_SESSION['username'] : '';

        // Подключение к базе данных
        $host = 'localhost';
        $dbname = 'postgres';
        $username = 'postgres';
        $password = 'postgres';

        try {
            $db = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Подготовка и выполнение SQL-запроса для вставки новой задачи
            $query = "INSERT INTO tasks (название, описание, тип, статус, приоритет, file_path, author, assignee) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->execute([$название, $описание, $тип, $статус, $приоритет, $file_path, $author, $assignee]);

            $success_message = 'Новая задача успешно создана! Файл: ' . $filename;
        } catch (PDOException $e) {
            echo 'Ошибка: ' . $e->getMessage();
        }

        $db = null; // Закрытие соединения
    } else {
        echo 'Пожалуйста, заполните все поля формы.';
    }
}
?>

<!DOCTYPE html>
<html>
<div class="container">

    <head>
        <title>Создать новую задачу</title>
        <link rel="stylesheet" href="csss/bootstrap.css">
        <link rel="stylesheet" href="csss/style.css">
    </head>

    <body>
        <h2>Создать новую задачу</h2>

        <form method="POST" action="" enctype="multipart/form-data">
            <label for="название">Название:</label>
            <input type="text" name="название" id="название" required><br>

            <label for="описание">Описание:</label><br>
            <textarea name="описание" id="описание" class="form-control" rows="2" required></textarea>

            <label for="тип">Тип:</label><br>
            <select name="тип" id="тип" required>
                <option value="тестирование">Тестирование</option>
                <option value="разработка">Разработка</option>
            </select><br>

            <label for="статус">Статус:</label><br>
            <select name="статус" id="статус" required>
                <option value="новая">новая</option>
                <option value="в работе">в работе</option>
                <option value="завершена">завершена</option>
            </select><br>

            <label for="приоритет">Приоритет:</label><br>
            <select name="приоритет" id="приоритет" required>
                <option value="высокий">высокий</option>
                <option value="средний">средний</option>
                <option value="низкий">низкий</option>
            </select><br>

            <!-- Новые поля -->
            <label for="file">Файл:</label>
            <input type="file" name="file" id="file" required><br>

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

            <?php if (isset($success_message)) {
                echo '<p class="success-message">' . $success_message . '</p>';
            } ?>
            <input type="submit" value="Создать" class="btn btn-primary"><br></br>

            <a href="index.php" class="btn btn-primary">Назад</a>
        </form>
    </body>
</div>

</html>