<?php include __DIR__ . '/../header.php'; //это страница показа ?>
    <h1><?=$article->getName()  ?></h1>
    <p><?=$article->getText()  ?></p>
    <p>Автор: <?= $article->getAuthor()->getNickname() ?></p> <!--запрашиваем напрямую пользователя-->

    <!--ссылка на ред-ие будет показываться, только если юзер залогинен и он админ-->
    <?if ($user !==null && $user->isAdmin()): ?>
    <p><a href="/articles/<?= $article->getId() ?>/edit">Редактировать</a></p>
    <?php endif; ?>
<?php include __DIR__ . '/../footer.php'; ?> 