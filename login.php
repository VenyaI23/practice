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
        <title>Авторизации</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="css\staly_login.css">


    </head>

    <body>
        <main class="form-signin w-100 m-auto">
            <form method="POST">
                <?php if (isset($errorMessage)) : ?>
                    <p style="color: red;"><?php echo $errorMessage; ?></p>
                <?php endif; ?>
                <img class="t456__imglogo t456__imglogomobile" src="https://static.tildacdn.info/tild3136-3264-4365-b839-353139343962/image.png" imgfield="img" style="max-width: 160px; width: 160px;" alt="АП Софт">
                <h1 class="h3 mb-3 fw-normal">Авторизации</h1>

                <div class="form-floating">
                    <input type="text" class="form-control" name="username" id="username" required>
                    <label for="floatingInput">Имя пользователя</label>
                </div>
                <div class="form-floating">
                    <input type="password" class="form-control" name="password" id="password" required>
                    <label for="floatingPassword">Пароль</label>
                </div>
                <button class="btn btn-primary w-100 py-2" type="submit">Войти</button>
                <p>Еще не зарегистрированы?<br> <a href="registration.php" class="btn btn-primary w-100 py-2">Регистрация</a></p>

            </form>
        </main>
    </body>
</div>

</html>