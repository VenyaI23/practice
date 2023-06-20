<?php
session_start();

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

        // Поиск пользователя в базе данных
        $query = "SELECT * FROM users WHERE username = :username";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Аутентификация успешна
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Редирект на домашнюю страницу
            header('Location: index.php');
            exit();
        } else {
            $errorMessage = 'Неверные имя пользователя или пароль.';
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
        <title>Страница авторизации</title>
        <link rel="stylesheet" href="csss/bootstrap.css">
        <link rel="stylesheet" href="csss/staly_login.css">
    </head>

    <body>
        <h1>Страница авторизации</h1>

        <?php if (isset($errorMessage)) : ?>
            <p style="color: red;"><?php echo $errorMessage; ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="username">Имя пользователя:</label>
            <input type="text" name="username" id="username" required>
            <br>
            <label for="password">Пароль:</label>
            <input type="password" name="password" id="password" required><br></br>

            <input type="submit" value="Войти" class="btn btn-primary"><br></br>
            <p>Еще не зарегистрированы?<br> <a href="registration.php" class="btn btn-primary">Регистрация</a></p>
        </form>
    </body>
</div>

</html>