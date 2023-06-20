<?php
// Подключение к базе данных
$host = 'localhost';
$dbname = 'postgres';
$username = 'postgres';
$password = 'postgres';

try {
    $db = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Проверка, были ли отправлены данные формы
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Получение введенных данных из формы
        $username = $_POST['username'];
        $password = $_POST['password'];
        $role = $_POST['role'];

        // Проверка, существует ли пользователь с таким именем
        $query = "SELECT COUNT(*) FROM users WHERE username = :username";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $errorMessage = 'Пользователь с таким именем уже существует.';
        } else {
            // Хеширование пароля
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Вставка нового пользователя в базу данных
            $query = "INSERT INTO users (username, password, role) VALUES (:username, :password, :role)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':role', $role);
            $stmt->execute();

            // Редирект на страницу авторизации
            header('Location: login.php');
            exit();
        }
    }
} catch (PDOException $e) {
    echo 'Ошибка подключения к базе данных: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<div class="container">

    <head>
        <title>Страница регистрации</title>
        <link rel="stylesheet" href="csss/bootstrap.css">
        <link rel="stylesheet" href="csss/staly_login.css">
    </head>

    <body>
        <h1>Страница регистрации</h1>

        <?php if (isset($errorMessage)) : ?>
            <p style="color: red;"><?php echo $errorMessage; ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="username">Имя пользователя:</label>
            <input type="text" name="username" id="username" required>
            <br>
            <label for="password">Пароль:</label>
            <input type="password" name="password" id="password" required>
            <br>
            <label for="role">Тип аккаунта:</label>
            <select name="role" id="role" required>
                <option value="tester">Тестировщик</option>
                <option value="programmer">Программист</option>
                <option value="admin">Главный разработчик</option>
            </select><br></br>
            <input type="submit" value="Зарегистрироваться" class="btn btn-primary"><br></br>
            <a href="login.php" class="btn btn-primary">Назад</a>
        </form>
    </body>
</div>

</html>