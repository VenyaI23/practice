<?php
session_start();
session_destroy();

// Перенаправление страницу авторизации
header("Location: login.php");
exit();
?>