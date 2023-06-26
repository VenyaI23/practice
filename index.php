<?php
// Проверка, авторизован ли пользователь
session_start();
if (!isset($_SESSION['username'])) {
    // Пользователь не авторизован, перенаправление на страницу авторизации
    header('Location: login.php');
    exit();
}
// Проверка роли пользователя
$role = $_SESSION['role'];
$isAdmin = ($role === 'admin');
?>

<!DOCTYPE html>
<html>

<head>
    <title>Главная страница</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .logout-btn {
            position: absolute;
            top: 40px;
            right: 20px;
        }

        .content-container {
            padding: 20px;
        }
    </style>
</head>

<body>
    <div class="content-container">
        <h1>Система учёта</h1>
        <h3>Пользователь: <?php echo $_SESSION['username'] ?></h3>
        <h4>Список задач</h4>

        <form action="index.php" method="GET">
            <label for="filter_type">Фильтр по типу:</label>
            <select name="filter_type" id="filter_type">
                <option value="">Все типы</option>
                <option value="тестирование" <?php echo isset($_GET['filter_type']) && $_GET['filter_type'] === 'тестирование' ? 'selected' : ''; ?>>Тестирование</option>
                <option value="разработка" <?php echo isset($_GET['filter_type']) && $_GET['filter_type'] === 'разработка' ? 'selected' : ''; ?>>Разработка</option>
                <!-- Добавьте другие опции для фильтрации по типу -->
            </select>

            <label for="filter_status">Фильтр по статусу:</label>
            <select name="filter_status" id="filter_status">
                <option value="">Все статусы</option>
                <option value="новая" <?php echo isset($_GET['filter_status']) && $_GET['filter_status'] === 'новая' ? 'selected' : ''; ?>>Новая</option>
                <option value="в работе" <?php echo isset($_GET['filter_status']) && $_GET['filter_status'] === 'в работе' ? 'selected' : ''; ?>>В работе</option>
                <option value="завершена" <?php echo isset($_GET['filter_status']) && $_GET['filter_status'] === 'завершена' ? 'selected' : ''; ?>>Завершена</option>
                <!-- Добавьте другие опции для фильтрации по статусу -->
            </select>
            <label for="search">Поиск:</label>
            <input type="text" name="search" id="search" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">

            <button type="submit" class="btn btn-primary">Применить фильтр</button>
        </form>

        <?php
        // Подключение к базе данных
        $host = 'localhost';
        $dbname = 'postgres';
        $username = 'postgres';
        $password = 'postgres';

        try {
            $db = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Подготовка фильтров
            $filterType = isset($_GET['filter_type']) ? $_GET['filter_type'] : '';
            $filterStatus = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';
            $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

            // Построение SQL-запроса с учетом фильтров
            $query = "SELECT * FROM tasks WHERE 1=1";
            if (!empty($filterType)) {
                $query .= " AND тип = '$filterType'";
            }
            if (!empty($filterStatus)) {
                $query .= " AND статус = '$filterStatus'";
            }
            if (!empty($searchTerm)) {
                $query .= " AND (название LIKE '%$searchTerm%' OR тип LIKE '%$searchTerm%' OR статус LIKE '%$searchTerm%')";
            }
            $query .= " ORDER BY создано DESC";

            $stmt = $db->query($query);
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($tasks) > 0) {
                echo '<table>';
                echo '<tr><th>Название</th><th>Тип</th><th>Статус</th><th>Приоритет</th><th>Создано</th><th>Файл</th><th>Автор</th><th>Исполнитель</th><th>Действия</th></tr>';

                foreach ($tasks as $task) {
                    if (isset($_SESSION['role']) && $_SESSION['role'] === 'tester' && $task['тип'] === 'тестирование') {
                        // Пользователь с ролью "tester"
                        echo '<tr>';
                        echo '<td>' . $task['название'] . '</td>';
                        echo '<td>' . $task['тип'] . '</td>';
                        echo '<td>';
                        echo '<form action="update_status.php" method="POST">';
                        echo '<input type="hidden" name="task_id" value="' . $task['id'] . '">';
                        echo '<select name="task_status">';
                        echo '<option value="новая"' . ($task['статус'] == 'новая' ? ' selected' : '') . '>Новая</option>';
                        echo '<option value="в работе"' . ($task['статус'] == 'в работе' ? ' selected' : '') . '>В работе</option>';
                        echo '<option value="завершена"' . ($task['статус'] == 'завершена' ? ' selected' : '') . '>Завершена</option>';
                        echo '</select>';
                        echo '<button type="submit" class="btn btn-primary">Обновить</button>';
                        echo '</form>';
                        echo '</td>';
                        echo '<td>' . $task['приоритет'] . '</td>';
                        $createdDate = date('H:i:s Y-m-d', strtotime($task['создано']));
                        echo '<td>' . $createdDate . '</td>';
                        echo '<td>' . (isset($task['file_path']) ? basename($task['file_path']) : '') . '</td>';
                        echo '<td>' . $task['author'] . '</td>';
                        echo '<td>' . $task['assignee'] . '</td>';
                        echo '<td><a href="view_task.php?id=' . $task['id'] . '" class="btn btn-primary">Просмотреть</a></td>';
                        echo '</tr>';
                    }
                    if (isset($_SESSION['role']) && $_SESSION['role'] === 'programmer' && $task['тип'] === 'разработка') {
                        // Пользователь с ролью "programmer"
                        echo '<tr>';
                        echo '<td>' . $task['название'] . '</td>';
                        echo '<td>' . $task['тип'] . '</td>';
                        echo '<td>';
                        echo '<form action="update_status.php" method="POST">';
                        echo '<input type="hidden" name="task_id" value="' . $task['id'] . '">';
                        echo '<select name="task_status">';
                        echo '<option value="новая"' . ($task['статус'] == 'новая' ? ' selected' : '') . '>Новая</option>';
                        echo '<option value="в работе"' . ($task['статус'] == 'в работе' ? ' selected' : '') . '>В работе</option>';
                        echo '<option value="завершена"' . ($task['статус'] == 'завершена' ? ' selected' : '') . '>Завершена</option>';
                        echo '</select>';
                        echo '<button type="submit" class="btn btn-primary">Обновить</button>';
                        echo '</form>';
                        echo '</td>';
                        echo '<td>' . $task['приоритет'] . '</td>';
                        $createdDate = date('H:i:s Y-m-d', strtotime($task['создано']));
                        echo '<td>' . $createdDate . '</td>';
                        echo '<td>' . (isset($task['file_path']) ? basename($task['file_path']) : '') . '</td>';
                        echo '<td>' . $task['author'] . '</td>';
                        echo '<td>' . $task['assignee'] . '</td>';
                        echo '<td><a href="view_task.php?id=' . $task['id'] . '" class="btn btn-primary">Просмотреть</a></td>';
                        echo '</tr>';
                    }
                    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                        // Пользователь с ролью "admin"
                        echo '<tr>';
                        echo '<td>' . $task['название'] . '</td>';
                        echo '<td>' . $task['тип'] . '</td>';
                        echo '<td>';
                        echo '<form action="update_status.php" method="POST">';
                        echo '<input type="hidden" name="task_id" value="' . $task['id'] . '">';
                        echo '<select name="task_status">';
                        echo '<option value="новая"' . ($task['статус'] == 'новая' ? ' selected' : '') . '>Новая</option>';
                        echo '<option value="в работе"' . ($task['статус'] == 'в работе' ? ' selected' : '') . '>В работе</option>';
                        echo '<option value="завершена"' . ($task['статус'] == 'завершена' ? ' selected' : '') . '>Завершена</option>';
                        echo '</select>';
                        echo '<button type="submit" class="btn btn-primary">Обновить</button>';
                        echo '</form>';
                        echo '</td>';
                        echo '<td>' . $task['приоритет'] . '</td>';
                        $createdDate = date('H:i:s Y-m-d', strtotime($task['создано']));
                        echo '<td>' . $createdDate . '</td>';
                        echo '<td>' . (isset($task['file_path']) ? basename($task['file_path']) : '') . '</td>';
                        echo '<td>' . $task['author'] . '</td>';
                        echo '<td>' . $task['assignee'] . '</td>';
                        echo '<td><a href="view_task.php?id=' . $task['id'] . '" class="btn btn-primary">Просмотреть</a> | <a href="edit_task.php?id=' . $task['id'] . '" class="btn btn-primary">Редактировать</a> | <a href="delete_task.php?id=' . $task['id'] . '" class="btn btn-primary">Удалить</a></td>';
                        echo '</tr>';
                    }
                }

                echo '</table>';
            } else {
                echo 'Нет задач для отображения.';
            }
        } catch (PDOException $e) {
            echo 'Ошибка: ' . $e->getMessage();
        }

        $db = null; // Закрытие соединения
        ?>
        <br>
        <?php
        // Проверка роли пользователя
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            // Пользователь с ролью "admin"
            echo '<a href="create_task.php" class="btn btn-primary">Добавить новую задачу</a>';
        }
        ?>
        <a href="logout.php" class="btn btn-primary logout-btn">Выход</a>
    </div>
</body>

</html>