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
        <title>Регистрации</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="css/staly_login.css">
    </head>

    <body>

        <main class="form-signin w-100 m-auto">
            <form method="POST">
                <?php if (isset($errorMessage)) : ?>
                    <p style="color: red;"><?php echo $errorMessage; ?></p>
                <?php endif; ?>
                <img class="t456__imglogo t456__imglogomobile" src="https://static.tildacdn.info/tild3136-3264-4365-b839-353139343962/image.png" imgfield="img" style="max-width: 160px; width: 160px;" alt="АП Софт">
                <h1 class="h3 mb-3 fw-normal">Регистрация</h1>

                <div class="form-floating">
                    <input type="text" class="form-control" name="username" id="username" required>
                    <label for="floatingInput">Имя пользователя</label>
                </div>
                <div class="form-floating">
                    <input type="password" class="form-control" name="password" id="password" required>
                    <label for="floatingPassword">Пароль</label>
                </div>
                <label for="role">Тип аккаунта:</label>
                <select name="role" id="role" class="form-select" aria-label="Default select example" required>
                    <option value="tester">Тестировщик</option>
                    <option value="programmer">Программист</option>
                    <option value="admin">Главный разработчик</option>
                </select><br>
                <input type="submit" value="Зарегистрироваться" class="btn btn-primary w-100 py-2"><br></br>
                <a href="login.php" class="btn btn-primary w-100 py-2"">Назад</a>
            </form>
        </main>
    </body>
</div>

</html>