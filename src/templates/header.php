<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Мой блог</title>
    <link rel="stylesheet" href="/styles.css">
</head>
<body>

<table class="layout">
    <tr>
        <td colspan="2" class="header">
            Мой блог
        </td>
    </tr>
    <tr>
        <td colspan="2" style="text-align: right">
            <!--Если пользователь залогинен, ссылка для выхода из этой учетной записи-->
            <?php if (!empty($user)): ?>
            Привет, <?= $user->getNickname() ?> | <a href="http://myproject.loc/users/logOut">Выйти</a>

                <!--Если пользователь не залогинен, ссылка для регистрации-->
            <?php else: ?>
            <a href="http://myproject.loc/users/login">Войти</a> | <a href="http://myproject.loc/users/register">Зарегестрироваться</a>
            <? endif; ?>
        </td>
    </tr>

    <tr>
        <td>
